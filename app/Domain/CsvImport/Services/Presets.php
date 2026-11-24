<?php

namespace Leantime\Domain\CsvImport\Services;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Events\DispatchesEvents;
use Symfony\Component\Yaml\Yaml;

/**
 * Presets for exports of other tools (CsvImport/Presets/{tool}.yaml): how to recognise a file,
 * which of its columns go to which Leantime field, and what its values mean in Leantime.
 *
 * Nothing is applied on its own. The matching step says when it recognises a file, and the
 * person opts in with "Match the columns for me"; the value translations then show on the check
 * step as choices they can undo.
 */
class Presets
{
    use DispatchesEvents;

    /** Fields never suggested: a matched Id updates existing items, so that is always a choice. */
    private const NEVER_SUGGESTED = ['id', 'itemId'];

    /** Fields that take several columns (their values are combined). */
    private const MULTI_COLUMN_FIELDS = ['tags'];

    /** @var array<string, array<string, mixed>>|null Presets by id, common.yaml excluded. */
    private ?array $presets = null;

    /** @var array<string, array<int, string>>|null Field => headings that usually mean it. */
    private ?array $common = null;

    /**
     * All presets, by id. Plugins add theirs through the "presets" filter.
     *
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        if ($this->presets !== null) {
            return $this->presets;
        }

        $presets = [];
        foreach (glob(__DIR__.'/../Presets/*.yaml') ?: [] as $path) {
            if (basename($path) === 'common.yaml') {
                continue;
            }
            $preset = $this->load($path);
            if ($preset !== null) {
                $presets[(string) $preset['id']] = $preset;
            }
        }

        $presets = self::dispatch_filter('presets', $presets);

        return $this->presets = is_array($presets) ? $presets : [];
    }

    /**
     * The preset a file's headings belong to, or null. The best match wins when several qualify.
     *
     * @param  array<int, string>  $headings  The file's column headings.
     * @param  string|null  $entity  Only presets that can import this entity, when given.
     * @return array<string, mixed>|null
     */
    public function recognise(array $headings, ?string $entity = null): ?array
    {
        $have = array_map(fn (string $heading) => mb_strtolower(self::baseHeading($heading)), $headings);
        $best = null;
        $bestShare = 0.0;

        foreach ($this->all() as $preset) {
            if ($entity !== null && ! in_array($entity, (array) ($preset['entities'] ?? []), true)) {
                continue;
            }
            $expected = array_map(fn ($heading) => mb_strtolower((string) $heading), (array) ($preset['recognise']['headings'] ?? []));
            if ($expected === []) {
                continue;
            }
            $share = count(array_intersect($expected, $have)) / count($expected);
            if ($share >= (float) ($preset['recognise']['minimum'] ?? 1.0) && $share > $bestShare) {
                $best = $preset;
                $bestShare = $share;
            }
        }

        return $best;
    }

    /**
     * Suggested column matches: the preset's columns first, then headings that usually mean a
     * field, then a field's own name. Each field gets one column, except fields that combine.
     *
     * @param  array<int, string>  $headings  The file's column headings.
     * @param  array<string, array<string, mixed>>  $leantimeFields  The entity's fields.
     * @param  array<string, mixed>|null  $preset  The recognised preset, if any.
     * @return array<string, string> Heading => Leantime field key.
     */
    public function suggest(array $headings, array $leantimeFields, ?array $preset): array
    {
        $byName = [];
        foreach ((array) ($preset['columns'] ?? []) as $heading => $field) {
            $byName[mb_strtolower((string) $heading)] = (string) $field;
        }
        foreach ($this->common() as $field => $names) {
            foreach ($names as $name) {
                $byName[mb_strtolower($name)] ??= $field;
            }
        }
        foreach ($leantimeFields as $key => $field) {
            foreach (array_merge([(string) ($field['name'] ?? '')], (array) ($field['aliases'] ?? [])) as $name) {
                if ($name !== '') {
                    $byName[mb_strtolower($name)] ??= (string) $key;
                }
            }
        }

        // Columns the preset knows Leantime can't import yet (Jira's Sprint holds Jira sprint names)
        // are never suggested, even when a Leantime field has the same name.
        $leftovers = array_map(fn ($heading) => mb_strtolower((string) $heading), (array) ($preset['leftovers'] ?? []));

        $suggested = [];
        $taken = [];
        foreach ($headings as $heading) {
            if (in_array(mb_strtolower(self::baseHeading((string) $heading)), $leftovers, true)) {
                continue;
            }
            $field = $byName[mb_strtolower(self::baseHeading((string) $heading))] ?? null;
            if ($field === null || ! isset($leantimeFields[$field]) || in_array($field, self::NEVER_SUGGESTED, true)) {
                continue;
            }
            if (isset($taken[$field]) && ! in_array($field, self::MULTI_COLUMN_FIELDS, true)) {
                continue;
            }
            $suggested[(string) $heading] = $field;
            $taken[$field] = true;
        }

        return $suggested;
    }

    /**
     * The preset's value translations for the values a file has, as check-step choices:
     * one per distinct value of a matched status, priority or type column that the preset knows.
     *
     * @param  array<string, mixed>  $preset  The recognised preset.
     * @param  array<int, array{sourceField: string, leantimeField: string}>  $fields  Column matches.
     * @param  array<int|string, array<string, mixed>>  $rows  The rows as in the file.
     * @return array<int, array{column: string, from: string, to: string}>
     */
    public function valueChoices(array $preset, array $fields, array $rows): array
    {
        $choices = [];
        foreach ($fields as $match) {
            $map = [];
            foreach ((array) ($preset['values'][$match['leantimeField']] ?? []) as $from => $to) {
                $map[mb_strtolower((string) $from)] = (string) $to;
            }
            if ($map === []) {
                continue;
            }

            $seen = [];
            foreach ($rows as $row) {
                $value = trim((string) ($row[$match['sourceField']] ?? ''));
                $to = $map[mb_strtolower($value)] ?? null;
                if ($value === '' || $to === null || isset($seen[$value])) {
                    continue;
                }
                $seen[$value] = true;
                $choices[] = [
                    'column' => (string) $match['sourceField'],
                    'from' => $value,
                    'to' => $match['leantimeField'] === 'status' ? 'kind:'.$to : $to,
                ];
            }
        }

        return $choices;
    }

    /**
     * A heading without the reader's numbering of repeated headings: "Labels (2)" → "Labels".
     */
    public static function baseHeading(string $heading): string
    {
        return trim((string) preg_replace('/\s\(\d+\)$/', '', trim($heading)));
    }

    /**
     * Headings that usually mean a field (common.yaml).
     *
     * @return array<string, array<int, string>>
     */
    private function common(): array
    {
        if ($this->common === null) {
            $common = $this->load(__DIR__.'/../Presets/common.yaml');
            $this->common = array_map(fn ($names) => array_map('strval', (array) $names), (array) ($common['columns'] ?? []));
        }

        return $this->common;
    }

    /**
     * Reads one preset file; a broken file is logged and left out.
     *
     * @return array<string, mixed>|null
     */
    private function load(string $path): ?array
    {
        try {
            $data = Yaml::parseFile($path);
        } catch (\Throwable $e) {
            Log::error($e);

            return null;
        }

        return is_array($data) && isset($data['id']) ? $data : null;
    }
}

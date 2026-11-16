<?php

namespace Unit\app\Domain\Blueprints\Templates;

use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Leantime\Domain\Blueprints\Services\TemplateRegistry;
use Unit\TestCase;

/**
 * Every translation key a canvas definition (Templates/definitions/*.yaml) uses must be in
 * en-US.ini, or the board shows the raw key (#4049: the Project Value Canvas dialog showed
 * label.valueCanvas.assumptions/data/conclusion; the Strategy Message dialog showed
 * label.sm.description).
 */
class DefinitionLabelKeysTest extends TestCase
{
    /**
     * Keys of en-US.ini, parsed the way Leantime's Language class reads it.
     */
    private function englishKeys(): array
    {
        return parse_ini_file(APP_ROOT.'/app/Language/en-US.ini', false, INI_SCANNER_RAW);
    }

    /**
     * Collect every 'title' string in the layout (box group headers etc.), at any depth.
     *
     * @return list<string>
     */
    private function layoutTitles(array $layout): array
    {
        $titles = [];
        array_walk_recursive($layout, function ($value, $key) use (&$titles) {
            if ($key === 'title' && is_string($value) && $value !== '') {
                $titles[] = $value;
            }
        });

        return $titles;
    }

    public function test_every_label_key_used_by_a_canvas_definition_exists_in_en_us(): void
    {
        $englishKeys = $this->englishKeys();
        $missingKeys = [];

        /** @var CanvasTemplate $template */
        foreach ((new TemplateRegistry)->all() as $template) {
            $keys = array_merge(
                array_column($template->boxes, 'title'),
                array_column($template->statusLabels, 'title'),
                array_column($template->relatesLabels, 'title'),
                $this->layoutTitles($template->layout),
            );

            if ($template->disclaimer !== '') {
                $keys[] = $template->disclaimer;
            }

            foreach ($keys as $key) {
                if (! isset($englishKeys[$key])) {
                    $missingKeys[] = $template->slug.': '.$key;
                }
            }

            // A data label may instead be worded per box ("{title}.{box}", see
            // Blueprints::getTranslatedDataLabels()); then every box needs its own key.
            foreach (array_column($template->dataLabels, 'title') as $key) {
                if ($key === '' || isset($englishKeys[$key])) {
                    continue;
                }

                foreach (array_keys($template->boxes) as $box) {
                    if (! isset($englishKeys[$key.'.'.$box])) {
                        $missingKeys[] = $template->slug.': '.$key.' (or '.$key.'.'.$box.')';
                    }
                }
            }
        }

        $this->assertSame([], $missingKeys, "Canvas definitions use keys that en-US.ini doesn't have");
    }
}

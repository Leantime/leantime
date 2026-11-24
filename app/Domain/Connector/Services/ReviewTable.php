<?php

namespace Leantime\Domain\Connector\Services;

use Leantime\Core\Language;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Users\Services\Users;

/**
 * The table of the review ("Check") step: each mapped value as Leantime will save it, where it
 * came from, and the problems of each cell.
 *
 * To-dos and milestones show resolved values (a person's name, a priority's label, a date in the
 * user's format, a status of the project) with the file's own value as a note when it differs.
 * Other imports show the file's values as they are.
 */
class ReviewTable
{
    /** Rows shown when nothing needs a fix; the rest is counted. */
    public const ROWS_SHOWN = 50;

    /** Entities whose values are shown resolved. */
    private const RESOLVED_ENTITIES = ['tickets', 'milestones'];

    /** @var array<int, string>|null Full names of people by id. */
    private ?array $names = null;

    /** @var array<int, array<int|string, mixed>> Status labels by project id. */
    private array $statusLabels = [];

    /** @var array<int, string> Project names by id. */
    private array $projectNames = [];

    public function __construct(
        private Users $userService,
        private Tickets $ticketService,
        private Projects $projectService,
        private Language $language,
    ) {}

    /**
     * Builds the review table.
     *
     * @param  string  $entity  Entity key.
     * @param  array<int, array{sourceField: string, leantimeField: string}>  $fields  Column matches.
     * @param  array<int|string, array<string, mixed>>  $rawValues  The rows as in the file.
     * @param  array<int|string, array<string, mixed>>  $reviewedValues  The same rows as the review read them.
     * @param  array{cells: array<int, array<string, array<int, string>>>, general: array<int, string>}  $problems  What the review found.
     * @param  array<string, array<string, mixed>>  $leantimeFields  The entity's fields (names).
     * @param  int|null  $chosenProjectId  The project chosen for rows that name none.
     * @return array{columns: array<int, array{source: string, field: string, name: string}>, rows: array<int, array{row: int, cells: array<int, array{value: string, note: ?string, problems: array<int, string>, muted: bool, userId: ?int}>}>, total: int, problemRows: int, hiddenRows: int, counts: array<string, int>, projects: array<int, string>, defaults: array<int, string>}
     */
    public function build(string $entity, array $fields, array $rawValues, array $reviewedValues, array $problems, array $leantimeFields, ?int $chosenProjectId = null): array
    {
        $columns = array_map(fn (array $match) => [
            'source' => (string) $match['sourceField'],
            'field' => (string) $match['leantimeField'],
            'name' => (string) ($leantimeFields[$match['leantimeField']]['name'] ?? $match['leantimeField']),
        ], array_values($fields));

        $resolve = in_array($entity, self::RESOLVED_ENTITIES, true);
        $idColumn = $this->sourceOf($columns, $entity === 'tickets' || $entity === 'milestones' ? 'id' : 'itemId');
        $rows = [];
        $problemRows = 0;
        $counts = ['new' => 0, 'updates' => 0, 'assigned' => 0, 'unassigned' => 0];
        $projects = [];

        foreach ($rawValues as $index => $raw) {
            $rowNumber = (int) ($raw[Connector::ROW_KEY] ?? ((int) $index + 2));
            $reviewed = $reviewedValues[$index] ?? $raw;
            $rowProblems = $problems['cells'][$rowNumber] ?? [];
            if ($rowProblems !== []) {
                $problemRows++;
            }

            $isUpdate = $idColumn !== null && is_numeric(trim((string) ($raw[$idColumn] ?? '')));
            $counts[$isUpdate ? 'updates' : 'new']++;
            if ($resolve) {
                if ((int) ($reviewed['editorId'] ?? 0) > 0) {
                    $counts['assigned']++;
                } elseif (! $isUpdate) {
                    $counts['unassigned']++;
                }
                if ((int) ($reviewed['projectId'] ?? 0) > 0) {
                    $projects[(int) $reviewed['projectId']] = $this->projectName((int) $reviewed['projectId']);
                }
            }

            $cells = [];
            foreach ($columns as $column) {
                $cellProblems = $rowProblems[$column['source']] ?? [];
                $cell = $resolve && $cellProblems === []
                    ? $this->resolvedCell($column, $raw, $reviewed, $isUpdate)
                    : $this->plainCell((string) ($raw[$column['source']] ?? ''));
                $cell['problems'] = $cellProblems;
                $cells[] = $cell;
            }

            $rows[] = ['row' => $rowNumber, 'cells' => $cells, 'hasProblems' => $rowProblems !== []];
        }

        $total = count($rows);
        // With problems, the rows that need a fix; otherwise the first rows.
        $shown = $problemRows > 0 || $problems['general'] !== []
            ? array_values(array_filter($rows, fn (array $row) => $row['hasProblems']))
            : array_slice($rows, 0, self::ROWS_SHOWN);
        if ($shown === [] && $rows !== []) {
            $shown = array_slice($rows, 0, self::ROWS_SHOWN);
        }

        return [
            'columns' => $columns,
            'rows' => $shown,
            'total' => $total,
            'problemRows' => $problemRows,
            'hiddenRows' => $total - count($shown),
            'counts' => $counts,
            'projects' => array_values($projects),
            'defaults' => $resolve ? $this->defaults($entity, $columns, $chosenProjectId) : [],
        ];
    }

    /**
     * A value shown as it is in the file.
     *
     * @return array{value: string, note: ?string, problems: array<int, string>, muted: bool, userId: ?int}
     */
    private function plainCell(string $value): array
    {
        return ['value' => trim($value), 'note' => null, 'problems' => [], 'muted' => false, 'userId' => null];
    }

    /**
     * A to-do or milestone value as Leantime will save it, with the file's value as a note when
     * they differ.
     *
     * @param  array{source: string, field: string, name: string}  $column
     * @param  array<string, mixed>  $raw  The row as in the file.
     * @param  array<string, mixed>  $reviewed  The row as the review read it.
     * @return array{value: string, note: ?string, problems: array<int, string>, muted: bool, userId: ?int}
     */
    private function resolvedCell(array $column, array $raw, array $reviewed, bool $isUpdate): array
    {
        $inFile = trim((string) ($raw[$column['source']] ?? ''));
        $read = trim((string) ($reviewed[$column['source']] ?? ''));
        $cell = $this->plainCell($inFile);

        switch ($column['field']) {
            case 'id':
                return is_numeric($inFile)
                    ? ['value' => sprintf($this->language->__('text.import_value_update'), $inFile)] + $cell
                    : ['value' => $this->language->__('text.import_value_new'), 'muted' => true] + $cell;

            case 'editorId':
                $userId = (int) ($reviewed['editorId'] ?? 0);
                if ($userId > 0) {
                    $name = $this->personName($userId);

                    return ['value' => $name, 'userId' => $userId, 'note' => $this->inFileNote($inFile, $name)] + $cell;
                }
                if ($inFile === '') {
                    return ['value' => $this->language->__($isUpdate ? 'text.import_value_keeps' : 'text.import_value_unassigned'), 'muted' => true] + $cell;
                }

                return $cell;

            case 'projectName':
                $projectId = (int) ($reviewed['projectId'] ?? 0);
                if ($projectId <= 0) {
                    return $inFile === '' && $isUpdate
                        ? ['value' => $this->language->__('text.import_value_keeps'), 'muted' => true] + $cell
                        : $cell;
                }
                $name = $this->projectName($projectId);

                return ['value' => $name, 'note' => $inFile === '' ? $this->language->__('text.import_note_project_chosen') : $this->inFileNote($inFile, $name)] + $cell;

            case 'priority':
                if ($read === '') {
                    return $cell;
                }
                $label = (string) ($this->ticketService->getPriorityLabels()[$read] ?? $read);

                return ['value' => $label, 'note' => $this->inFileNote($inFile, $label)] + $cell;

            case 'status':
                if ($inFile === '') {
                    return $cell;
                }
                $projectId = (int) ($reviewed['projectId'] ?? 0);
                $statusId = $reviewed['status'] ?? null;
                if ($statusId !== null && $projectId > 0) {
                    $label = (string) ($this->statusLabels($projectId)[$statusId]['name'] ?? $inFile);

                    return ['value' => $this->language->__($label), 'note' => $this->inFileNote($inFile, $this->language->__($label))] + $cell;
                }

                return ['note' => $this->language->__($isUpdate ? 'text.import_note_status_kept' : 'text.import_note_status_start'), 'muted' => true] + $cell;

            case 'type':
                $type = strtolower($inFile);

                return $type === ''
                    ? ['value' => ucfirst('task'), 'note' => $this->language->__('text.import_note_type_default'), 'muted' => true] + $cell
                    : ['value' => ucfirst($type), 'note' => $this->inFileNote($inFile, ucfirst($type))] + $cell;

            case 'planHours':
            case 'hourRemaining':
                if ($read === '') {
                    return $cell;
                }
                $hours = sprintf($this->language->__('text.import_value_hours'), $read);

                return ['value' => $hours, 'note' => $this->inFileNote($inFile, $read)] + $cell;

            case 'date':
            case 'dateToFinish':
            case 'editFrom':
            case 'editTo':
                if ($read === '') {
                    return $cell;
                }
                try {
                    $date = dtHelper()->parseUserDateTime($read);
                } catch (\Exception $e) {
                    return $cell;
                }
                // Due and created dates are days; work start and end keep a time when the file has one.
                $hasTime = in_array($column['field'], ['editFrom', 'editTo'], true) && preg_match('/\d{1,2}:\d{2}|\d{8}T\d{4}/i', $read) === 1;
                $shown = $date->formatDateForUser().($hasTime ? ' '.$date->formatTimeForUser() : '');

                return ['value' => $shown, 'note' => $this->inFileNote($inFile, $shown)] + $cell;

            case 'description':
                return ['value' => \Illuminate\Support\Str::limit(trim(strip_tags($inFile)), 80)] + $cell;
        }

        return $cell;
    }

    /**
     * Where values come from that the file has no column for.
     *
     * @param  array<int, array{source: string, field: string, name: string}>  $columns
     * @return array<int, string>
     */
    private function defaults(string $entity, array $columns, ?int $chosenProjectId): array
    {
        $defaults = [];
        if ($this->sourceOf($columns, 'projectName') === null && $chosenProjectId !== null) {
            $defaults[] = sprintf($this->language->__('text.import_default_project'), $this->projectName($chosenProjectId));
        }
        if ($this->sourceOf($columns, 'editorId') === null) {
            $defaults[] = $this->language->__('text.import_default_assignee');
        }
        if ($entity === 'tickets' && $this->sourceOf($columns, 'status') === null) {
            $defaults[] = $this->language->__('text.import_default_status');
        }
        if ($entity === 'tickets' && $this->sourceOf($columns, 'type') === null) {
            $defaults[] = $this->language->__('text.import_default_type');
        }

        return $defaults;
    }

    /**
     * "… in your file" when the file's value differs from the one shown, else null.
     */
    private function inFileNote(string $inFile, string $shown): ?string
    {
        if ($inFile === '' || strcasecmp($inFile, $shown) === 0) {
            return null;
        }

        return sprintf($this->language->__('text.import_note_in_file'), $inFile);
    }

    /**
     * The column matched to a field, or null.
     *
     * @param  array<int, array{source: string, field: string, name: string}>  $columns
     */
    private function sourceOf(array $columns, string $field): ?string
    {
        foreach ($columns as $column) {
            if ($column['field'] === $field) {
                return $column['source'];
            }
        }

        return null;
    }

    /**
     * A person's full name.
     */
    private function personName(int $userId): string
    {
        if ($this->names === null) {
            $this->names = [];
            foreach ((array) $this->userService->getAll() as $user) {
                $this->names[(int) $user['id']] = trim(($user['firstname'] ?? '').' '.($user['lastname'] ?? ''));
            }
        }

        return $this->names[$userId] ?? (string) $userId;
    }

    /**
     * A project's name.
     */
    private function projectName(int $projectId): string
    {
        if (! isset($this->projectNames[$projectId])) {
            $project = $this->projectService->getProject($projectId);
            $this->projectNames[$projectId] = is_array($project) ? (string) $project['name'] : (string) $projectId;
        }

        return $this->projectNames[$projectId];
    }

    /**
     * The status labels of a project.
     *
     * @return array<int|string, mixed>
     */
    private function statusLabels(int $projectId): array
    {
        return $this->statusLabels[$projectId] ??= $this->ticketService->getStatusLabels($projectId);
    }
}

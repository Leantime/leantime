<?php

namespace Leantime\Domain\Connector\Services;

use Leantime\Core\Language;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Users\Services\Users;

/**
 * Choices on the check step that change every row with the same value at once: assign
 * "Zed Nobody" to a person, treat "Urgent" as Critical, treat "Waiting" as an in-progress status,
 * put the rows of an unknown project into one, set a due date where the file has none. The file itself isn't changed: the choices are
 * applied to the rows before they are reviewed again, and imported with them.
 */
class ReviewFixes
{
    /** A choice that empties the cells (leave unassigned). */
    public const EMPTY = '__empty__';

    /** Which kind of choice each fixable problem gets. */
    private const KIND_OF_PROBLEM = [
        'notification.import_row_unknown_person' => 'person',
        'notification.import_row_unknown_email' => 'person',
        'notification.import_row_person_ambiguous' => 'person',
        'notification.import_row_assignee_not_on_team' => 'person',
        'notification.import_row_unknown_project' => 'project',
        'notification.import_row_project_empty' => 'project',
        'notification.import_row_invalid_priority' => 'priority',
        'notification.import_row_invalid_type' => 'type',
        Connector::UNKNOWN_STATUS => 'status',
    ];

    /** Entities whose empty Assigned To and Due Date cells can be filled in for all rows. */
    private const FILLABLE_ENTITIES = ['tickets', 'milestones'];

    /** @var array<string, array<string, string>> Options per kind: value to save => label. */
    private array $options = [];

    public function __construct(
        private Users $userService,
        private Projects $projectService,
        private Tickets $ticketService,
        private Language $language,
    ) {}

    /**
     * The choices sent from the check step, without the one being undone.
     *
     * @param  array<string, mixed>  $params  The posted form: fixes[hash][column|from|to], undo.
     * @return array<string, array{column: string, from: string, to: string}> Keyed by fixKey().
     */
    public function fromRequest(array $params): array
    {
        $fixes = [];
        foreach ((array) ($params['fixes'] ?? []) as $fix) {
            if (! is_array($fix) || ! isset($fix['column'], $fix['to']) || ! is_string($fix['column']) || ! is_string($fix['to'])) {
                continue;
            }
            $to = trim($fix['to']);
            if ($to === '') {
                continue;
            }
            $key = self::fixKey($fix['column'], (string) ($fix['from'] ?? ''));
            $fixes[$key] = ['column' => $fix['column'], 'from' => trim((string) ($fix['from'] ?? '')), 'to' => $to];
        }

        unset($fixes[(string) ($params['undoFix'] ?? '')]);

        return $fixes;
    }

    /**
     * The rows with the choices applied: each cell whose value is a choice's "from" gets its "to".
     *
     * @param  array<int|string, array<string, mixed>>  $values  The rows as in the file.
     * @param  array<string, array{column: string, from: string, to: string}>  $fixes
     * @return array<int|string, array<string, mixed>>
     */
    public function apply(array $values, array $fixes): array
    {
        foreach ($fixes as $fix) {
            $to = $fix['to'] === self::EMPTY ? '' : $fix['to'];
            foreach ($values as &$row) {
                if (array_key_exists($fix['column'], $row) && trim((string) $row[$fix['column']]) === $fix['from']) {
                    $row[$fix['column']] = $to;
                }
            }
            unset($row);
        }

        return $values;
    }

    /**
     * The choices to offer: one per fixable problem value, and for to-dos and milestones one for
     * the empty cells of Assigned To and Due Date. Choices already made are listed separately.
     *
     * @param  string  $entity  Entity key.
     * @param  array<int, array{sourceField: string, leantimeField: string}>  $fields  Column matches.
     * @param  array<int|string, array<string, mixed>>  $rawValues  The rows as in the file.
     * @param  array<string, array<string, array<string, array<int, int>>>>  $fixable  From Connector::reviewProblems().
     * @param  array<string, array{column: string, from: string, to: string}>  $applied  Choices already made.
     * @param  array<string, array<string, mixed>>  $leantimeFields  The entity's fields (names).
     * @return array{open: array<int, array{key: string, column: string, from: string, label: string, rows: int, kind: string, options: array<string, string>}>, applied: array<int, array{key: string, column: string, from: string, to: string, label: string, toLabel: string}>}
     */
    public function choices(string $entity, array $fields, array $rawValues, array $fixable, array $applied, array $leantimeFields): array
    {
        $open = [];
        $fieldOf = [];
        foreach ($fields as $match) {
            $fieldOf[(string) $match['sourceField']] = (string) $match['leantimeField'];
        }

        foreach ($fixable as $column => $byProblem) {
            foreach ($byProblem as $problem => $byValue) {
                $kind = self::KIND_OF_PROBLEM[$problem] ?? null;
                // A person choice only for the assignee column (an idea's author is fixed in the file).
                if ($kind === null || ($kind === 'person' && ($fieldOf[$column] ?? '') !== 'editorId')) {
                    continue;
                }
                foreach ($byValue as $from => $rows) {
                    $key = self::fixKey($column, (string) $from);
                    $open[$key] ??= $this->choice($key, $column, (string) $from, $kind, 0, $leantimeFields[$fieldOf[$column] ?? '']['name'] ?? $column);
                    $open[$key]['rows'] += count(array_unique($rows));
                }
            }
        }

        if (in_array($entity, self::FILLABLE_ENTITIES, true)) {
            foreach (['editorId' => 'person', 'dateToFinish' => 'date'] as $field => $kind) {
                $column = array_search($field, $fieldOf, true);
                if ($column === false) {
                    continue;
                }
                $key = self::fixKey($column, '');
                $empty = count(array_filter($rawValues, fn (array $row) => trim((string) ($row[$column] ?? '')) === ''));
                if ($empty > 0 && ! isset($open[$key]) && ! isset($applied[$key])) {
                    $open[$key] = $this->choice($key, $column, '', $kind, $empty, $leantimeFields[$field]['name'] ?? $column);
                }
            }
        }

        $appliedList = [];
        foreach ($applied as $key => $fix) {
            $field = $fieldOf[$fix['column']] ?? '';
            $kind = match ($field) {
                'editorId' => 'person',
                'projectName' => 'project',
                'priority' => 'priority',
                'type' => 'type',
                'status' => 'status',
                'dateToFinish' => 'date',
                default => '',
            };
            $appliedList[] = $fix + [
                'key' => $key,
                'label' => $this->label($fix['from'], $leantimeFields[$field]['name'] ?? $fix['column']),
                'toLabel' => $fix['to'] === self::EMPTY
                    ? $this->language->__('label.import_fix_unassigned')
                    : ($this->options($kind)[$fix['to']] ?? $fix['to']),
            ];
        }

        return ['open' => array_values($open), 'applied' => $appliedList];
    }

    /**
     * The key of a choice: its column and the value it replaces.
     */
    public static function fixKey(string $column, string $from): string
    {
        return md5($column."\0".trim($from));
    }

    /**
     * One choice to offer.
     *
     * @return array{key: string, column: string, from: string, label: string, rows: int, kind: string, options: array<string, string>}
     */
    private function choice(string $key, string $column, string $from, string $kind, int $rows, string $fieldName): array
    {
        $options = $kind === 'date' ? [] : $this->options($kind);
        if ($kind === 'person') {
            // Leaving the rows unassigned is a choice too.
            $options = [self::EMPTY => $this->language->__('label.import_fix_unassigned')] + $options;
        }

        return [
            'key' => $key,
            'column' => $column,
            'from' => $from,
            'label' => $this->label($from, $fieldName),
            'rows' => $rows,
            'kind' => $kind,
            'options' => $options,
        ];
    }

    /**
     * What a choice is about: "“Zed Nobody” in Assigned To", or "Empty Due Date".
     */
    private function label(string $from, string $fieldName): string
    {
        return $from === ''
            ? sprintf($this->language->__('label.import_fix_empty'), $fieldName)
            : sprintf($this->language->__('label.import_fix_value'), $from, $fieldName);
    }

    /**
     * The values a kind of choice offers, as saved in the cell => as shown.
     *
     * @return array<string, string>
     */
    private function options(string $kind): array
    {
        if (isset($this->options[$kind])) {
            return $this->options[$kind];
        }

        $options = [];
        switch ($kind) {
            case 'person':
                // The email address finds the person again when the rows are reviewed.
                foreach ((array) $this->userService->getAll(true) as $user) {
                    if (! empty($user['username'])) {
                        $options[(string) $user['username']] = trim(($user['firstname'] ?? '').' '.($user['lastname'] ?? ''));
                    }
                }
                asort($options, SORT_NATURAL | SORT_FLAG_CASE);
                break;
            case 'project':
                foreach ((array) $this->projectService->getAllProjects() as $project) {
                    $options[(string) $project['name']] = (string) $project['name'];
                }
                uksort($options, 'strnatcasecmp');
                break;
            case 'priority':
                foreach ($this->ticketService->getPriorityLabels() as $number => $label) {
                    $options[(string) $number] = (string) $label;
                }
                break;
            case 'type':
                foreach (array_diff($this->ticketService->getTicketTypes(), ['subtask']) as $type) {
                    $options[(string) $type] = ucfirst((string) $type);
                }
                break;
            case 'status':
                // A kind of status: each row gets its own project's status of that kind.
                foreach (['new', 'inprogress', 'done'] as $kind) {
                    $options['kind:'.$kind] = $this->language->__('label.import_status_kind_'.$kind);
                }
                break;
        }

        return $this->options[$kind] = $options;
    }
}

<?php

namespace Leantime\Domain\Tickets\Services;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Domains\BaseService;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Models\TicketHistoryEntry;
use Leantime\Domain\Tickets\Permissions\TicketsPermissions;
use Leantime\Domain\Tickets\Repositories\TicketHistory as TicketHistoryRepository;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;

/**
 * Change history of a ticket (the field changes recorded in zp_tickethistory), prepared for display.
 */
class TicketHistory extends BaseService
{
    /** Maximum number of history entries returned for one ticket. */
    public const MAX_ENTRIES = 200;

    /** Language keys of the field labels, keyed by the recorded changeType. */
    private const FIELD_LABELS = [
        'headline' => 'label.headline',
        'type' => 'label.todo_type',
        'description' => 'label.description',
        'project' => 'label.project',
        'priority' => 'label.priority',
        'deadline' => 'label.due_date',
        'editors' => 'label.editor',
        'fromDate' => 'label.working_date_from',
        'toDate' => 'label.working_date_to',
        'staging' => 'label.history_staging',
        'production' => 'label.history_production',
        'planHours' => 'label.planned_hours',
        'status' => 'label.todo_status',
    ];

    /**
     * @param  TicketHistoryRepository  $ticketHistoryRepo  The ticket history repository
     * @param  TicketRepository  $ticketRepository  The ticket repository (ticket lookup + labels)
     * @param  ProjectService  $projectService  The project service (project names)
     * @param  LanguageCore  $language  The language service
     */
    public function __construct(
        private TicketHistoryRepository $ticketHistoryRepo,
        private TicketRepository $ticketRepository,
        private ProjectService $projectService,
        private LanguageCore $language,
    ) {}

    /**
     * The change history of a ticket, newest first.
     *
     * Authorizes against the ticket's own project and fails closed: a missing ticket and a ticket
     * the current user cannot view both return false (no existence oracle).
     *
     * @param  int  $ticketId  The ticket id
     * @return array<int, TicketHistoryEntry>|false The entries, or false when not found/not allowed
     *
     * @api
     */
    #[RequiresPermission(TicketsPermissions::VIEW, entityScoped: true)]
    public function getTicketHistory(int $ticketId): array|false
    {
        if ($ticketId <= 0) {
            return false;
        }

        $ticket = $this->ticketRepository->getTicket($ticketId);
        if (! $ticket) {
            return false;
        }

        $projectId = (int) $ticket->projectId;
        if ($projectId <= 0 || ! $this->can(TicketsPermissions::VIEW, $projectId)) {
            return false;
        }

        $rows = $this->ticketHistoryRepo->getTicketChanges($ticketId, self::MAX_ENTRIES);
        $predecessors = $this->findPredecessors($ticketId, $rows);

        return $this->buildEntries($rows, $predecessors, $projectId);
    }

    /**
     * For a truncated history (more than MAX_ENTRIES rows), the latest change of each field that
     * happened before the retained window, so the first retained change of a field still gets its
     * "from" value. When the history was not truncated there is nothing older to look up.
     *
     * @param  int  $ticketId  The ticket id
     * @param  array<int, array<string, mixed>>  $rows  The retained rows, oldest first
     * @return array<string, array<string, mixed>> Preceding rows keyed by changeType
     */
    private function findPredecessors(int $ticketId, array $rows): array
    {
        if (count($rows) < self::MAX_ENTRIES) {
            return [];
        }

        $oldestRetainedRow = $rows[0];
        $fields = array_map(fn (array $row) => (string) ($row['changeType'] ?? ''), $rows);
        // The project in effect at the start of the window decides which status labels apply.
        $fields[] = 'project';
        $fields = array_unique($fields);

        $predecessors = [];
        foreach ($fields as $field) {
            $predecessor = $this->ticketHistoryRepo->getLatestChangeBefore(
                $ticketId,
                $field,
                (string) $oldestRetainedRow['dateModified'],
                (int) $oldestRetainedRow['id']
            );

            if ($predecessor !== null) {
                $predecessors[$field] = $predecessor;
            }
        }

        return $predecessors;
    }

    /**
     * Turns raw history rows (oldest first) into display entries (newest first).
     *
     * The history table stores only the new value of each change, so the previous value of a field
     * is taken from the preceding change of the same field (or from $predecessors for the first
     * retained change); the first change ever recorded has none.
     *
     * Status ids are project specific, so each status change is labelled with the labels of the
     * project the ticket was in at that time (tracked through the recorded 'project' changes).
     * When that project is unknown (before the first recorded move) or not viewable by the user,
     * the raw status id is shown.
     * Private on purpose: public service methods are JSON-RPC callable, and this one trusts its
     * input (it would resolve labels/names for arbitrary ids).
     *
     * @param  array<int, array<string, mixed>>  $rows  Rows from TicketHistoryRepository::getTicketChanges()
     * @param  array<string, array<string, mixed>>  $predecessors  Changes before $rows, keyed by changeType
     * @param  int  $projectId  The ticket's project (for status labels)
     * @return array<int, TicketHistoryEntry>
     */
    private function buildEntries(array $rows, array $predecessors, int $projectId): array
    {
        if (empty($rows)) {
            return [];
        }

        $statusLabelsByProject = [];
        $projectInEffect = $this->projectAtWindowStart($rows, $predecessors, $projectId);
        // Status ids are project-specific. The previous status value belongs to the project that was
        // in effect when it was recorded, which differs from the new value's project when one update
        // moved the ticket and changed its status together (the project row is recorded first).
        $projectOfPreviousStatus = $projectInEffect;
        $editorNames = $this->ticketHistoryRepo->getUserNames(
            $this->collectEditorIds(array_merge(array_values($predecessors), $rows))
        );
        $projectNames = [];

        $entries = [];
        $lastRawValueByField = [];
        foreach ($predecessors as $field => $predecessor) {
            $lastRawValueByField[$field] = isset($predecessor['changeValue']) ? (string) $predecessor['changeValue'] : null;
        }

        foreach ($rows as $rowIndex => $row) {
            $field = (string) ($row['changeType'] ?? '');
            $rawNewValue = isset($row['changeValue']) ? (string) $row['changeValue'] : null;
            $rawOldValue = $lastRawValueByField[$field] ?? null;
            $lastRawValueByField[$field] = $rawNewValue;

            if ($field === 'project') {
                $movedToProjectId = (int) $rawNewValue;
                $projectInEffect = $movedToProjectId > 0 ? $movedToProjectId : null;

                // A move that kept the same numeric status records no status row; the current status
                // then belongs to the destination project from here on. When the same update also
                // changed the status, that row still needs the source project for its old value.
                if (! $this->isFollowedBySameUpdateStatusChange($rows, $rowIndex)) {
                    $projectOfPreviousStatus = $projectInEffect;
                }
            }

            $newStatusLabels = [];
            $oldStatusLabels = [];
            if ($field === 'status') {
                $newStatusLabels = $this->statusLabelsFor($projectInEffect, $projectId, $statusLabelsByProject);
                $oldStatusLabels = $this->statusLabelsFor($projectOfPreviousStatus, $projectId, $statusLabelsByProject);
                $projectOfPreviousStatus = $projectInEffect;
            }

            $isDescriptionChange = $field === 'description';

            $oldValue = null;
            $newValue = null;
            if (! $isDescriptionChange) {
                $oldValue = $this->formatValue($field, $rawOldValue, $oldStatusLabels, $editorNames, $projectNames);
                $newValue = $this->formatValue($field, $rawNewValue, $newStatusLabels, $editorNames, $projectNames);
            }

            $entries[] = new TicketHistoryEntry(
                id: (int) ($row['id'] ?? 0),
                userId: isset($row['userId']) ? (int) $row['userId'] : null,
                userName: $this->changedByName($row),
                dateModified: (string) ($row['dateModified'] ?? ''),
                field: $field,
                fieldLabel: $this->fieldLabel($field),
                oldValue: $oldValue,
                newValue: $newValue,
                isDescriptionChange: $isDescriptionChange,
            );
        }

        return array_reverse($entries);
    }

    /**
     * Whether the change after $rows[$index] is a status change recorded by the same update.
     *
     * addTicketChange() writes one row per changed field with the same timestamp, the project row
     * before the status row.
     *
     * @param  array<int, array<string, mixed>>  $rows  History rows, oldest first
     * @param  int  $index  Index of a project row in $rows
     */
    private function isFollowedBySameUpdateStatusChange(array $rows, int $index): bool
    {
        $current = $rows[$index] ?? null;

        for ($next = $index + 1; isset($rows[$next]); $next++) {
            if (($rows[$next]['dateModified'] ?? null) !== ($current['dateModified'] ?? null)) {
                return false;
            }

            if (($rows[$next]['changeType'] ?? '') === 'status') {
                return true;
            }
        }

        return false;
    }

    /**
     * The project a ticket was in at the start of the history window.
     *
     * The latest move before the window wins. Without one, a window that contains no moves means
     * the ticket never moved, so it is the current project; a window that does contain moves starts
     * in an unknown project (the first move's origin is not recorded).
     *
     * @param  array<int, array<string, mixed>>  $rows  History rows, oldest first
     * @param  array<string, array<string, mixed>>  $predecessors  Changes before $rows, keyed by changeType
     * @param  int  $currentProjectId  The ticket's current project
     * @return int|null The project id, null when unknown
     */
    private function projectAtWindowStart(array $rows, array $predecessors, int $currentProjectId): ?int
    {
        if (isset($predecessors['project'])) {
            $predecessorProjectId = (int) ($predecessors['project']['changeValue'] ?? 0);

            return $predecessorProjectId > 0 ? $predecessorProjectId : null;
        }

        foreach ($rows as $row) {
            if (($row['changeType'] ?? '') === 'project') {
                return null;
            }
        }

        return $currentProjectId;
    }

    /**
     * Status labels of a project, memoized. The ticket's current project was authorized already;
     * any other project is only used if the user may view its tickets. Unknown or not viewable
     * projects give no labels, so the raw status id is shown.
     *
     * @param  int|null  $projectId  The project in effect, null when unknown
     * @param  int  $currentProjectId  The ticket's current (authorized) project
     * @param  array<int, array<int|string, mixed>>  $statusLabelsByProject  Memo (by reference)
     * @return array<int|string, mixed>
     */
    private function statusLabelsFor(?int $projectId, int $currentProjectId, array &$statusLabelsByProject): array
    {
        if ($projectId === null) {
            return [];
        }

        if (! isset($statusLabelsByProject[$projectId])) {
            $mayViewProject = $projectId === $currentProjectId || $this->can(TicketsPermissions::VIEW, $projectId);
            $statusLabelsByProject[$projectId] = $mayViewProject
                ? $this->ticketRepository->getStateLabels($projectId)
                : [];
        }

        return $statusLabelsByProject[$projectId];
    }

    /**
     * Translated label of a recorded field. Unknown fields fall back to the raw changeType.
     *
     * @param  string  $field  The recorded changeType
     */
    private function fieldLabel(string $field): string
    {
        if (! isset(self::FIELD_LABELS[$field])) {
            return $field;
        }

        return $this->language->__(self::FIELD_LABELS[$field]);
    }

    /**
     * Human readable value of a recorded change (status id → label, user id → name, ...).
     *
     * @param  string  $field  The recorded changeType
     * @param  string|null  $rawValue  The stored value
     * @param  array<int|string, mixed>  $statusLabels  Status labels of the project in effect for this change
     * @param  array<int, string>  $editorNames  User names keyed by user id
     * @param  array<int, string>  $projectNames  Memo of resolved project names (by reference)
     * @return string|null The display value, null when there is no value
     */
    private function formatValue(string $field, ?string $rawValue, array $statusLabels, array $editorNames, array &$projectNames): ?string
    {
        if ($rawValue === null) {
            return null;
        }

        return match ($field) {
            'status' => (string) ($statusLabels[$rawValue]['name'] ?? $rawValue),
            'priority' => (string) ($this->ticketRepository->priority[$rawValue] ?? $rawValue),
            'type' => $this->typeLabel($rawValue),
            'editors' => $this->editorName($rawValue, $editorNames),
            'project' => $this->projectName($rawValue, $projectNames),
            'deadline', 'fromDate', 'toDate' => $this->formatDate($rawValue),
            default => $rawValue,
        };
    }

    /**
     * Translated ticket type ("task" → "Task"); unknown types are returned as stored.
     */
    private function typeLabel(string $rawValue): string
    {
        $knownTypes = array_merge($this->ticketRepository->type, ['milestone']);

        if (! in_array(strtolower($rawValue), $knownTypes, true)) {
            return $rawValue;
        }

        return $this->language->__('label.'.strtolower($rawValue));
    }

    /**
     * Name of an assigned user, "Not assigned" for an empty assignment.
     *
     * @param  array<int, string>  $editorNames  User names keyed by user id
     */
    private function editorName(string $rawValue, array $editorNames): string
    {
        $userId = (int) $rawValue;

        if ($userId <= 0) {
            return $this->language->__('label.not_assigned_to_user');
        }

        return $editorNames[$userId] ?? '#'.$userId;
    }

    /**
     * Project name, only for projects the current user can view (getProject() self-authorizes);
     * other projects are shown by id so a move does not leak a foreign project's name.
     *
     * @param  array<int, string>  $projectNames  Memo of resolved project names (by reference)
     */
    private function projectName(string $rawValue, array &$projectNames): string
    {
        $projectId = (int) $rawValue;

        if (! isset($projectNames[$projectId])) {
            $project = $projectId > 0 ? $this->projectService->getProject($projectId) : false;
            $projectNames[$projectId] = is_array($project) && isset($project['name'])
                ? (string) $project['name']
                : '#'.$projectId;
        }

        return $projectNames[$projectId];
    }

    /**
     * A stored UTC datetime as user date + time; unparseable values are returned as stored.
     */
    private function formatDate(string $rawValue): string
    {
        $formatted = format($rawValue);
        $date = $formatted->date();

        if ($date === '') {
            return $rawValue;
        }

        return trim($date.' '.$formatted->time());
    }

    /**
     * Name of the user who made a change; deleted users show as "Unknown user".
     *
     * @param  array<string, mixed>  $row  A history row
     */
    private function changedByName(array $row): string
    {
        $name = trim(($row['firstname'] ?? '').' '.($row['lastname'] ?? ''));

        if ($name === '') {
            return $this->language->__('label.history_unknown_user');
        }

        return $name;
    }

    /**
     * Distinct user ids referenced by assignment changes.
     *
     * @param  array<int, array<string, mixed>>  $rows  History rows
     * @return array<int, int>
     */
    private function collectEditorIds(array $rows): array
    {
        $userIds = [];

        foreach ($rows as $row) {
            if (($row['changeType'] ?? '') !== 'editors') {
                continue;
            }

            $userId = (int) ($row['changeValue'] ?? 0);
            if ($userId > 0) {
                $userIds[$userId] = $userId;
            }
        }

        return array_values($userIds);
    }
}

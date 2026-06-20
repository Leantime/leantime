<?php

namespace Leantime\Domain\Tickets\Support;

use Illuminate\Support\Str;
use Leantime\Core\Support\AbstractEntityFormatter;
use Leantime\Domain\Projects\Models\Project;
use Leantime\Domain\Tickets\Models\Tickets;

/**
 * Ticket entity formatter for AI consumption.
 *
 * Formats ticket/task entities into structured markdown suitable for AI prompts,
 * embeddings, and other LLM operations.
 */
class TicketFormatter extends AbstractEntityFormatter
{
    /**
     * Fields to exclude from ticket formatting.
     */
    protected array $excludedFields = [
        'sortIndex',
        'timelineDate',
        'timelineDateToFinish',
        'timeToFinish',
        'timeFrom',
        'timeTo',
        'url',
        'doneTickets',
        'allTickets',
        'children',
        'editorProfileId',
    ];

    /**
     * Priority order for displaying ticket fields.
     */
    protected array $fieldPriority = [
        'id',
        'headline',
        'description',
        'type',
        'status',
        'priority',
        'projectName',
        'assignedTo',
        'dueDate',
        'storypoints',
        'planHours',
    ];

    public function __construct(
        protected Tickets $ticket,
        protected Project|array|null $project = null,
        protected array|false|null $subtasks = null
    ) {}

    /**
     * Get the entity type.
     */
    public function getEntityType(): string
    {
        return 'ticket';
    }

    /**
     * Get the entity ID.
     */
    public function getEntityId(): mixed
    {
        return $this->ticket->id;
    }

    /**
     * Prepare ticket data for formatting.
     */
    protected function prepareEntityData(array $context = []): array
    {
        $data = [
            'id' => $this->ticket->id,
            'headline' => $this->sanitizeValue($this->ticket->headline),
            'type' => $this->sanitizeValue($this->ticket->type),
            'description' => $this->sanitizeValue($this->ticket->description),
            'status' => $this->ticket->status,
            'priority' => $this->ticket->priority,
            'storypoints' => $this->sanitizeValue($this->ticket->storypoints),
            'dueDate' => $this->formatDate($this->ticket->dateToFinish),
            'planHours' => $this->sanitizeValue($this->ticket->planHours),
            'scheduledFrom' => $this->formatDate($this->ticket->editFrom),
            'scheduledTo' => $this->formatDate($this->ticket->editTo),
            'assignedTo' => $this->formatAssignedUser(),
            'createdBy' => $this->formatCreatedUser(),
            'projectId' => $this->ticket->projectId,
            'projectName' => $this->sanitizeValue($this->ticket->projectName),
            'clientName' => $this->sanitizeValue($this->ticket->clientName),
            'milestone' => $this->formatMilestone(),
            'acceptanceCriteria' => $this->sanitizeValue($this->ticket->acceptanceCriteria),
            'tags' => $this->sanitizeValue($this->ticket->tags),
            'dependingTicketId' => $this->ticket->dependingTicketId,
            'parentHeadline' => $this->sanitizeValue($this->ticket->parentHeadline),
            'lastUpdated' => $this->formatDate($this->ticket->date),
            'bookedHours' => $this->sanitizeValue($this->ticket->bookedHours),
            'hourRemaining' => $this->sanitizeValue($this->ticket->hourRemaining),
            'percentDone' => $this->sanitizeValue($this->ticket->percentDone),
        ];

        // Add project information if available
        if ($this->project) {
            if (is_array($this->project)) {
                $this->project = new Project($this->project);
            }

            $data['projectDetails'] = [
                'name' => $this->sanitizeValue($this->project->name),
                'type' => $this->sanitizeValue($this->project->type),
                'state' => $this->sanitizeValue($this->project->state),
                'progress' => $this->sanitizeValue($this->project->progress),
                'status' => $this->sanitizeValue($this->project->status),
            ];
        }

        if ($this->subtasks) {
            $data['subtasks'] = $this->formatSubtasks($this->subtasks);
        }

        return $data;
    }

    /**
     * Format the header section.
     */
    protected function formatHeader(array $data): string
    {
        $type = ! empty($data['type']) ? strtoupper($data['type']) : 'TASK';
        $projectInfo = ! empty($data['projectName']) ? " ({$data['projectName']})" : '';

        return "## {$type} #{$data['id']} - {$data['headline']}{$projectInfo}";
    }

    /**
     * Format the body with custom ticket-specific formatting.
     */
    protected function formatBody(array $data, array $context = []): string
    {
        $filteredData = $this->filterFields($data, $context);

        // Custom formatting for specific fields
        if (isset($filteredData['priority'])) {
            $filteredData['priority'] = $this->formatPriority($filteredData['priority']);
        }

        // Remove the duplicated header fields from body
        unset($filteredData['id'], $filteredData['headline'], $filteredData['projectName']);

        $sortedData = $this->sortFields($filteredData);

        return "\n".Str::toMarkdown($sortedData);
    }

    /**
     * Format a compact summary.
     */
    protected function formatSummary(array $data): string
    {
        $type = ! empty($data['type']) ? strtoupper($data['type']) : 'TASK';
        $status = $this->formatSimpleStatus($data['status'] ?? '');
        $assignee = ! empty($data['assignedTo']) ? " → {$data['assignedTo']}" : '';

        return "{$type} #{$data['id']}: {$data['headline']}{$status}{$assignee}";
    }

    /**
     * Format subtasks into a list string.
     */
    protected function formatSubtasks(array $subtasks): string
    {
        $subtaskData = '';
        foreach ($subtasks as $subtask) {
            $subtaskData .= ' #'.$subtask['id'].' '.$this->sanitizeValue($subtask['headline'])."\n";
        }

        return $subtaskData;
    }

    /**
     * Format the assigned user information.
     */
    protected function formatAssignedUser(): string
    {
        $firstName = $this->sanitizeValue($this->ticket->editorFirstname);
        $lastName = $this->sanitizeValue($this->ticket->editorLastname);

        if (empty($firstName) && empty($lastName)) {
            return 'Unassigned';
        }

        $name = trim($firstName.' '.$lastName);
        $id = $this->ticket->editorId;

        return ! empty($id) ? "{$name} (ID: {$id})" : $name;
    }

    /**
     * Format the user who created the ticket.
     */
    protected function formatCreatedUser(): string
    {
        $firstName = $this->sanitizeValue($this->ticket->userFirstname);
        $lastName = $this->sanitizeValue($this->ticket->userLastname);

        if (empty($firstName) && empty($lastName)) {
            return 'Unknown';
        }

        $name = trim($firstName.' '.$lastName);
        $id = $this->ticket->userId;

        return ! empty($id) ? "{$name} (ID: {$id})" : $name;
    }

    /**
     * Format milestone information.
     */
    protected function formatMilestone(): string
    {
        if (empty($this->ticket->milestoneHeadline) && empty($this->ticket->milestoneid)) {
            return 'No milestone';
        }

        $headline = $this->sanitizeValue($this->ticket->milestoneHeadline) ?: 'Untitled Milestone';
        $id = $this->ticket->milestoneid;

        return ! empty($id) ? "{$headline} (ID: {$id})" : $headline;
    }

    /**
     * Format status for summary (simple version).
     *
     * @param  mixed  $status
     */
    protected function formatSimpleStatus($status): string
    {
        if (empty($status)) {
            return '';
        }

        // Simple status indicators for summaries
        return match ((string) $status) {
            '1' => ' [NEW]',
            '2' => ' [IN PROGRESS]',
            '3' => ' [DONE]',
            default => " [{$status}]"
        };
    }
}

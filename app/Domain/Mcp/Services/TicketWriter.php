<?php

namespace Leantime\Domain\Mcp\Services;

use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Mcp\Auth\McpPrincipal;
use Leantime\Core\Mcp\McpException;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Users\Services\Users;

class TicketWriter
{
    public function __construct(
        private TicketRepository $ticketRepository,
        private TicketService $ticketService,
        private ProjectService $projectService,
        private Users $usersService,
        private LanguageCore $language,
    ) {}

    public function createTicket(McpPrincipal $principal, array $values): array
    {
        $projectId = (int) ($values['projectId'] ?? 0);
        $headline = trim((string) ($values['headline'] ?? ''));

        if ($projectId <= 0 || $headline === '') {
            throw new McpException('projectId and headline are required', -32602, 400);
        }

        $type = (string) ($values['type'] ?? 'task');
        if (! in_array($type, $this->ticketRepository->getType(), true)) {
            throw new McpException('Invalid ticket type', -32602, 400);
        }

        $preparedValues = [
            'headline' => $headline,
            'type' => $type,
            'description' => (string) ($values['description'] ?? ''),
            'projectId' => $projectId,
            'editorId' => (int) ($values['editorId'] ?? $principal->userId),
            'userId' => $principal->userId,
            'date' => gmdate('Y-m-d H:i:s'),
            'dateToFinish' => $values['dateToFinish'] ?? '',
            'timeToFinish' => $values['timeToFinish'] ?? '',
            'status' => (int) ($values['status'] ?? 3),
            'planHours' => $values['planHours'] ?? '',
            'tags' => (string) ($values['tags'] ?? ''),
            'sprint' => $values['sprint'] ?? '',
            'storypoints' => $values['storypoints'] ?? '',
            'hourRemaining' => $values['hourRemaining'] ?? '',
            'priority' => $values['priority'] ?? '',
            'acceptanceCriteria' => (string) ($values['acceptanceCriteria'] ?? ''),
            'editFrom' => $values['editFrom'] ?? '',
            'timeFrom' => $values['timeFrom'] ?? '',
            'editTo' => $values['editTo'] ?? '',
            'timeTo' => $values['timeTo'] ?? '',
            'dependingTicketId' => $values['dependingTicketId'] ?? '',
            'milestoneid' => $values['milestoneid'] ?? '',
            'collaborators' => is_array($values['collaborators'] ?? null) ? $values['collaborators'] : [],
        ];

        if (! array_key_exists($preparedValues['status'], $this->ticketRepository->getStatusList())) {
            throw new McpException('Invalid ticket status', -32602, 400);
        }

        $projectUsers = $this->projectService->getUsersAssignedToProject($projectId, true);
        $projectUserIds = array_map(static fn (array $user) => (int) $user['id'], $projectUsers);
        if (! in_array($preparedValues['editorId'], $projectUserIds, true)) {
            throw new McpException('Editor must belong to the project', -32602, 400);
        }

        foreach ($preparedValues['collaborators'] as $collaboratorId) {
            if (! in_array((int) $collaboratorId, $projectUserIds, true)) {
                throw new McpException('Collaborators must belong to the project', -32602, 400);
            }
        }

        $preparedValues = $this->ticketService->prepareTicketDates($preparedValues);
        $ticketId = $this->ticketRepository->addTicket($preparedValues);
        if ($ticketId === false) {
            throw new McpException('Ticket could not be created', -32000, 500);
        }

        $user = $this->usersService->getUser($principal->userId);
        $authorName = $user !== false ? (string) $user['firstname'] : $principal->tokenName;
        $subject = sprintf($this->language->__('email_notifications.new_todo_subject'), $ticketId, strip_tags($headline));
        $message = sprintf($this->language->__('email_notifications.new_todo_message'), $authorName, strip_tags($headline));

        $notification = new NotificationModel;
        $notification->url = [
            'url' => BASE_URL.'/dashboard/home#/tickets/showTicket/'.$ticketId,
            'text' => $this->language->__('email_notifications.new_todo_cta'),
        ];
        $notification->entity = $preparedValues + ['id' => $ticketId];
        $notification->module = 'tickets';
        $notification->action = 'created';
        $notification->projectId = $projectId;
        $notification->subject = $subject;
        $notification->authorId = $principal->userId;
        $notification->message = $message;
        $this->projectService->notifyProjectUsers($notification);

        $ticket = $this->ticketRepository->getTicket($ticketId);

        return [
            'ticketId' => $ticketId,
            'ticket' => $ticket,
        ];
    }

    public function updateTicket(McpPrincipal $principal, int $ticketId, string $expectedVersion, array $values): array
    {
        $ticket = $this->ticketRepository->getTicket($ticketId);
        if ($ticket === false) {
            throw new McpException('Ticket not found', -32004, 404);
        }

        if (($ticket->modified ?? null) !== $expectedVersion) {
            throw new McpException('Ticket version conflict', -32009, 409, [
                'ticketId' => $ticketId,
                'currentVersion' => $ticket->modified ?? null,
            ]);
        }

        if (array_key_exists('projectId', $values)) {
            throw new McpException('tickets.update does not support project moves; use a dedicated move tool', -32602, 400);
        }

        $projectId = (int) $ticket->projectId;
        $projectUsers = $this->projectService->getUsersAssignedToProject($projectId, true);
        $projectUserIds = array_map(static fn (array $user) => (int) $user['id'], $projectUsers);

        $patchValues = [];
        foreach (['headline', 'type', 'description', 'status', 'date', 'dateToFinish', 'sprint', 'storypoints', 'priority', 'hourRemaining', 'planHours', 'tags', 'editorId', 'editFrom', 'editTo', 'acceptanceCriteria', 'dependingTicketId', 'milestoneid'] as $field) {
            if (array_key_exists($field, $values)) {
                $patchValues[$field] = $values[$field];
            }
        }

        if (isset($patchValues['headline']) && trim((string) $patchValues['headline']) === '') {
            throw new McpException('headline cannot be empty', -32602, 400);
        }

        if (isset($patchValues['type']) && ! in_array((string) $patchValues['type'], $this->ticketRepository->getType(), true)) {
            throw new McpException('Invalid ticket type', -32602, 400);
        }

        if (isset($patchValues['priority']) && $patchValues['priority'] !== '' && ! array_key_exists((int) $patchValues['priority'], $this->ticketRepository->priority)) {
            throw new McpException('Invalid ticket priority', -32602, 400);
        }

        if (isset($patchValues['status'])) {
            $projectStatuses = $this->ticketRepository->getStateLabels($projectId);
            if (! array_key_exists((int) $patchValues['status'], $projectStatuses)) {
                throw new McpException('Invalid ticket status for this project', -32602, 400);
            }
        }

        if (isset($patchValues['editorId']) && ! in_array((int) $patchValues['editorId'], $projectUserIds, true)) {
            throw new McpException('Editor must belong to the ticket project', -32602, 400);
        }

        foreach (['dependingTicketId', 'milestoneid'] as $relationField) {
            if (isset($patchValues[$relationField]) && $patchValues[$relationField] !== '' && $patchValues[$relationField] !== null) {
                $relatedTicketId = (int) $patchValues[$relationField];
                if ($relatedTicketId === $ticketId) {
                    throw new McpException($relationField.' cannot reference the same ticket', -32602, 400);
                }

                if (! $this->ticketRepository->projectHasTicket($projectId, $relatedTicketId)) {
                    throw new McpException($relationField.' must reference a ticket in the same project', -32602, 400);
                }

                if ($relationField === 'milestoneid') {
                    $milestone = $this->ticketRepository->getTicket($relatedTicketId);
                    if ($milestone === false || $milestone->type !== 'milestone') {
                        throw new McpException('milestoneid must reference a milestone ticket', -32602, 400);
                    }
                }
            }
        }

        if (isset($patchValues['date']) || isset($patchValues['dateToFinish']) || isset($patchValues['editFrom']) || isset($patchValues['editTo'])) {
            $dateAware = [
                'date' => $patchValues['date'] ?? $ticket->date,
                'dateToFinish' => $patchValues['dateToFinish'] ?? $ticket->dateToFinish,
                'timeToFinish' => $values['timeToFinish'] ?? '',
                'editFrom' => $patchValues['editFrom'] ?? $ticket->editFrom,
                'timeFrom' => $values['timeFrom'] ?? '',
                'editTo' => $patchValues['editTo'] ?? $ticket->editTo,
                'timeTo' => $values['timeTo'] ?? '',
            ];
            $dateAware = $this->ticketService->prepareTicketDates($dateAware);
            foreach (['date', 'dateToFinish', 'editFrom', 'editTo'] as $dateField) {
                if (array_key_exists($dateField, $patchValues)) {
                    $patchValues[$dateField] = $dateAware[$dateField];
                }
            }
        }

        $collaborators = null;
        if (array_key_exists('collaborators', $values)) {
            if (! is_array($values['collaborators'])) {
                throw new McpException('collaborators must be an array of project user ids', -32602, 400);
            }

            $collaborators = array_values(array_unique(array_map('intval', $values['collaborators'])));
            foreach ($collaborators as $collaboratorId) {
                if (! in_array($collaboratorId, $projectUserIds, true)) {
                    throw new McpException('Collaborators must belong to the ticket project', -32602, 400);
                }
            }
        }

        $updated = $this->ticketRepository->patchTicketForActor($principal->userId, $ticketId, $patchValues, $expectedVersion);
        if (! $updated) {
            throw new McpException('Ticket version conflict', -32009, 409, [
                'ticketId' => $ticketId,
                'currentVersion' => $this->ticketRepository->getTicket($ticketId)?->modified,
            ]);
        }

        if ($collaborators !== null) {
            $this->ticketRepository->removeCollaborators($ticketId);
            $this->ticketRepository->addCollaborators($ticketId, $collaborators, $principal->userId);
        }

        $user = $this->usersService->getUser($principal->userId);
        $authorName = $user !== false ? (string) $user['firstname'] : $principal->tokenName;
        $updatedTicket = $this->ticketRepository->getTicket($ticketId);
        $headline = $updatedTicket !== false ? (string) $updatedTicket->headline : (string) $ticket->headline;

        $notification = new NotificationModel;
        $notification->url = [
            'url' => BASE_URL.'/dashboard/home#/tickets/showTicket/'.$ticketId,
            'text' => $this->language->__('email_notifications.todo_update_cta'),
        ];
        $notification->entity = $patchValues + ['id' => $ticketId, 'projectId' => $projectId];
        $notification->module = 'tickets';
        $notification->action = 'updated';
        $notification->projectId = $projectId;
        $notification->subject = sprintf($this->language->__('email_notifications.todo_update_subject'), $ticketId, strip_tags($headline));
        $notification->authorId = $principal->userId;
        $notification->message = sprintf($this->language->__('email_notifications.todo_update_message'), $authorName, strip_tags($headline));
        $this->projectService->notifyProjectUsers($notification);

        return [
            'ticketId' => $ticketId,
            'ticket' => $updatedTicket,
        ];
    }
}

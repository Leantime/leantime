<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Controller\HxComponent;
use Leantime\Domain\Tickets\Events\SubtasksUpdated;
use Leantime\Domain\Tickets\Services\Tickets;

/**
 * Subtasks list component for a ticket.
 *
 * Mounted with <x-global::hx :for="self::class" :id="$ticketId" />. It listens for
 * {@see SubtasksUpdated}, which the Tickets service dispatches whenever a subtask is added, edited
 * or deleted, so a subtask change made elsewhere (e.g. the dashboard to-do widget) refreshes this
 * list and a change made here refreshes the others — both sides reference the same event class.
 */
class Subtasks extends HxComponent
{
    protected static string $view = 'tickets::partials.subtasks';

    /** Refresh swaps the inner content so the mount wrapper keeps its id + event triggers. */
    public static string $swap = 'innerHTML';

    private Tickets $ticketService;

    public function init(Tickets $ticketService): void
    {
        $this->ticketService = $ticketService;
    }

    public static function route(): string
    {
        return 'tickets/subtasks';
    }

    /**
     * @return array<int, class-string<SubtasksUpdated>>
     */
    public static function listensTo(): array
    {
        return [SubtasksUpdated::class];
    }

    /**
     * @return array<int, class-string<SubtasksUpdated>>
     */
    public static function emits(): array
    {
        return [SubtasksUpdated::class];
    }

    public function save(): void
    {
        $getParams = $_GET;
        $params = $_POST;

        $ticket = $this->ticketService->getTicket($getParams['ticketId']);

        if ($this->ticketService->upsertSubtask($params, $ticket)) {
            $this->tpl->setNotification($this->language->__('notifications.subtask_saved'), 'success');
        } else {
            $this->tpl->setNotification($this->language->__('notifications.subtask_save_error'), 'error');
        }

        $ticketSubtasks = $this->ticketService->getAllSubtasks($ticket->id);
        $statusLabels = $this->ticketService->getStatusLabels(session('currentProject'));
        $efforts = $this->ticketService->getEffortLabels();

        $this->tpl->assign('ticket', $ticket);
        $this->tpl->assign('ticketSubtasks', $ticketSubtasks);
        $this->tpl->assign('statusLabels', $statusLabels);
        $this->tpl->assign('efforts', $efforts);
    }

    public function get(): void
    {
        if (! $this->incomingRequest->getMethod() == 'GET') {
            throw new \Exception('This endpoint only supports GET requests');
        }

        $getVars = $_GET;
        // Accept the ticket id from the path (contract-driven mount → query['id']) or the legacy
        // ?ticketId= query used by the in-partial forms.
        $id = $getVars['ticketId'] ?? $getVars['id'] ?? null;

        $ticket = $this->ticketService->getTicket($id);
        $ticketSubtasks = $this->ticketService->getAllSubtasks((int) $id);
        $statusLabels = $this->ticketService->getStatusLabels(session('currentProject'));
        $efforts = $this->ticketService->getEffortLabels();

        $this->tpl->assign('ticket', $ticket);
        $this->tpl->assign('ticketSubtasks', $ticketSubtasks);
        $this->tpl->assign('statusLabels', $statusLabels);
        $this->tpl->assign('efforts', $efforts);
    }

    public function delete()
    {
        $getVars = $_GET;
        $id = $getVars['ticketId'];
        $parentId = $getVars['parentTicket'];

        if ($this->ticketService->delete($id)) {
            $this->tpl->setNotification($this->language->__('notifications.subtask_deleted'), 'success');
        } else {
            $this->tpl->setNotification($this->language->__('notifications.subtask_delete_error'), 'error');
        }

        $ticket = $this->ticketService->getTicket($parentId);
        $ticketSubtasks = $this->ticketService->getAllSubtasks($parentId);
        $statusLabels = $this->ticketService->getStatusLabels(session('currentProject'));
        $efforts = $this->ticketService->getEffortLabels();

        $this->tpl->assign('ticket', $ticket);
        $this->tpl->assign('ticketSubtasks', $ticketSubtasks);
        $this->tpl->assign('statusLabels', $statusLabels);
        $this->tpl->assign('efforts', $efforts);
    }
}

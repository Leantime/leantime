<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Http\Controller\HtmxController;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Timesheets\Services\Timesheets;

class Subtasks extends HtmxController
{
    protected static string $view = 'tickets::partials.subtasks';

    private Tickets $ticketService;

    /**
     * Controller constructor
     *
     * @param  Timesheets  $timesheetService
     */
    public function init(Tickets $ticketService): void
    {
        $this->ticketService = $ticketService;
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
        $id = $getVars['ticketId'];

        $ticket = $this->ticketService->getTicket($id);
        $ticketSubtasks = $this->ticketService->getAllSubtasks($id);
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

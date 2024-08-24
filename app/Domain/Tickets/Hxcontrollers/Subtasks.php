<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Core\Language;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 *
 */
class Subtasks extends HtmxController
{
    /**
     * @var string
     */
    protected static string $view = 'tickets::partials.subtasks';

    /**
     * @var Tickets
     */
    private Tickets $ticketService;

    private Language $language;

    /**
     * Controller constructor
     *
     * @param Timesheets $timesheetService
     * @return void
     */
    public function init(Tickets $ticketService, Language $language): void
    {
        $this->ticketService = $ticketService;
        $this->language = $language;
    }

    /**
     * @return void
     */
    public function save(): void
    {
        $getParams = $_GET;
        $params = $_POST;


        $ticket = $this->ticketService->getTicket($getParams["ticketId"]);

        if ($this->ticketService->upsertSubtask($params, $ticket)) {
            $this->tpl->setNotification($this->language->__("notifications.subtask_saved"), "success");
        } else {
            $this->tpl->setNotification($this->language->__("notifications.subtask_save_error"), "error");
        }

        $this->setHTMXEvent("HTMX.ShowNotification");
        $ticketSubtasks = $this->ticketService->getAllSubtasks($ticket->id);
        $statusLabels  = $this->ticketService->getStatusLabels(session("currentProject"));
        $efforts = $this->ticketService->getEffortLabels();

        $this->tpl->assign("ticket", $ticket);
        $this->tpl->assign("ticketSubtasks", $ticketSubtasks);
        $this->tpl->assign("statusLabels", $statusLabels);
        $this->tpl->assign("efforts", $efforts);
    }

    /**
     * @return void
     */
    public function get(): void
    {

        if (! $this->incomingRequest->getMethod() == 'GET') {
            throw new \Exception('This endpoint only supports GET requests');
        }

        $getVars = $_GET;
        $id = $getVars["ticketId"];

        $ticket = $this->ticketService->getTicket($id);
        $ticketSubtasks = $this->ticketService->getAllSubtasks($id);
        $statusLabels  = $this->ticketService->getStatusLabels(session("currentProject"));
        $efforts = $this->ticketService->getEffortLabels();


        $this->tpl->assign("ticket", $ticket);
        $this->tpl->assign("ticketSubtasks", $ticketSubtasks);
        $this->tpl->assign("statusLabels", $statusLabels);
        $this->tpl->assign("efforts", $efforts);
    }

    public function delete()
    {

        $getVars = $_GET;
        $id = $getVars["ticketId"];
        $parentId = $getVars["parentTicket"];

        if ($this->ticketService->delete($id)) {
            $this->tpl->setNotification($this->language->__("notifications.subtask_deleted"), "success");
        } else {
            $this->tpl->setNotification($this->language->__("notifications.subtask_delete_error"), "error");
        }

        $ticket = $this->ticketService->getTicket($parentId);
        $ticketSubtasks = $this->ticketService->getAllSubtasks($parentId);
        $statusLabels  = $this->ticketService->getStatusLabels(session("currentProject"));
        $efforts = $this->ticketService->getEffortLabels();

        $this->setHTMXEvent("HTMX.ShowNotification");
        $this->tpl->assign("ticket", $ticket);
        $this->tpl->assign("ticketSubtasks", $ticketSubtasks);
        $this->tpl->assign("statusLabels", $statusLabels);
        $this->tpl->assign("efforts", $efforts);
    }
}

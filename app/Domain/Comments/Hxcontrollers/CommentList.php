<?php

namespace Leantime\Domain\Comments\Hxcontrollers;

use Illuminate\Support\Facades\Lang;
use Leantime\Core\HtmxController;
use Leantime\Core\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Domain\Comments\Services\Comments;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 *
 */
class CommentList extends HtmxController
{
    /**
     * @var string
     */
    protected static string $view = 'comments::partials.commentList';

    /**
     * @var Tickets
     */
    private Comments $commentService;

    private Language $language;

    /**
     * Controller constructor
     *
     * @param Comments $commentService
     * @return void
     */
    public function init(Comments $commentService, Language $language): void
    {
        $this->commentService = $commentService;
        $this->language = $language;
    }

    /**
     * @return void
     */
    public function save(): void
    {
        $getParams = $_GET;

        $module = $this->incomingRequest->input("module");
        $moduleId = $this->incomingRequest->input("moduleId");
        $includeStatus = $this->incomingRequest->input("includeStatus");
        $editComment = filter_var($this->incomingRequest->input("editComment"), FILTER_SANITIZE_NUMBER_INT);

        if ($editComment > 0 && $this->commentService->editComment($_POST, $editComment)) {
                $this->tpl->setNotification($this->language->__("notifications.comment_saved_success"), "success");
        } elseif ($editComment == "" && $this->commentService->addComment($_POST, $module, $moduleId)) {
                $this->tpl->setNotification($this->language->__("notifications.comment_create_success"), "success");
        } else {
            $this->tpl->setNotification($this->language->__("notifications.comment_create_error"), "error");
        }

        $comments = $this->commentService->getComments($module, $moduleId);

        $this->tpl->assign("module", $module);
        $this->tpl->assign("moduleId", $moduleId);
        $this->tpl->assign("includeStatus", $includeStatus);
        $this->tpl->assign("comments", $comments);

        $this->setHTMXEvent("HTMX.ShowNotification");
    }

    /**
     * @return void
     */
    public function get(): void
    {

        if (! $this->incomingRequest->getMethod() == 'GET') {
            throw new \Exception('This endpoint only supports GET requests');
        }

        $module = $this->incomingRequest->input("module");
        $moduleId = $this->incomingRequest->input("moduleId");
        $includeStatus = $this->incomingRequest->input("includeStatus");

        $comments = $this->commentService->getComments($module, $moduleId);

        $this->tpl->assign("module", $module);
        $this->tpl->assign("moduleId", $moduleId);
        $this->tpl->assign("includeStatus", $includeStatus);
        $this->tpl->assign("comments", $comments);
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

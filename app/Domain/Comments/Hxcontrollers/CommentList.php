<?php

namespace Leantime\Domain\Comments\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Core\Http\IncomingRequest;
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
        $simpleStatusField = $this->incomingRequest->input("simpleStatusField");
        $editComment = filter_var($this->incomingRequest->input("editComment"), FILTER_SANITIZE_NUMBER_INT);

        if($includeStatus == true) {
            $_POST['status'] = $simpleStatusField;
        }

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
        $id = $getVars["commentId"];

        if ($this->commentService->deleteComment($id)) {
            $this->tpl->setNotification($this->language->__("notifications.comment_deleted"), "success");
        } else {
            $this->tpl->setNotification($this->language->__("notifications.comment_delete_error"), "error");
        }

        $module = $this->incomingRequest->input("module");
        $moduleId = $this->incomingRequest->input("moduleId");
        $includeStatus = $this->incomingRequest->input("includeStatus");

        $comments = $this->commentService->getComments($module, $moduleId);

        $this->tpl->assign("module", $module);
        $this->tpl->assign("moduleId", $moduleId);
        $this->tpl->assign("includeStatus", $includeStatus);
        $this->tpl->assign("comments", $comments);

        $this->setHTMXEvent("HTMX.ShowNotification");
    }
}

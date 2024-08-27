<?php

namespace Leantime\Domain\Ideas\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
    use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
    use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;

    /**
     *
     */
    class IdeaDialog extends Controller
    {
        private IdeaRepository $ideaRepo;
        private CommentRepository $commentsRepo;
        private TicketService $ticketService;
        private ProjectService $projectService;

        /**
         * init - initialize private variables
         *
         * @access public
         *
         */
        public function init(
            IdeaRepository $ideaRepo,
            CommentRepository $commentsRepo,
            TicketService $ticketService,
            ProjectService $projectService
        ) {
            $this->ideaRepo = $ideaRepo;
            $this->commentsRepo = $commentsRepo;
            $this->ticketService = $ticketService;
            $this->projectService = $projectService;
        }

        /**
         * get - handle get requests
         *
         * @access public
         *
         */
        public function get($params)
        {
            if (isset($params['id'])) {
                //Delete comment
                if (isset($params['delComment']) === true) {
                    $commentId = (int)($params['delComment']);
                    $this->commentsRepo->deleteComment($commentId);
                    $this->tpl->setNotification($this->language->__('notifications.comment_deleted'), "success", "ideacomment_deleted");
                }

                //Delete milestone relationship
                if (isset($params['removeMilestone']) === true) {
                    $milestoneId = (int)($params['removeMilestone']);
                    $this->ideaRepo->patchCanvasItem($params['id'], array("milestoneId" => ''));
                    $this->tpl->setNotification($this->language->__('notifications.milestone_detached'), "success");
                }

                $canvasItem = $this->ideaRepo->getSingleCanvasItem($params['id']);
                if ($canvasItem['box'] == "0") {
                    $canvasItem['box'] = "idea";
                }
                $comments = $this->commentsRepo->getComments('idea', $canvasItem['id']);
                $this->tpl->assign('numComments', $this->commentsRepo->countComments('ideas', $canvasItem['id']));

            } else {

                $type = $params['type'] ?? "idea";

                $canvasItem = array(
                    "id" => "",
                    "box" => $type,
                    "tags" => '',
                    "description" => "",
                    "status" => "idea",
                    "assumptions" => "",
                    "data" => "",
                    "conclusion" => "",
                    "milestoneHeadline" => "",
                    "milestoneId" => "",
                );

                $comments = [];
            }

            $this->tpl->assign('comments', $comments);

            $allProjectMilestones = $this->ticketService->getAllMilestones(["sprint" => '', "type" => "milestone", "currentProject" => session("currentProject")]);
            $this->tpl->assign('milestones', $allProjectMilestones);
            $this->tpl->assign('canvasTypes', $this->ideaRepo->canvasTypes);
            $this->tpl->assign('canvasItem', $canvasItem);
            return $this->tpl->displayPartial('ideas.ideaDialog');
        }

        /**
         * post - handle post requests
         *
         * @access public
         *
         */
        public function post($params)
        {

            if (isset($params['comment']) === true) {
                if ($params['text'] != '') {
                    $values = array(
                        'text' => $params['text'],
                        'date' => date("Y-m-d H:i:s"),
                        'userId' => (session("userdata.id")),
                        'moduleId' => (int)$_GET['id'],
                        'commentParent' => ($params['father']),
                    );

                    $message = $this->commentsRepo->addComment($values, 'idea');
                    $values["id"] = $message;
                    $this->tpl->setNotification($this->language->__('notifications.comment_create_success'), "success");

                    $subject = $this->language->__('email_notifications.new_comment_idea_subject');
                    $actual_link = BASE_URL . "/ideas/ideaDialog/" . (int)$_GET['id'];
                    $message = sprintf(
                        $this->language->__('email_notifications.new_comment_idea_message'),
                        session("userdata.name")
                    );


                    $notification = app()->make(NotificationModel::class);
                    $notification->url = array(
                        "url" => $actual_link,
                        "text" => $this->language->__('email_notifications.new_comment_idea_cta'),
                    );
                    $notification->entity = $values;
                    $notification->module = "comments";
                    $notification->projectId = session("currentProject");
                    $notification->subject = $subject;
                    $notification->authorId = session("userdata.id");
                    $notification->message = $message;

                    $this->projectService->notifyProjectUsers($notification);

                    return Frontcontroller::redirect(BASE_URL . "/ideas/ideaDialog/" . (int)$_GET['id']);
                }
            }

            //changeItem is set for new or edited item changes.
            if (isset($params['changeItem'])) {
                if (isset($params['itemId']) && $params['itemId'] != '') {
                    if (isset($params['description']) === true) {
                        $currentCanvasId = (int)session("currentIdeaCanvas");

                        $canvasItem = array(
                            "box" => $params['box'],
                            "author" => session("userdata.id"),
                            "description" => $params['description'],
                            "status" => $params['status'],
                            "assumptions" => "",
                            "data" => $params['data'],
                            "conclusion" => "",
                            "tags" => $params['tags'],
                            "itemId" => $params['itemId'],
                            "canvasId" => $currentCanvasId,
                            "milestoneId" => $params['milestoneId'],
                            "id" => $params['itemId'],
                        );

                        if (isset($params['newMilestone']) && $params['newMilestone'] != '') {
                            $milestone = array();
                            $milestone['headline'] = $params['newMilestone'];
                            $milestone['tags'] = "#ccc";
                            $milestone['editFrom'] = dtHelper()->userNow()->formatDateForUser();
                            $milestone['editTo'] = dtHelper()->userNow()->addDays(7)->formatDateForUser();
                            $id = $this->ticketService->quickAddMilestone($milestone);
                            if ($id !== false) {
                                $canvasItem['milestoneId'] = $id;
                            }
                        }

                        if (isset($params['existingMilestone']) && $params['existingMilestone'] != '') {
                            $canvasItem['milestoneId'] = $params['existingMilestone'];
                        }

                        $this->ideaRepo->editCanvasItem($canvasItem);

                        $comments = $this->commentsRepo->getComments('leancanvasitem', $params['itemId']);
                        $this->tpl->assign(
                            'numComments',
                            $this->commentsRepo->countComments('leancanvasitem', $params['itemId'])
                        );
                        $this->tpl->assign('comments', $comments);

                        $this->tpl->setNotification($this->language->__('notification.idea_edited'), 'success');

                        $subject = $this->language->__('email_notifications.idea_edited_subject');
                        $actual_link = BASE_URL . "/ideas/ideaDialog/" . (int)$params['itemId'];
                        $message = sprintf(
                            $this->language->__('notification.idea_edited'),
                            session("userdata.name"),
                            $params['description']
                        );


                        $notification = app()->make(NotificationModel::class);
                        $notification->url = array(
                            "url" => $actual_link,
                            "text" => $this->language->__('email_notifications.idea_edited_cta'),
                        );

                        $notification->entity = $canvasItem;
                        $notification->module = "ideas";
                        $notification->projectId = session("currentProject");
                        $notification->subject = $subject;
                        $notification->authorId = session("userdata.id");
                        $notification->message = $message;

                        $this->projectService->notifyProjectUsers($notification);

                        return Frontcontroller::redirect(BASE_URL . "/ideas/ideaDialog/" . (int)$params['itemId']);
                    } else {
                        $this->tpl->setNotification($this->language->__("notification.please_enter_title"), 'error');
                    }
                } else {
                    if (isset($_POST['description']) === true) {
                        $currentCanvasId = (int)session("currentIdeaCanvas");

                        $canvasItem = array(
                            "box" => $params['box'],
                            "author" => session("userdata.id"),
                            "description" => $params['description'],
                            "status" => $params['status'],
                            "assumptions" => "",
                            "data" => $params['data'],
                            "conclusion" => "",
                            "canvasId" => $currentCanvasId,
                        );

                        $id = $this->ideaRepo->addCanvasItem($canvasItem);
                        $canvasItem["id"] = $id;

                        $subject = $this->language->__('email_notifications.idea_created_subject');
                        $actual_link = BASE_URL . "/ideas/ideaDialog/" . $id;
                        $message = sprintf($this->language->__('email_notifications.idea_created_message'), session("userdata.name"), $params['description']);


                        $notification = app()->make(NotificationModel::class);
                        $notification->url = array(
                            "url" => $actual_link,
                            "text" => $this->language->__('email_notifications.idea_created_subject'),
                        );
                        $notification->entity = $canvasItem;
                        $notification->module = "ideas";
                        $notification->projectId = session("currentProject");
                        $notification->subject = $subject;
                        $notification->authorId = session("userdata.id");
                        $notification->message = $message;

                        $this->projectService->notifyProjectUsers($notification);

                        $this->tpl->setNotification($this->language->__('notification.idea_created'), 'success', 'idea_created');

                        return Frontcontroller::redirect(BASE_URL . "/ideas/ideaDialog/" . (int)$id);
                    } else {
                        $this->tpl->setNotification($this->language->__("notification.please_enter_title"), 'error');
                    }
                }
            }

            $this->tpl->assign('canvasTypes', $this->ideaRepo->canvasTypes);
            $this->tpl->assign('canvasItem', $this->ideaRepo->getSingleCanvasItem($_GET['id']));
            return $this->tpl->displayPartial('ideas.ideaDialog');
        }

        /**
         * put - handle put requests
         *
         * @access public
         *
         */
        public function put($params)
        {
        }

        /**
         * delete - handle delete requests
         *
         * @access public
         *
         */
        public function delete($params)
        {
        }
    }

}

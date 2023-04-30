<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    use DateTime;
    use DateInterval;

    class ideaDialog extends controller
    {
        private repositories\ideas $ideaRepo;

        private services\tickets $ticketService;
        private repositories\comments $commentsRepo;
        private services\projects $projectService;

        /**
         * init - initialize private variables
         *
         * @access public
         *
         */
        public function init()
        {

            $this->ideaRepo = new repositories\ideas();

            $this->ticketService = new services\tickets();
            $this->commentsRepo = new repositories\comments();
            $this->projectService = new services\projects();
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
                    $this->tpl->setNotification($this->language->__('notifications.comment_deleted'), "success");
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
                if (isset($params['type'])) {
                    $type = $params['type'];
                } else {
                    $type = "idea";
                }

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
                    "milestoneId" => ""
                );

                $comments = [];
            }

            $this->tpl->assign('comments', $comments);
            $this->tpl->assign("milestones", $this->ticketService->getAllMilestones($_SESSION["currentProject"]));
            $this->tpl->assign('canvasTypes', $this->ideaRepo->canvasTypes);
            $this->tpl->assign('canvasItem', $canvasItem);
            $this->tpl->displayPartial('ideas.ideaDialog');
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
                if($params['text'] != '') {
                    $values = array(
                        'text' => $params['text'],
                        'date' => date("Y-m-d H:i:s"),
                        'userId' => ($_SESSION['userdata']['id']),
                        'moduleId' => (int)$_GET['id'],
                        'commentParent' => ($params['father'])
                    );

                    $message = $this->commentsRepo->addComment($values, 'idea');
                    $values["id"] = $message;
                    $this->tpl->setNotification($this->language->__('notifications.comment_create_success'), "success");

                    $subject = $this->language->__('email_notifications.new_comment_idea_subject');
                    $actual_link = BASE_URL . "/ideas/ideaDialog/" . (int)$_GET['id'];
                    $message = sprintf(
                        $this->language->__('email_notifications.new_comment_idea_message'),
                        $_SESSION["userdata"]["name"]
                    );


                    $notification = new models\notifications\notification();
                    $notification->url = array(
                        "url" => $actual_link,
                        "text" => $this->language->__('email_notifications.new_comment_idea_cta')
                    );
                    $notification->entity = $values;
                    $notification->module = "comments";
                    $notification->projectId = $_SESSION['currentProject'];
                    $notification->subject = $subject;
                    $notification->authorId = $_SESSION['userdata']['id'];
                    $notification->message = $message;

                    $this->projectService->notifyProjectUsers($notification);

                    $this->tpl->redirect(BASE_URL . "/ideas/ideaDialog/" . (int)$_GET['id']);
                }
            }

            //changeItem is set for new or edited item changes.
            if (isset($params['changeItem'])) {
                if (isset($params['itemId']) && $params['itemId'] != '') {
                    if (isset($params['description']) === true) {
                        $currentCanvasId = (int)$_SESSION['currentIdeaCanvas'];

                        $canvasItem = array(
                            "box" => $params['box'],
                            "author" => $_SESSION['userdata']["id"],
                            "description" => $params['description'],
                            "status" => $params['status'],
                            "assumptions" => "",
                            "data" => $params['data'],
                            "conclusion" => "",
                            "tags" => $params['tags'],
                            "itemId" => $params['itemId'],
                            "canvasId" => $currentCanvasId,
                            "milestoneId" => $params['milestoneId'],
                            "id" => $params['itemId']
                        );

                        if (isset($params['newMilestone']) && $params['newMilestone'] != '') {
                            $milestone = array();
                            $milestone['headline'] = $params['newMilestone'];
                            $milestone['tags'] = "#ccc";
                            $milestone['editFrom'] = date("Y-m-d");
                            $milestone['editTo'] = date("Y-m-d", strtotime("+1 week"));
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
                            $_SESSION["userdata"]["name"],
                            $params['description']
                        );


                        $notification = new models\notifications\notification();
                        $notification->url = array(
                            "url" => $actual_link,
                            "text" => $this->language->__('email_notifications.idea_edited_cta')
                        );

                        $notification->entity = $canvasItem;
                        $notification->module = "ideas";
                        $notification->projectId = $_SESSION['currentProject'];
                        $notification->subject = $subject;
                        $notification->authorId = $_SESSION['userdata']['id'];
                        $notification->message = $message;

                        $this->projectService->notifyProjectUsers($notification);


                        $this->tpl->redirect(BASE_URL . "/ideas/ideaDialog/" . (int)$params['itemId']);
                    } else {
                        $this->tpl->setNotification($this->language->__("notification.please_enter_title"), 'error');
                    }
                } else {
                    if (isset($_POST['description']) === true) {
                        $currentCanvasId = (int)$_SESSION['currentIdeaCanvas'];

                        $canvasItem = array(
                            "box" => $params['box'],
                            "author" => $_SESSION['userdata']["id"],
                            "description" => $params['description'],
                            "status" => $params['status'],
                            "assumptions" => "",
                            "data" => $params['data'],
                            "conclusion" => "",
                            "canvasId" => $currentCanvasId
                        );

                        $id = $this->ideaRepo->addCanvasItem($canvasItem);
                        $canvasItem["id"] = $id;

                        $subject = $this->language->__('email_notifications.idea_created_subject');
                        $actual_link = BASE_URL . "/ideas/ideaDialog/" . $id;
                        $message = sprintf($this->language->__('email_notifications.idea_created_message'), $_SESSION["userdata"]["name"], $params['description']);


                        $notification = new models\notifications\notification();
                        $notification->url = array(
                            "url" => $actual_link,
                            "text" => $this->language->__('email_notifications.idea_created_subject')
                        );
                        $notification->entity = $canvasItem;
                        $notification->module = "ideas";
                        $notification->projectId = $_SESSION['currentProject'];
                        $notification->subject = $subject;
                        $notification->authorId = $_SESSION['userdata']['id'];
                        $notification->message = $message;

                        $this->projectService->notifyProjectUsers($notification);

                        $this->tpl->setNotification($this->language->__('notification.idea_created'), 'success');

                        $this->tpl->redirect(BASE_URL . "/ideas/ideaDialog/" . (int)$id);
                    } else {
                        $this->tpl->setNotification($this->language->__("notification.please_enter_title"), 'error');
                    }
                }
            }



            $this->tpl->assign('canvasTypes', $this->ideaRepo->canvasTypes);
            $this->tpl->assign('canvasItem', $this->ideaRepo->getSingleCanvasItem($_GET['id']));
            $this->tpl->displayPartial('ideas.ideaDialog');
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

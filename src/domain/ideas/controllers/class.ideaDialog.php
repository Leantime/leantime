<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    use \DateTime;
    use \DateInterval;


    class ideaDialog
    {

        private $tpl;
        private $projects;
        private $sprintService;
        private $language;

        /**
         * constructor - initialize private variables
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function __construct()
        {

            $this->tpl = new core\template();
            $this->ideaRepo = new repositories\ideas();
            $this->sprintService = new services\sprints();
            $this->ticketRepo = new repositories\tickets();
            $this->ticketService = new services\tickets();
            $this->commentsRepo = new repositories\comments();
            $this->projectService = new services\projects();
            $this->language = new core\language();
        }

        /**
         * get - handle get requests
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function get($params)
        {
            if(isset($params['id'])) {

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
                if($canvasItem['box'] == "0"){$canvasItem['box'] = "idea";}
                $comments = $this->commentsRepo->getComments('idea', $canvasItem['id']);
                $this->tpl->assign('numComments', $this->commentsRepo->countComments('ideas', $canvasItem['id']));

            }else{

                if(isset($params['type'])) {
                    $type=$params['type'];
                } else {
                    $type = "idea";
                }

                $canvasItem = array(
                    "id"=>"",
                    "box" => $params['type'],
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
            $this->tpl->assign("milestones",  $this->ticketService->getAllMilestones($_SESSION["currentProject"]));
            $this->tpl->assign('canvasTypes',  $this->ideaRepo->canvasTypes);
            $this->tpl->assign('canvasItem', $canvasItem);
            $this->tpl->displayPartial('ideas.ideaDialog');
        }

        /**
         * post - handle post requests
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function post($params)
        {

            //changeItem is set for new or edited item changes.
            if(isset($params['changeItem'])) {

                if(isset($params['itemId']) && $params['itemId'] != '') {

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
                            "itemId" => $params['itemId'],
                            "canvasId" => $currentCanvasId,
                            "milestoneId" => $params['milestoneId']
                        );

                        if (isset($params['newMilestone']) && $params['newMilestone'] != '') {

                            $params['headline'] = $params['newMilestone'];
                            $params['tags'] = "#ccc";
                            $params['editFrom'] = date("Y-m-d");
                            $params['editTo'] = date("Y-m-d", strtotime("+1 week"));
                            $id = $this->ticketService->quickAddMilestone($params);
                            if ($id !== false) {
                                $canvasItem['milestoneId'] = $id;
                            }
                        }

                        if (isset($params['existingMilestone']) && $params['existingMilestone'] != '') {
                            $canvasItem['milestoneId'] = $params['existingMilestone'];
                        }

                        $this->ideaRepo->editCanvasItem($canvasItem);

                        $comments = $this->commentsRepo->getComments('leancanvasitem', $params['itemId']);
                        $this->tpl->assign('numComments',
                            $this->commentsRepo->countComments('leancanvasitem', $params['itemId']));
                        $this->tpl->assign('comments', $comments);

                        $this->tpl->setNotification($this->language->__('notification.idea_edited'), 'success');

                        $subject = $this->language->__('email_notifications.idea_edited_subject');
                        $actual_link = BASE_URL . "/ideas/ideaDialog/" . (int)$params['itemId'];
                        $message = sprintf($this->language->__('notification.idea_edited'),
                            $_SESSION["userdata"]["name"], $params['description']);
                        $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'],
                            array(
                                "link" => $actual_link,
                                "text" => $this->language->__('email_notifications.idea_edited_cta')
                            ));

                        $this->tpl->redirect(BASE_URL . "/ideas/ideaDialog/" . (int)$params['itemId']);

                    } else {

                        $this->tpl->setNotification($this->language->__("notification.please_enter_title"), 'error');

                    }

                }else{

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

                        $subject = $this->language->__('email_notifications.idea_created_subject');
                        $actual_link = BASE_URL."/ideas/ideaDialog/".$id;
                        $message = sprintf($this->language->__('email_notifications.idea_created_message'), $_SESSION["userdata"]["name"], $params['description']);
                        $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> $this->language->__('email_notifications.idea_edited_cta')));

                        $this->tpl->setNotification($this->language->__('notification.idea_created'), 'success');

                        $this->tpl->redirect(BASE_URL."/ideas/ideaDialog/".(int)$id);

                    } else {

                        $this->tpl->setNotification($this->language->__("notification.please_enter_title"), 'error');

                    }
                }
            }

            if (isset($params['comment']) === true) {

                $values = array(
                    'text' => $params['text'],
                    'date' => date("Y-m-d H:i:s"),
                    'userId' => ($_SESSION['userdata']['id']),
                    'moduleId' => (int)$_GET['id'],
                    'commentParent' => ($params['father'])
                );

                $message = $this->commentsRepo->addComment($values, 'idea');
                $this->tpl->setNotification($this->language->__('notifications.comment_create_success'), "success");

                $subject = $this->language->__('email_notifications.new_comment_idea_subject');
                $actual_link = BASE_URL."/ideas/ideaDialog/".(int)$_GET['id'];
                $message = sprintf($this->language->__('email_notifications.new_comment_idea_message'), $_SESSION["userdata"]["name"]);
                $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> $this->language->__('email_notifications.new_comment_idea_cta')));

                $this->tpl->redirect(BASE_URL."/ideas/ideaDialog/".(int)$_GET['id']);

            }

            $this->tpl->assign('canvasTypes',  $this->ideaRepo->canvasTypes);
            $this->tpl->assign('canvasItem',  $this->ideaRepo->getSingleCanvasItem($_GET['id']));
            $this->tpl->displayPartial('ideas.ideaDialog');
        }

        /**
         * put - handle put requests
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function put($params)
        {

        }

        /**
         * delete - handle delete requests
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function delete($params)
        {

        }

    }

}

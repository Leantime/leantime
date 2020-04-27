<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    use \DateTime;
    use \DateInterval;


    class editCanvasItem
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
            $this->leanCanvasRepo = new repositories\leancanvas();
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
                    $this->tpl->setNotification($this->language->__("notifications.comment_deleted"), "success");
                }

                //Delete milestone relationship
                if (isset($params['removeMilestone']) === true) {
                    $milestoneId = (int)($params['removeMilestone']);
                    $this->leanCanvasRepo->patchCanvasItem($params['id'], array("milestoneId" => ''));
                    $this->tpl->setNotification($this->language->__("notifications.milestone_detached"), "success");
                }

                $canvasItem = $this->leanCanvasRepo->getSingleCanvasItem($params['id']);

                $comments = $this->commentsRepo->getComments('leancanvasitem', $canvasItem['id']);
                $this->tpl->assign('numComments', $this->commentsRepo->countComments('leancanvasitem', $canvasItem['id']));

            }else{
                if(isset($params['type'])) {
                    $type=$params['type'];
                } else {
                    $type = "problem";
                }

                $canvasItem = array(
                    "id"=>"",
                    "box" => $params['type'],
                    "description" => "",
                    "status" => "danger",
                    "assumptions" => "",
                    "data" => "",
                    "conclusion" => "",
                    "milestoneHeadline" => "",
                    "milestoneId" => ""
                );

                $comments = [];

            }

            $this->tpl->assign('comments', $comments);
            $this->tpl->assign('helper', new core\helper());

            $this->tpl->assign("milestones",  $this->ticketService->getAllMilestones($_SESSION["currentProject"]));
            $this->tpl->assign('canvasTypes',  $this->leanCanvasRepo->canvasTypes);
            $this->tpl->assign('canvasItem', $canvasItem);
            $this->tpl->displayPartial('leancanvas.canvasDialog');
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

                        $currentCanvasId = (int)$_SESSION['currentLeanCanvas'];

                        $canvasItem = array(
                            "box" => $params['box'],
                            "author" => $_SESSION['userdata']["id"],
                            "description" => $params['description'],
                            "status" => $params['status'],
                            "assumptions" => $params['assumptions'],
                            "data" => $params['data'],
                            "conclusion" => $params['conclusion'],
                            "itemId" => $params['itemId'],
                            "canvasId" => $currentCanvasId,
                            "milestoneId" => $params['milestoneId']
                        );



                        if(isset($params['newMilestone']) && $params['newMilestone'] != '' ) {
                            $params['headline'] = $params['newMilestone'];
                            $params['tags'] = "#ccc";
                            $params['editFrom'] = date("Y-m-d");
                            $params['editTo'] = date("Y-m-d", strtotime("+1 week"));
                            $id = $this->ticketService->quickAddMilestone($params);
                            if($id !== false) {
                                $canvasItem['milestoneId'] = $id;
                            }
                        }

                        if(isset($params['existingMilestone']) && $params['existingMilestone'] != '' ) {
                            $canvasItem['milestoneId'] = $params['existingMilestone'];
                        }

                        $this->leanCanvasRepo->editCanvasItem($canvasItem);

                        $comments = $this->commentsRepo->getComments('leancanvasitem', $params['itemId']);
                        $this->tpl->assign('numComments', $this->commentsRepo->countComments('leancanvasitem', $params['itemId']));
                        $this->tpl->assign('comments', $comments);

                        $this->tpl->setNotification($this->language->__("notifications.canvas_item_updates"), 'success');

                        $subject = $this->language->__("email_notifications.canvas_board_edited");
                        $actual_link = BASE_URL."/leancanvas/editCanvasItem/".(int)$params['itemId'];
                        $message = sprintf($this->language->__("email_notifications.canvas_item_update_message"),$_SESSION["userdata"]["name"],  $canvasItem['description']);
                        $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> $this->language->__("email_notifications.canvas_item_update_cta")));

                        $this->tpl->redirect(BASE_URL."/leancanvas/editCanvasItem/".$params['itemId']);

                    } else {
                        $this->tpl->setNotification($this->language->__("notification.please_enter_hypothesis"), 'error');

                    }

                }else{

                    if (isset($_POST['description']) === true) {

                        $currentCanvasId = (int)$_SESSION['currentLeanCanvas'];

                        $canvasItem = array(
                            "box" => $params['box'],
                            "author" => $_SESSION['userdata']["id"],
                            "description" => $params['description'],
                            "status" => $params['status'],
                            "assumptions" => $params['assumptions'],
                            "data" => $params['data'],
                            "conclusion" => $params['conclusion'],
                            "canvasId" => $currentCanvasId
                        );

                        $id = $this->leanCanvasRepo->addCanvasItem($canvasItem);

                        $this->tpl->setNotification($this->leanCanvasRepo->canvasTypes[$params['box']].' successfully created', 'success');

                        $subject = $this->language->__("email_notifications.canvas_board_item_created");
                        $actual_link = BASE_URL."/leancanvas/editCanvasItem/".(int)$params['itemId'];
                        $message = sprintf($this->language->__("email_notifications.canvas_item_created_message"),$_SESSION["userdata"]["name"],  $canvasItem['description']);
                        $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> $this->language->__("email_notifications.canvas_item_update_cta")));


                        $this->tpl->setNotification($this->language->__("notification.hypothesis_created"), 'success');

                        $this->tpl->redirect(BASE_URL."/leancanvas/editCanvasItem/".$id);

                    } else {

                        $this->tpl->setNotification($this->language->__("notification.please_enter_hypothesis"), 'error');

                    }
                }

            }


            if (isset($params['comment']) === true) {

                $values = array(
                    'text' => $params['text'],
                    'date' => date("Y-m-d H:i:s"),
                    'userId' => ($_SESSION['userdata']['id']),
                    'moduleId' => $_GET['id'],
                    'commentParent' => ($params['father'])
                );

                $message = $this->commentsRepo->addComment($values, 'leancanvasitem');
                $this->tpl->setNotification($this->language->__("notifications.comment_create_success"), "success");
                $this->tpl->assign('helper', new core\helper());

                $subject = $this->language->__("email_notifications.canvas_board_comment_created");
                $actual_link = BASE_URL."/leancanvas/editCanvasItem/".(int)$_GET['id'];
                $message = sprintf($this->language->__("email_notifications.canvas_item__comment_created_message"),$_SESSION["userdata"]["name"]);
                $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> $this->language->__("email_notifications.canvas_item_update_cta")));


                $this->tpl->redirect(BASE_URL."/leancanvas/editCanvasItem/".$_GET['id']);

            }

            $this->tpl->assign('helper', new core\helper());
            $this->tpl->assign('canvasTypes',  $this->leanCanvasRepo->canvasTypes);
            $this->tpl->assign('canvasItem',  $this->leanCanvasRepo->getSingleCanvasItem($_GET['id']));
            $this->tpl->displayPartial('leancanvas.canvasDialog');
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

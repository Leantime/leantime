<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    use \DateTime;
    use \DateInterval;


    class retroDialog
    {

        private $tpl;
        private $projects;
        private $sprintService;

        /**
         * constructor - initialize private variables
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function __construct()
        {

            $this->tpl = new core\template();
            $this->retroRepo = new repositories\retrospectives();
            $this->sprintService = new services\sprints();
            $this->ticketRepo = new repositories\tickets();
            $this->ticketService = new services\tickets();
            $this->commentsRepo = new repositories\comments();
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
                    $this->retroRepo->patchCanvasItem($params['id'], array("milestoneId" => ''));
                    $this->tpl->setNotification($this->language->__("notifications.milestone_detached"), "success");
                }

                $canvasItem = $this->retroRepo->getSingleCanvasItem($params['id']);

                $comments = $this->commentsRepo->getComments('retrospective', $canvasItem['id']);
                $this->tpl->assign('numComments', $this->commentsRepo->countComments('retrospective', $canvasItem['id']));

            }else{

                if(isset($params['type'])) {
                    $type=$params['type'];
                } else {
                    $type = "well";
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
            $this->tpl->assign('canvasTypes',  $this->retroRepo->canvasTypes);
            $this->tpl->assign('canvasItem', $canvasItem);
            $this->tpl->displayPartial('retrospectives.retroDialog');
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

                        $currentCanvasId = (int)$_SESSION['currentRetroCanvas'];

                        $canvasItem = array(
                            "box" => $params['box'],
                            "author" => $_SESSION['userdata']["id"],
                            "description" => $params['description'],
                            "status" => "",
                            "assumptions" => "",
                            "data" => $params['data'],
                            "conclusion" => "",
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

                        $this->retroRepo->editCanvasItem($canvasItem);

                        $comments = $this->commentsRepo->getComments('leancanvasitem', $params['itemId']);
                        $this->tpl->assign('numComments', $this->commentsRepo->countComments('leancanvasitem', $params['itemId']));
                        $this->tpl->assign('comments', $comments);

                        $this->tpl->setNotification($this->language->__("notifications.canvas_item_updates"), 'success');

                        $this->tpl->redirect(BASE_URL."/retrospectives/retroDialog/".$params['itemId']);

                    } else {
                        $this->tpl->setNotification($this->language->__("notification.please_enter_title"), 'error');

                    }

                }else{

                    if (isset($_POST['description']) === true) {

                        $currentCanvasId = (int)$_SESSION['currentRetroCanvas'];

                        $canvasItem = array(
                            "box" => $params['box'],
                            "author" => $_SESSION['userdata']["id"],
                            "description" => $params['description'],
                            "status" => "",
                            "assumptions" => "",
                            "data" => $params['data'],
                            "conclusion" => "",
                            "canvasId" => $currentCanvasId
                        );

                        $id = $this->retroRepo->addCanvasItem($canvasItem);

                        $this->tpl->setNotification($this->language->__("notification.feedback_successfully_created"), 'success');

                        $this->tpl->redirect(BASE_URL."/retrospectives/retroDialog/".$id);


                    } else {
                        $this->tpl->setNotification($this->language->__("notification.please_enter_title"), 'error');

                    }
                }

            }


            if (isset($params['comment']) === true) {

                $id = (int) $_GET['id'];
                $values = array(
                    'text' => $params['text'],
                    'date' => date("Y-m-d H:i:s"),
                    'userId' => ($_SESSION['userdata']['id']),
                    'moduleId' => $id,
                    'commentParent' => ($params['father'])
                );

                $message = $this->commentsRepo->addComment($values, 'retrospective');
                $this->tpl->setNotification($this->language->__('notifications.comment_create_success'), "success");
                $this->tpl->assign('helper', new core\helper());

                $this->tpl->redirect(BASE_URL."/retrospectives/retroDialog/".$id);

            }

            $this->tpl->assign('helper', new core\helper());
            $this->tpl->assign('canvasTypes',  $this->retroRepo->canvasTypes);
            $this->tpl->assign('canvasItem',  $this->retroRepo->getSingleCanvasItem($_GET['id']));
            $this->tpl->displayPartial('retrospectives.retroDialog');
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

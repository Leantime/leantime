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
                    $this->tpl->setNotification("Comment successfully deleted", "success");
                }

                //Delete milestone relationship
                if (isset($params['removeMilestone']) === true) {
                    $milestoneId = (int)($params['removeMilestone']);
                    $this->ideaRepo->patchCanvasItem($params['id'], array("milestoneId" => ''));
                    $this->tpl->setNotification("Milestone successfully detached", "success");
                }

                $canvasItem = $this->ideaRepo->getSingleCanvasItem($params['id']);
                if($canvasItem['box'] == "0"){$canvasItem['box'] = "idea";}
                $comments = $this->commentsRepo->getComments('idea', $canvasItem['id']);
                $this->tpl->assign('numComments', $this->commentsRepo->countComments('retrospective', $canvasItem['id']));

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

                        $this->ideaRepo->editCanvasItem($canvasItem);

                        $comments = $this->commentsRepo->getComments('leancanvasitem', $params['itemId']);
                        $this->tpl->assign('numComments', $this->commentsRepo->countComments('leancanvasitem', $params['itemId']));
                        $this->tpl->assign('comments', $comments);

                        $this->tpl->setNotification('Idea successfully updated', 'success');

                        $subject = "An idea was edited on one of your idea boards: ". $params['headline'];
                        $actual_link = BASE_URL."/ideas/ideaDialog/".$params['itemId'];
                        $message = "An idea was updated by " . $_SESSION["userdata"]["name"] . "";
                        $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> "Click here to see it."));


                        header("Location:".BASE_URL."/ideas/ideaDialog/".$params['itemId']);

                    } else {
                        $this->tpl->setNotification('ENTER_TITLE', 'error');

                    }

                }else{

                    if (isset($_POST['description']) === true) {

                        $currentCanvasId = (int)$_SESSION['currentIdeaCanvas'];

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

                        $id = $this->ideaRepo->addCanvasItem($canvasItem);

                        $subject = "A new idea was added to one of your idea boards";
                        $actual_link = BASE_URL."/ideas/ideaDialog/".$id;
                        $message = "A new idea was added by " . $_SESSION["userdata"]["name"] . ": ".$params['description'];
                        $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> "Click here to see it."));

                        $this->tpl->setNotification('Idea successfully created', 'success');

                        header("Location:".BASE_URL."/ideas/ideaDialog/".$id);

                    } else {
                        $this->tpl->setNotification('ENTER_TITLE', 'error');

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
                $this->tpl->setNotification($message["msg"], $message["type"]);
                $this->tpl->assign('helper', new core\helper());

                $subject = "A new comment was added to an idea";
                $actual_link = BASE_URL."/ideas/ideaDialog/".(int)$_GET['id'];
                $message = "" . $_SESSION["userdata"]["name"] . " added a comment to an idea:
                ".$params['text'];
                $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> "Click here to see it."));

                header("Location:".BASE_URL."/ideas/ideaDialog/".$_GET['id']);

            }

            $this->tpl->assign('helper', new core\helper());
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

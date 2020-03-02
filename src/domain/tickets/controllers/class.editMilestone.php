<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;
    use \DateTime;
    use \DateInterval;

    class editMilestone
    {

        private $tpl;
        private $projects;
        private $ticketService;

        /**
         * constructor - initialize private variables
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function __construct()
        {

            $this->tpl = new core\template();
            $this->ticketService = new services\tickets();
            $this->ticketRepo = new repositories\tickets();
            $this->projectRepo = new repositories\projects();
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

                $milestone = $this->ticketRepo->getTicket($params['id']);
                $milestone = (object) $milestone;

                if(!isset($milestone->id)) {
                    $this->tpl->setNotification("There was an issue retrieving this milestone", "error");
                    $this->tpl->redirect(BASE_URL."/tickets/roadmap/");
                }

                $milestone->editFrom =  date('m/d/Y', strtotime($milestone->editFrom));
                $milestone->editTo = date('m/d/Y', strtotime($milestone->editTo));

                $comments = $this->commentsRepo->getComments('ticket', $params['id']);

            }else{

                $milestone = new models\tickets();
                $today = new DateTime();
                $milestone->editFrom = $today->format("m/d/Y");

                //Add 1 week
                $interval = new DateInterval('P1W');
                $next_week = $today->add($interval);

                $milestone->editTo = $next_week->format("m/d/Y");

                $comments = [];
            }

            $this->tpl->assign('comments', $comments);
            $allProjectMilestones = $this->ticketService->getAllMilestones($_SESSION['currentProject']);
            $this->tpl->assign('milestones', $allProjectMilestones);
            $this->tpl->assign('helper', new core\helper());
            $this->tpl->assign('users', $this->projectRepo->getUsersAssignedToProject($_SESSION['currentProject']));
            $this->tpl->assign('milestone', $milestone);
            $this->tpl->displayPartial('tickets.milestoneDialog');


        }

        /**
         * post - handle post requests
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function post($params)
        {
            //If ID is set its an update
            if(isset($_GET['id']) && $_GET['id'] > 0) {

                $params['id'] = (int)$_GET['id'];

                if (isset($params['comment']) === true) {

                    $values = array(
                        'text' => $params['text'],
                        'date' => date("Y-m-d H:i:s"),
                        'userId' => ($_SESSION['userdata']['id']),
                        'moduleId' => $params['id'],
                        'commentParent' => ($params['father'])
                    );

                    $message = $this->commentsRepo->addComment($values, 'ticket');
                    $this->tpl->setNotification($message["msg"], $message["type"]);
                    $this->tpl->assign('helper', new core\helper());

                    $subject = "A new comment was added to a milestone";
                    $actual_link = BASE_URL."/tickets/editMilestone/".(int)$_GET['id'];
                    $message = "" . $_SESSION["userdata"]["name"] . " added a comment to a milestone. ";
                    $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> "Click here to see it."));

                    $this->tpl->redirect(BASE_URL."/tickets/editMilestone/".$params['id']);

                }

                if($this->ticketService->quickUpdateMilestone($params) == true) {
                    $this->tpl->setNotification("Milestone Edited Successfully", "success");

                    $subject = "A milestone was updated in one of your projects";
                    $actual_link = BASE_URL."/tickets/editMilestone/".(int)$params['id'];
                    $message = "" . $_SESSION["userdata"]["name"] . " edited a new milestone. ";
                    $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> "Click here to see it."));

                }else{
                    $this->tpl->setNotification("There was a problem saving the milestone", "error");
                }

                //$this->tpl->redirect(BASE_URL."/tickets/editMilestone/".$params['id']);

            }else{


                $result = $this->ticketService->quickAddMilestone($params);

                if(is_numeric($result)) {

                    $this->tpl->setNotification("Milestone Created Successfully", "success");

                    $subject = "A new milestone was created in one of your projects";
                    $actual_link = BASE_URL."/tickets/editMilestone/".(int)$result;
                    $message = "" . $_SESSION["userdata"]["name"] . " created a new milestone ";
                    $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> "Click here to see it."));


                    $this->tpl->redirect(BASE_URL."/tickets/editMilestone/".$result);

                }else{

                    $this->tpl->setNotification("There was a problem saving the milestone: ".$result['message'], "error");
                    $this->tpl->redirect(BASE_URL."/tickets/editMilestone/");

                }

            }

            $this->tpl->assign('milestone', (object) $params);
            $this->tpl->displayPartial('tickets.milestoneDialog');
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
            //ToDO
        }

    }

}

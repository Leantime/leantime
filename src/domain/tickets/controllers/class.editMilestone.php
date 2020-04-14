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
            $this->commentsService = new services\comments();
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
                    $this->commentsService->deleteComment($commentId);

                    $this->tpl->setNotification($this->language->__("notifications.comment_deleted"), "success");
                }

                $milestone = $this->ticketRepo->getTicket($params['id']);
                $milestone = (object) $milestone;

                if(!isset($milestone->id)) {
                    $this->tpl->setNotification($this->language->__("notifications.could_not_find_milestone"), "error");
                    $this->tpl->redirect(BASE_URL."/tickets/roadmap/");
                }

                $comments = $this->commentsService->getComments('ticket', $params['id']);

            }else{

                $milestone = new models\tickets();
                $milestone->status = 3;

                $today = new DateTime();
                $milestone->editFrom = $today->format("Y-m-d");

                //Add 1 week
                $interval = new DateInterval('P1W');
                $next_week = $today->add($interval);

                $milestone->editTo = $next_week->format("Y-m-d");

                $comments = [];
            }

            $this->tpl->assign('statusLabels', $this->ticketService->getStatusLabels());
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
            if(isset($_GET['id']) && (int) $_GET['id'] > 0) {

                $params['id'] = (int)$_GET['id'];
                $milestone = $this->ticketRepo->getTicket($params['id']);

                if (isset($params['comment']) === true) {

                    $values = array(
                        'text' => $params['text'],
                        'date' => date("Y-m-d H:i:s"),
                        'userId' => ($_SESSION['userdata']['id']),
                        'moduleId' => $params['id'],
                        'father' => ($params['father'])
                    );


                    $message = $this->commentsService->addComment($values, 'ticket',  $params['id'], $milestone);

                    if($message === true) {
                        $this->tpl->setNotification($this->language->__("notifications.comment_added_successfully"), "success");

                        $this->tpl->assign('helper', new core\helper());

                        $subject = $this->language->__("email_notifications.new_comment_milestone_subject");
                        $actual_link = BASE_URL."/tickets/editMilestone/".(int)$_GET['id'];
                        $message = sprintf($this->language->__("email_notifications.new_comment_milestone_message"), $_SESSION["userdata"]["name"]);
                        $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> $this->language->__("email_notifications.new_comment_milestone_cta")));


                    }else{
                        $this->tpl->setNotification($this->language->__("notifications.problem_saving_your_comment"), "error");
                    }

                    $this->tpl->redirect(BASE_URL."/tickets/editMilestone/".$params['id']);

                }

                if (isset($params['headline']) === true) {

                    if($this->ticketService->quickUpdateMilestone($params) == true) {

                        $this->tpl->setNotification($this->language->__("notification.milestone_edited_successfully"), "success");

                        $subject = $this->language->__("email_notifications.milestone_update_subject");
                        $actual_link = BASE_URL."/tickets/editMilestone/".(int)$_GET['id'];
                        $message = sprintf($this->language->__("email_notifications.milestone_update_message"), $_SESSION["userdata"]["name"]);
                        $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> $this->language->__("email_notifications.milestone_update_cta")));
                        $this->tpl->redirect(BASE_URL."/tickets/editMilestone/".$params['id']);

                    }else{
                        $this->tpl->setNotification($this->language->__("notification.saving_milestone_error"), "error");
                        $this->tpl->redirect(BASE_URL."/tickets/editMilestone/".$params['id']);
                    }
                }

                $this->tpl->redirect(BASE_URL."/tickets/editMilestone/".$params['id']);

            }else{


                $result = $this->ticketService->quickAddMilestone($params);

                if(is_numeric($result)) {

                    $this->tpl->setNotification($this->language->__("notification.milestone_created_successfully"), "success");

                    $subject = $this->language->__("email_notifications.milestone_created_subject");
                    $actual_link = BASE_URL."/tickets/editMilestone/".$result;
                    $message = sprintf($this->language->__("email_notifications.milestone_created_message"), $_SESSION["userdata"]["name"]);
                    $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> $this->language->__("email_notifications.milestone_created_cta")));

                    $this->tpl->redirect(BASE_URL."/tickets/editMilestone/".$result);

                }else{

                    $this->tpl->setNotification($this->language->__("notification.saving_milestone_error"), "error");
                    $this->tpl->redirect(BASE_URL."/tickets/editMilestone/");

                }

            }

            $this->tpl->assign('statusLabels', $this->ticketService->getStatusLabels());
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
        }

    }

}

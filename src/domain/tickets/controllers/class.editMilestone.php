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

                $milestone = $this->ticketRepo->getTicket($params['id']);
                $milestone = (object) $milestone;
                $milestone->editFrom =  date($this->language->__("language.dateformat"), strtotime($milestone->editFrom));
                $milestone->editTo = date($this->language->__("language.dateformat"), strtotime($milestone->editTo));

                $comments = $this->commentsRepo->getComments('ticket', $params['id']);

            }else{
                $milestone = new models\tickets();
                $today = new DateTime();
                $milestone->editFrom = $today->format($this->language->__("language.dateformat"));

                //Add 1 week
                $interval = new DateInterval('P1W');
                $next_week = $today->add($interval);

                $milestone->editTo = $next_week->format($this->language->__("language.dateformat"));

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

                    $subject = $this->language->__("email_notifications.new_comment_milestone_subject");
                    $actual_link = BASE_URL."/tickets/editMilestone/".(int)$_GET['id'];
                    $message = sprintf($this->language->__("email_notifications.new_comment_milestone_message"), $_SESSION["userdata"]["name"]);
                    $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> $this->language->__("email_notifications.new_comment_milestone_cta")));

                    $this->tpl->redirect("/tickets/editMilestone/".$params['id']);

                }

                if($this->ticketService->quickUpdateMilestone($params) == true) {

                    $this->tpl->setNotification($this->language->__("notification.milestone_edited_successfully"), "success");

                    $subject = $this->language->__("email_notifications.milestone_update_subject");
                    $actual_link = BASE_URL."/tickets/editMilestone/".(int)$_GET['id'];
                    $message = sprintf($this->language->__("email_notifications.milestone_update_message"), $_SESSION["userdata"]["name"]);
                    $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> $this->language->__("email_notifications.milestone_update_cta")));


                }else{
                    $this->tpl->setNotification($this->language->__("notification.saving_milestone_error"), "error");
                }

                $this->tpl->redirect("/tickets/editMilestone/".$params['id']);

            }else{

                $result = $this->ticketService->quickAddMilestone($params);

                if($result == true) {

                    $this->tpl->setNotification($this->language->__("notification.milestone_created_successfully"), "success");

                    $subject = $this->language->__("email_notifications.milestone_created_subject");
                    $actual_link = BASE_URL."/tickets/editMilestone/".(int)$_GET['id'];
                    $message = sprintf($this->language->__("email_notifications.milestone_created_message"), $_SESSION["userdata"]["name"]);
                    $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> $this->language->__("email_notifications.milestone_created_cta")));

                    $this->tpl->redirect("/tickets/editMilestone/".$result);

                }else{

                    $this->tpl->setNotification($this->language->__("notification.saving_milestone_error"), "error");

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
        }

    }

}

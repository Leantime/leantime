<?php

namespace leantime\domain\controllers {

    use leantime\domain\models\auth\roles;
    use leantime\domain\services;
    use leantime\domain\repositories;
    use leantime\core;
    use leantime\core\controller;

    class show extends controller
    {

        private services\projects $projectService;
        private services\tickets $ticketService;
        private services\users $userService;
        private services\timesheets $timesheetService;
        private services\comments $commentService;
        private services\reactions $reactionsService;

        public function init()
        {


            $this->projectService = new services\projects();
            $this->ticketService = new services\tickets();
            $this->userService = new services\users();
            $this->timesheetService = new services\timesheets();
            $this->commentService = new services\comments();
            $this->reactionsService = new services\reactions();

            $_SESSION['lastPage'] = BASE_URL . "/dashboard/show";

            (new services\reports())->dailyIngestion();
        }

        /**
         * @return void
         */
        public function get()
        {

            if (!isset($_SESSION['currentProject']) || $_SESSION['currentProject'] == '') {
                core\frontcontroller::redirect(BASE_URL . "/dashboard/home");
            }

            $project = $this->projectService->getProject($_SESSION['currentProject']);

            if (isset($project['id']) === false) {
                core\frontcontroller::redirect(BASE_URL . "/dashboard/home");
            }

            $projectRedirectFilter = static::dispatch_filter("dashboardRedirect", "/dashboard/show", array("type" => $project["type"]));
            if($projectRedirectFilter != "/dashboard/show") {
                core\frontcontroller::redirect(BASE_URL . $projectRedirectFilter);
            }

            $progressSteps = $this->projectService->getProjectSetupChecklist($_SESSION['currentProject']);
            $this->tpl->assign("progressSteps", $progressSteps);

            $project['assignedUsers'] = $this->projectService->getProjectUserRelation($_SESSION['currentProject']);
            $this->tpl->assign('project', $project);

            $userReaction = $this->reactionsService->getUserReactions($_SESSION['userdata']['id'], 'project', $_SESSION['currentProject'], \leantime\domain\models\reactions::$favorite);
            if($userReaction && is_array($userReaction) && count($userReaction) >0) {
                $this->tpl->assign("isFavorite", true);
            }else{
                $this->tpl->assign("isFavorite", false);
            }

            $this->tpl->assign('allUsers', $this->userService->getAll());

            //Project Progress
            $progress = $this->projectService->getProjectProgress($_SESSION['currentProject']);
            $this->tpl->assign('projectProgress', $progress);
            $this->tpl->assign("currentProjectName", $this->projectService->getProjectName($_SESSION['currentProject']));

            //Milestones
            $milestones = $this->ticketService->getAllMilestones($_SESSION['currentProject'], false, "date");
            $this->tpl->assign('milestones', $milestones);

            $comments = new repositories\comments();

            //Delete comment
            if (isset($_GET['delComment']) === true) {
                $commentId = (int)($_GET['delComment']);

                $comments->deleteComment($commentId);

                $this->tpl->setNotification($this->language->__("notifications.comment_deleted"), "success");
            }

            $comment = $comments->getComments('project', $_SESSION['currentProject'], "");
            $this->tpl->assign('comments', $comment);
            $this->tpl->assign('numComments', $comments->countComments('project', $_SESSION['currentProject']));

            // TICKETS
            $this->tpl->assign('tickets', $this->ticketService->getLastTickets($_SESSION['currentProject']));
            $this->tpl->assign("onTheClock", $this->timesheetService->isClocked($_SESSION["userdata"]["id"]));
            $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
            $this->tpl->assign('priorities', $this->ticketService->getPriorityLabels());
            $this->tpl->assign("types", $this->ticketService->getTicketTypes());
            $this->tpl->assign("statusLabels", $this->ticketService->getStatusLabels());

            $this->tpl->display('dashboard.show');
        }

        public function post($params)
        {

            if (services\auth::userHasRole([roles::$owner, roles::$manager, roles::$editor, roles::$commenter])) {
                if (isset($params['quickadd']) == true) {
                    $result = $this->ticketService->quickAddTicket($params);

                    if (isset($result["status"])) {
                        $this->tpl->setNotification($result["message"], $result["status"]);
                    } else {
                        $this->tpl->setNotification($this->language->__("notifications.ticket_saved"), "success");
                    }

                    $this->tpl->redirect(BASE_URL . "/dashboard/show");
                }
            }

            // Manage Post comment
            $comments = new repositories\comments();
            if (isset($_POST['comment']) === true) {
                $project = $this->projectService->getProject($_SESSION['currentProject']);

                if ($this->commentService->addComment($_POST, "project", $_SESSION['currentProject'], $project)) {
                    $this->tpl->setNotification($this->language->__("notifications.comment_create_success"), "success");
                } else {
                    $this->tpl->setNotification($this->language->__("notifications.comment_create_error"), "error");
                }
            }

            $this->tpl->redirect(BASE_URL . "/dashboard/show");
        }
    }
}

<?php

namespace leantime\domain\controllers {

    use leantime\domain\models\auth\roles;
    use leantime\domain\services;
    use leantime\domain\repositories;
    use leantime\core;

    class show
    {

        private $tpl;
        private $dashboardRepo;
        private $projectService;
        private $sprintService;
        private $ticketService;
        private $userService;
        private $timesheetService;


        public function __construct()
        {
            $this->tpl = new core\template();
            $this->dashboardRepo = new repositories\dashboard();
            $this->projectService = new services\projects();
            $this->sprintService = new services\sprints();
            $this->ticketService = new services\tickets();
            $this->userService = new services\users();
            $this->timesheetService = new services\timesheets();
            $this->language = new core\language();
            $this->commentService = new services\comments();

            $_SESSION['lastPage'] = BASE_URL."/dashboard/show";

            $reportService = new services\reports();
            $reportService->dailyIngestion();

        }

        /**
         * @return void
         */
        public function get()
        {

            if(!isset($_SESSION['currentProject']) || $_SESSION['currentProject'] == '') {
                core\frontcontroller::redirect(BASE_URL."/dashboard/home");
            }

            $this->tpl->assign('allUsers', $this->userService->getAll());

            //Project Progress
            $progress = $this->projectService->getProjectProgress($_SESSION['currentProject']);

            $this->tpl->assign('projectProgress', $progress);
            $this->tpl->assign("currentProjectName", $this->projectService->getProjectName($_SESSION['currentProject']));


            $project = $this->projectService->getProject($_SESSION['currentProject']);
            $project['assignedUsers'] = $this->projectService->getProjectUserRelation($_SESSION['currentProject']);
            $this->tpl->assign('project', $project);

            //Milestones
            $milestones = $this->ticketService->getAllMilestones($_SESSION['currentProject'], false, "date");
            $this->tpl->assign('milestones', $milestones);

            //Delete comment
            if (isset($_GET['delComment']) === true) {

                $commentId = (int)($_GET['delComment']);

                $comments->deleteComment($commentId);

                $tpl->setNotification($this->language->__("notifications.comment_deleted"), "success");

            }

            $comments = new repositories\comments();
            $comment = $comments->getComments('project', $_SESSION['currentProject'],"", $_SESSION["projectsettings"]['commentOrder']);
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

            if(services\auth::userHasRole([roles::$owner, roles::$manager, roles::$editor, roles::$commenter])) {

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

                if($this->commentService->addComment($_POST, "project", $_SESSION['currentProject'], $project)) {

                    $this->tpl->setNotification($this->language->__("notifications.comment_create_success"), "success");
                }else {
                    $this->tpl->setNotification($this->language->__("notifications.comment_create_error"), "error");
                }

            }

            $this->tpl->redirect(BASE_URL . "/dashboard/show");


        }
    }
}

<?php

namespace leantime\domain\controllers {

    use leantime\domain\services;
    use leantime\domain\repositories;
    use leantime\core;

    class show
    {

        /**
         * @return void
         */
        public function run()
        {

            $_SESSION['lastPage'] = "/dashboard/show";

            $tpl = new core\template();
            $helper = new core\helper();
            $dashboardRepo = new repositories\dashboard();
            $projectService = new services\projects();
            $sprintService = new services\sprints();
            $ticketService= new services\tickets();
            $userRepo = new repositories\users();
            $ticketRepo = new repositories\tickets();

            $tpl->assign('allUsers', $userRepo->getAll());

            //QuickAdd
            if (isset($_POST['quickadd']) == true) {
                $result = $ticketService->quickAddTicket($_POST);

                if (isset($result["status"])) {
                    $tpl->setNotification($result["message"], $result["status"]);
                } else {
                    $tpl->setNotification("To-Do successfully added", "success");

                    $subject = "A new To-Do was added";
                    $actual_link = BASE_URL."/tickets/showTicket/". $result;
                    $message = "" . $_SESSION["userdata"]["name"] . " added a new To-Do ";
                    $projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> "Click here to see it."));

                }
            }


            //Run reports

            $reportService = new services\reports();
            $reportService->dailyIngestion();

            //Project Progress

            $progress = $projectService->getProjectProgress($_SESSION['currentProject']);
            $tpl->assign('projectProgress', $progress);
            $tpl->assign("currentProjectName", $projectService->getProjectName($_SESSION['currentProject']));

            //Sprint Burndown
            $currentSprint = $sprintService->getCurrentSprint($_SESSION['currentProject']);
            $sprintChart = $sprintService->getSprintBurndown($currentSprint);

            if($sprintChart !== false) {
                $tpl->assign('sprintBurndown', $sprintChart);
                $tpl->assign('currentSprint', $currentSprint);
                $tpl->assign('upcomingSprint', false);
            }else{
                $tpl->assign('backlogBurndown', $sprintService->getBacklogBurndown($_SESSION['currentProject']));
                $tpl->assign('currentSprint', false);
                $tpl->assign('upcomingSprint',  $sprintService->getUpcomingSprint($_SESSION['currentProject']));
            }


            //Milestones
            $milestones = $ticketService->getAllMilestones($_SESSION['currentProject']);
            $tpl->assign('milestones', $milestones);


            // TICKETS
            $tickets = new repositories\tickets();

            $tpl->assign('tickets', $ticketService->getOpenUserTicketsThisWeekAndLater($_SESSION["userdata"]["id"], $_SESSION['currentProject']));

            $tpl->assign('states', $tickets->statePlain);
            $tpl->assign("onTheClock", $tickets->isClocked($_SESSION["userdata"]["id"]));



            // HOURS
            $ts = new repositories\timesheets();
            $myHours = $ts->getUsersHours($_SESSION['userdata']['id']);

            $tpl->assign('myHours', $myHours);

            // NOTES
            if (isset($_POST['save'])) {
                if (isset($_POST['title']) && isset($_POST['description'])) {
                    $values = array(
                        'title' => $_POST['title'],
                        'description' => $_POST['description']
                    );

                    $dashboardRepo->addNote($_SESSION['userdata']['id'], $values);
                    $tpl->setNotification('SAVE_SUCCESS', 'success');
                } else {
                    $tpl->setNotification('MISSING_FIELDS', 'error');
                }
            }

            // Statistics
            //Todo: Remove in 2.1
            //$tpl->assign('closedTicketsPerWeek', $dashboardRepo->getClosedTicketsPerWeek());
            //$tpl->assign('hoursPerTicket', round($dashboardRepo->getHoursPerTicket()));
            //$tpl->assign('hoursBugFixing', round($dashboardRepo->getHoursBugFixing(), 1));

            $tpl->assign('ticketsRepo', $ticketRepo);
            $tpl->assign('efforts', $ticketRepo->efforts);
            $tpl->assign("types", $ticketRepo->type);
            $tpl->assign('allTicketStates', $ticketRepo->statePlain);


            $tpl->assign('notes', $dashboardRepo->getNotes($_SESSION['userdata']['id']));

            $tpl->assign('helper', $helper);

            $tpl->display('dashboard.show');

        }

    }

}

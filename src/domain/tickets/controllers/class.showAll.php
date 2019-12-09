<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class showAll
    {

        private $projectService;
        private $tpl;
        private $ticketService;
        private $sprintService;
        private $timesheetService;

        public function __construct()
        {
            $this->tpl = new core\template();
            $this->projectService = new services\projects();
            $this->ticketService = new services\tickets();
            $this->sprintService = new services\sprints();
            $this->timesheetService = new services\timesheets();

            $_SESSION['lastPage'] = "/tickets/showAll";


        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $projects = new repositories\projects();
            $ticketRepo = new repositories\tickets();
            $sprintService = new services\sprints();
            $ticketService = new services\tickets();

            $_SESSION['lastPage'] = "/tickets/showAll";

            $allprojects = $projects->getUserProjects();
            $allSprints = $sprintService->getAllSprints($_SESSION["currentProject"]);

            //Set initial sprint
            if (isset($_SESSION['currentSprint']) === false || $_SESSION['currentSprint'] == '' || $_SESSION['currentSprint'] == 'none') {
                $currentSprint = $sprintService->getCurrentSprint($_SESSION['currentProject']);

                if (is_object($currentSprint) === true) {
                
                    $_SESSION['currentSprint'] = $currentSprint->id;
                
                } else {
                
                    if ($allSprints !== false && count($allSprints) > 0) {
                        $_SESSION['currentSprint'] = $allSprints[0]->id;
                    }else{
                        //No sprints available
                        $_SESSION['currentSprint'] = '';
                    }
                }

            }

            $searchCriteria = array("currentProject" => $_SESSION["currentProject"], "users" => "", "status" => "not_done", "searchType"=> "", "searchterm" => "", "sprint" => "", "milestone" => '');

            //Active search overrides
            if(isset($_COOKIE['searchCriteria']) == true) {
                $postedValues = unserialize($_COOKIE['searchCriteria']);
                $searchCriteria = $this->getSearchCriteriaFromPost($postedValues, $searchCriteria);
            }

            if (isset($_POST['search']) == true) {
                $searchCriteria = $this->getSearchCriteriaFromPost($_POST, $searchCriteria);
            }

            //QuickAdd
            if (isset($_POST['quickadd']) == true) {
                $result = $ticketService->quickAddTicket($_POST);

                if (isset($result["status"])) {
                    $tpl->setNotification($result["message"], $result["status"]);
                } else {
                    $tpl->setNotification("To-Do successfully added", "success");

                    $subject = "A new To-Do was added";
                    $actual_link = "https://$_SERVER[HTTP_HOST]/tickets/showTicket/". $result;
                    $message = "" . $_SESSION["userdata"]["name"] . " added a new To-Do to one of your projects: '".strip_tags($_POST['headline'])."'";
                    $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> "Click here to see it."));

                }
            }


            if (isset($_GET["sort"]) === true) {

                $sprint = $_GET["sprint"];

                $sortedTicketArray = array();

                foreach ($_POST["ticket"] as $key => $id) {
                    $sortedTicketArray[] = array("id" => $id, "sortIndex" => $key * 10000, "sprint" => $sprint);
                }

                $ticketRepo->updateTicketSorting($_SESSION["currentProject"], $sortedTicketArray);

            }

            if (isset($_GET["changeStatus"]) == true) {

                $id = $_POST["id"];
                $status = $_POST["status"];
                $ticketRepo->updateTicketStatus($id, $status);

                $state = $ticketRepo->stateLabels[$ticketRepo->statePlain[$status]];
                $ticket = $ticketRepo->getTicket($id);
                $headline = $ticket['headline'];
                $subject = "The status of a To-Do has changed.";
                $actual_link = "https://$_SERVER[HTTP_HOST]/tickets/showTicket/". $id;
                $message = "" . $_SESSION["userdata"]["name"]. " changed the status of '".$headline."' to '".$state."'";
                $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> "Click here to see it."));


            }



            $tpl->assign("onTheClock", $ticketRepo->isClocked($_SESSION["userdata"]["id"]));


            $searchCriteria["sprint"] = 'none';
            $tpl->assign('allBacklogTickets', $ticketRepo->getAllBySearchCriteria($searchCriteria));


            //If sprint is empty that means that we don't have a single sprint available. Don't perform search for sprints.
            if($_SESSION['currentSprint'] != "") {
                $searchCriteriaSprint = array("currentProject" => $_SESSION["currentProject"], "users" => "", "status" => "", "searchType"=> "", "searchterm" => "", "sprint" => $_SESSION['currentSprint'], "milestone" => '');
                $tpl->assign('allSprintTickets', $ticketRepo->getAllBySearchCriteria($searchCriteriaSprint));
            }else{
                $tpl->assign('allSprintTickets', []);
            }

            $tpl->assign("currentSprint", $_SESSION['currentSprint']);

            $tpl->assign("users", $projects->getUsersAssignedToProject($searchCriteria["currentProject"]));

            $searchCriteria["users"] = array_filter(
                explode(",", $searchCriteria["users"]), function ($s) {
                    if($s == "") { return false; 
                    } else { return true;
                    }
                }
            );
            //$searchCriteria["status"] = array_filter(explode(",", $searchCriteria["status"]), function($s) {if($s == "") return false; else return true;} );
            $searchCriteria["sprint"] = array_filter(
                explode(",", $searchCriteria["sprint"]), function ($s) {
                    if($s == "") { return false; 
                    } else { return true;
                    }
                }
            );
            $tpl->assign('searchCriteria', $searchCriteria);


            $tpl->assign("sprints", $allSprints);

            $tpl->assign("futureSprints", $sprintService->getAllFutureSprints($_SESSION["currentProject"]));

            $tpl->assign('allProjects', $allprojects);
            $tpl->assign('allSprints', $ticketRepo->getAllSprintsByProject($searchCriteria["currentProject"]));
            $tpl->assign('allTicketStates', $ticketRepo->statePlain);
            $tpl->assign('tickets', $ticketRepo);
            $tpl->assign("milestones", $ticketService->getAllMilestones($searchCriteria["currentProject"]));
            $tpl->assign('efforts', $ticketRepo->efforts);
            $tpl->assign("types", $ticketRepo->type);

            if (isset($_GET["raw"]) === false) {
                $tpl->display('tickets.showAll');
            }
        }

        public function get($params) {


            $searchCriteria = $this->ticketService->prepareTicketSearchArray($params);

            $this->tpl->assign('allTickets', $this->ticketService->getAll($searchCriteria));
            $this->tpl->assign('allTicketStates', $this->ticketService->getStatusLabels());
            $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
            $this->tpl->assign('types', $this->ticketService->getTicketTypes());
            $this->tpl->assign('ticketTypeIcons', $this->ticketService->getTypeIcons());

            $this->tpl->assign('searchCriteria', $searchCriteria);

            $this->tpl->assign('onTheClock', $this->timesheetService->isClocked($_SESSION["userdata"]["id"]));

            $this->tpl->assign('sprints', $this->sprintService->getAllSprints($_SESSION["currentProject"]));
            $this->tpl->assign('futureSprints', $this->sprintService->getAllFutureSprints($_SESSION["currentProject"]));

            $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject($_SESSION["currentProject"]));
            $this->tpl->assign('milestones', $this->ticketService->getAllMilestones($_SESSION["currentProject"]));

            $this->tpl->assign('currentSprint', $_SESSION["currentSprint"]);
            $this->tpl->assign('allSprints', $this->sprintService->getAllSprints($_SESSION["currentProject"]));

            $this->tpl->display('tickets.showAll');

        }



    }

}

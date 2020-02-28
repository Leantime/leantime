<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\services;

    class showKanban
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

            $_SESSION['lastPage'] = CURRENT_URL;

        }

        public function get(array $params) {

            $currentSprint = $this->sprintService->getCurrentSprint($_SESSION['currentProject']);

            $searchCriteria = $this->ticketService->prepareTicketSearchArray($params);
            $searchCriteria["orderBy"] = "kanbanSortIndex";

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

            $this->tpl->display('tickets.showKanban');

        }

        public function post(array $params) {


            //QuickAdd
            if(isset($_POST['quickadd']) == true) {

                $result = $this->ticketService->quickAddTicket($params);

                if(isset($result["status"]) ) {
                    $this->tpl->setNotification($result["message"], $result["status"]);


                }
            }

            $this->tpl->redirect($_SERVER['REQUEST_URI']);

        }

        /**
         * run - display template and edit data
         *
         * @access public

        public function run()
        {






            //Default Search Criteria, set current project

            //Set initial sprint


            //Active search overrides
            /*if(isset($_COOKIE['searchCriteria']) == true) {
                $postedValues = unserialize($_COOKIE['searchCriteria']);
                $searchCriteria = $this->getSearchCriteriaFromPost($postedValues, $searchCriteria);
            }

            if(isset($_GET['milestone'])) {
                $searchCriteria['milestone'] = (int) $_GET['milestone'];
            }
            //Active search overrides
            if(isset($_POST['search']) == true) {
                $searchCriteria = $this->getSearchCriteriaFromPost($_POST, $searchCriteria);
            }
*/





  /*          if(isset($_GET["sort"]) === true) {

                $sortedTicketArray = array();

                foreach($_POST as $status=>$ticketArray){

                    $params = explode("&", $ticketArray);

                    if(is_array($params)=== true) {
                        foreach($params as $key => $ticketString){

                            $id = substr($ticketString, 9);
                            $ticketsRepo->updateTicketStatus($id, $status, ($key*10000));

                        }
                    }

                }

            }



            //prepare search criteria
            $searchCriteria["users"] = array_filter(
                explode(",", $searchCriteria["users"]), function ($s) {
                    if($s == "") { return false; 
                    } else { return true;
                    }
                }
            );
            $searchCriteria["status"] = array_filter(
                explode(",", $searchCriteria["status"]), function ($s) {
                    if($s == "") { return false; 
                    } else { return true;
                    }
                }
            );
            $searchCriteria["sprint"] = array_filter(
                explode(",", $searchCriteria["sprint"]), function ($s) {
                    if($s == "") { return false; 
                    } else { return true;
                    }
                }
            );
            $tpl->assign('searchCriteria', $searchCriteria);

            $tpl->assign('currentSprint', $_SESSION['currentSprint']);
            $tpl->assign('allSprints', $ticketsRepo->getAllSprintsByProject($searchCriteria["currentProject"]));


            unset($ticketsRepo->statePlain[-1]);
            $tpl->assign('allTicketStates', $ticketsRepo->statePlain);
            $tpl->assign('tickets', $ticketsRepo);
            $tpl->assign('efforts', $ticketsRepo->efforts);

            if(isset($_GET["raw"]) === false) {
                $tpl->display('tickets.showKanban');
            }
        }

        private function getSearchCriteriaFromPost($post, $searchCriteria)
        {


            if(isset($post["searchUsers"]) === true) {
                $searchCriteria["users"] = implode(",", $post["searchUsers"]);
            }else if(isset($post["users"]) === true) {
                $searchCriteria["users"] =  $post["users"];
            }else{
                $searchCriteria["users"] = '';
            }

            $searchCriteria["status"] = "";

            if(isset($post["searchTerm"]) === true) {
                $searchCriteria["searchterm"] =$post["searchTerm"];
            }else{
                $searchCriteria["searchterm"] = "";
            }

            if(isset($post["searchType"]) === true) {
                $searchCriteria["searchType"] =$post["searchType"];
            }else{
                $searchCriteria["searchType"] = "";
            }

            if(isset($post["searchMilestone"]) === true) {
                $searchCriteria["milestone"] =$post["searchMilestone"];
            }else if(isset($post["milestone"])) {
                $searchCriteria["milestone"] = $post["milestone"];
            }

            if(isset($post["searchSprints"]) === true) {
                $searchCriteria["sprint"] =  $post["searchSprints"];
                $_SESSION["currentSprint"] = $searchCriteria["sprint"];
            }else if(isset($post["sprint"]) === true) {
                $searchCriteria["sprint"] = $post["sprint"];
                $_SESSION["currentSprint"] = $searchCriteria["sprint"];
            }else{
                $searchCriteria["sprint"] = "";
            }


            setcookie("searchCriteria", serialize($searchCriteria), time()+3600, "/tickets/");

            return $searchCriteria;
        }*/


    }

}



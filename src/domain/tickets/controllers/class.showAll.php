<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class showAll
    {

        public function __construct() {
            $this->projectService = new services\projects();
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
            $currentSprint = "";

            if (isset($_SESSION['currentSprint']) === false || $_SESSION['currentSprint'] == '' || $_SESSION['currentSprint'] == 'none') {

                $currentSprint = $sprintService->getCurrentSprint($_SESSION['currentProject']);

                if (is_object($currentSprint) === true) {

                    $currentSprint =  $currentSprint->id;

                } else {

                    if ($allSprints !== false && count($allSprints) > 0) {

                        $currentSprint = $allSprints[0]->id;

                    }else{

                        //No sprints available
                        $currentSprint = '';

                    }
                }

            }else{
                $currentSprint = $_SESSION['currentSprint'];
            }

            $searchCriteria = array("currentProject" => $_SESSION["currentProject"], "users" => "", "status" => "not_done", "searchType"=> "", "searchterm" => "", "sprint" => $currentSprint, "milestone" => '');

            //Active search overrides
            /*if(isset($_COOKIE['searchCriteria']) == true) {
                //$postedValues = unserialize($_COOKIE['searchCriteria']);
                //$searchCriteria = $this->getSearchCriteriaFromPost($postedValues, $searchCriteria);
            }*/

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
                    $actual_link = BASE_URL."/tickets/showTicket/". $result;
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
                $actual_link = BASE_URL."/tickets/showTicket/". $id;
                $message = "" . $_SESSION["userdata"]["name"]. " changed the status of '".$headline."' to '".$state."'";
                $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> "Click here to see it."));


            }

            if (isset($_GET["punchIn"]) === true) {
                $ticketId = $_POST["ticketId"];
                $ticketRepo->punchIn($ticketId);

            }

            if (isset($_GET["punchOut"]) === true) {
                $ticketId = $_POST["ticketId"];
                $hoursBooked = $ticketRepo->punchOut($ticketId);
                echo $hoursBooked;
            }

            $tpl->assign("onTheClock", $ticketRepo->isClocked($_SESSION["userdata"]["id"]));


            //If sprint is empty that means that we don't have a single sprint available. Don't perform search for sprints.

            if($searchCriteria["sprint"] != "") {
                $searchCriteriaSprint = array("currentProject" => $_SESSION["currentProject"], "users" => "", "status" => "", "searchType"=> "", "searchterm" => "", "sprint" => $searchCriteria["sprint"], "milestone" => '');
                $tpl->assign('allSprintTickets', $ticketRepo->getAllBySearchCriteria($searchCriteriaSprint));
                $tpl->assign("currentSprint", $searchCriteriaSprint["sprint"]);
            }else{
                $tpl->assign('allSprintTickets', []);
                $tpl->assign("currentSprint", "");
            }

            $searchCriteria["sprint"] = 'none';
            $tpl->assign('allBacklogTickets', $ticketRepo->getAllBySearchCriteria($searchCriteria));



            $tpl->assign("users", $projects->getUsersAssignedToProject($searchCriteria["currentProject"]));

            $searchCriteria["users"] = array_filter(
                explode(",", $searchCriteria["users"]), function ($s) {
                    if($s == "") { return false; 
                    } else { return true;
                    }
                }
            );

            //$searchCriteria["status"] = array_filter(explode(",", $searchCriteria["status"]), function($s) {if($s == "") return false; else return true;} );
            /*$searchCriteria["sprint"] = array_filter(
                explode(",", $searchCriteria["sprint"]), function ($s) {
                    if($s == "") { return false; 
                    } else { return true;
                    }
                }
            );*/

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


        private function getSearchCriteriaFromPost($post, $searchCriteria)
        {


            if(isset($post["searchUsers"]) === true) {
                $searchCriteria["users"] = implode(",", $post["searchUsers"]);
            }else if(isset($post["users"]) === true) {
                $searchCriteria["users"] =  $post["users"];
            }else{
                $searchCriteria["users"] = '';
            }

            if (isset($post["searchStatus"]) === true) {
                $searchCriteria["status"] = implode(",", $post["searchStatus"]);
            }else if (isset($post["status"]) === true) {
                $searchCriteria["status"] = $post["status"];
            }else{
                $searchCriteria["status"] = "";
            }

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

            }else if(isset($post["sprint"]) === true) {
                $searchCriteria["sprint"] = $post["sprint"];

            }else{
                $searchCriteria["sprint"] = "";
            }


            setcookie("searchCriteria", serialize($searchCriteria), time()+3600, "/tickets/");

            return $searchCriteria;
        }



    }

}

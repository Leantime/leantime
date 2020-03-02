<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class showKanban
    {

        public function __construct()
        {
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
            $ticketsRepo = new repositories\tickets();
            $sprintService = new services\sprints();
            $ticketService = new services\tickets();

            $_SESSION['lastPage'] = "/tickets/showKanban";

            //Default Search Criteria, set current project

            //Set initial sprint

            $sprintService->getCurrentSprint($_SESSION['currentProject']);

            if(isset($_SESSION['currentSprint']) === false || $_SESSION['currentSprint'] == '') {
                $currentSprint = $sprintService->getCurrentSprint($_SESSION['currentProject']);

                if(is_object($currentSprint) === true) {
                    $_SESSION['currentSprint'] = $currentSprint->id;
                }else{
                    //If sprint doesnt exist. Show backlog
                    $_SESSION['currentSprint'] = "none";
                }

            }

            $searchCriteria = array("currentProject"=>$_SESSION["currentProject"],"users"=>"", "status"=>"", "searchterm"=> "", "searchType"=> "", "sprint"=>$_SESSION['currentSprint'], "milestone"=>"");

            //Active search overrides
            if(isset($_COOKIE['searchCriteria']) == true) {
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




            //QuickAdd
            if(isset($_POST['quickadd']) == true) {
                $result = $ticketService->quickAddTicket($_POST);

                if(isset($result["status"]) ) {
                    $tpl->setNotification($result["message"], $result["status"]);
                }else{

                    $tpl->setNotification("To-Do successfully added", "success");
                    $subject = "A new To-Do was added";
                    $actual_link = BASE_URL."/tickets/showTicket/". $result;
                    $message = "" . $_SESSION["userdata"]["name"] . " added a new To-Do to one of your projects: '".strip_tags($_POST['headline'])."'";
                    $this->projectService->notifyProjectUsers($message, $subject, $_SESSION['currentProject'], array("link"=>$actual_link, "text"=> "Click here to see it."));

                }
            }

            if(isset($_GET["sort"]) === true) {

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

            $tpl->assign("onTheClock", $ticketsRepo->isClocked($_SESSION["userdata"]["id"]));
            $tpl->assign("milestones", $ticketService->getAllMilestones($_SESSION["currentProject"]));

            $tpl->assign("sprints", $sprintService->getAllSprints($_SESSION["currentProject"]));
            $tpl->assign("futureSprints", $sprintService->getAllFutureSprints($_SESSION["currentProject"]));


            $tpl->assign('allTickets', $ticketsRepo->getAllBySearchCriteria($searchCriteria, 'kanbansort'));
            $tpl->assign("users", $projects->getUsersAssignedToProject($searchCriteria["currentProject"]));

            $tpl->assign("types", $ticketsRepo->type);

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
        }


    }
}



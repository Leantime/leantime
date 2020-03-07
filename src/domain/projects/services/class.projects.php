<?php

namespace leantime\domain\services {

    use leantime\core;
    use leantime\domain\repositories;
    use \DateTime;
    use \DateInterval;

    class projects
    {

        private $tpl;
        private $projectRepository;
        private $ticketRepository;
        private $settingsRepo;

        public function __construct()
        {

            $this->tpl = new core\template();
            $this->projectRepository = new repositories\projects();
            $this->ticketRepository = new repositories\tickets();
            $this->settingsRepo = new repositories\setting();
        }

        public function getProject($id) {
            return $this->projectRepository->getProject($id);
        }

        //Gets project progress
        public function getProjectProgress($projectId)
        {
            $returnValue = array("percent" => 0, "estimatedCompletionDate" => "We need more data to determine that.", "plannedCompletionDate" => "");

            $averageStorySize = $this->ticketRepository->getAverageTodoSize($projectId);

            //We'll use this as the start date of the project
            $firstTicket = $this->ticketRepository->getFirstTicket($projectId);

            if(is_object($firstTicket) === false) {
                return $returnValue;
            }

            $dateOfFirstTicket =  new DateTime($firstTicket->date);
            $today = new DateTime();
            $totalprojectDays = $today->diff($dateOfFirstTicket)->format("%a");


            //Calculate percent
            //The magic: Use effort calculation AND number of ticket calculation. Then get the average.
            $numberOfClosedTickets = $this->ticketRepository->getNumberOfClosedTickets($projectId);

            $numberOfTotalTickets = $this->ticketRepository->getNumberOfAllTickets($projectId);

            if($numberOfTotalTickets == 0) {
                $percentNum = 0;
            }else{
                $percentNum = ($numberOfClosedTickets / $numberOfTotalTickets) * 100;
            }


            $effortOfClosedTickets = $this->ticketRepository->getEffortOfClosedTickets($projectId, $averageStorySize);
            $effortOfTotalTickets = $this->ticketRepository->getEffortOfAllTickets($projectId, $averageStorySize);

            if($effortOfTotalTickets == 0) {
                $percentEffort = $percentNum; //This needs to be set to percentNum in case users choose to not use efforts
            }else{
                $percentEffort = ($effortOfClosedTickets / $effortOfTotalTickets) * 100;
            }
            

            $finalPercent = ($percentNum + $percentEffort) / 2;

            if($totalprojectDays > 0) {
                $dailyPercent = $finalPercent / $totalprojectDays;
            }else{
                $dailyPercent = 0;
            }

            $percentLeft = 100 - $finalPercent;

            if($dailyPercent == 0) {
                $estDaysLeftInProject = 10000;
            }else{
                $estDaysLeftInProject = ceil($percentLeft / $dailyPercent);
            }

            $today->add(new DateInterval('P'.$estDaysLeftInProject.'D'));


            //Fix this
            $currentDate = new DateTime();
            if($today->format("Y") > ($currentDate->format("Y") +5)) {
                $completionDate = "Past ".($currentDate->format("Y")+5);
            }else{
                $completionDate = $today->format('m/d/Y');
            }


            $returnValue = array("percent" => $finalPercent, "estimatedCompletionDate" => $completionDate , "plannedCompletionDate" => '');
            if($numberOfClosedTickets < 10) {
                $returnValue['estimatedCompletionDate'] = "<a href='".BASE_URL."/tickets/showAll' class='btn btn-primary'><span class=\"fa fa-thumb-tack\"></span> Complete more To-Dos to see that!</a>";
            }else if($finalPercent == 100) {
                $returnValue['estimatedCompletionDate'] = "<a href='".BASE_URL."/projects/showAll' class='btn btn-primary'><span class=\"fa fa-suitcase\"></span> This project is complete, onto the next!</a>";

            }
            return $returnValue;
        }

        public function getUsersToNotify($projectId)
        {

            $users = $this->projectRepository->getUsersAssignedToProject($projectId);

            $to = array();

            //Only users that actually want to be notified
            foreach ($users as $user) {

                if ($user["notifications"] != 0) {
                    $to[] = $user["username"];
                }
            }

            return $to;

        }

        public function notifyProjectUsers($message, $subject, $projectId, $url = false){

            $projectName = $this->getProjectName($projectId);

            //Email
            $users = $this->getUsersToNotify($projectId);

            $mailer = new core\mailer();
            $mailer->setSubject($subject);

            $emailMessage = $message;
            if($url !== false){
                $emailMessage .= " <a href='".$url['link']."'>".$url['text']."</a>";
            }
            $mailer->setHtml($emailMessage);
            $mailer->sendMail($users, $_SESSION["userdata"]["name"]);


            //Prepare message for chat applications (Slack, Mattermost)
            $prepareChatMessage = $message;
            if($url !== false){
                $prepareChatMessage .= " <".$url['link']."|".$url['text'].">";
            }

            $attachments = array([
                'fallback' => $subject,
                'pretext'  => $subject,
                'color'    => '#1b75bb',
                'fields'   => array(
                    [
                        'title' => "Project: ".$projectName,
                        'value' => $prepareChatMessage,
                        'short' => false
                    ]
                )
            ]);

            //Slack Webhook post
            $slackWebhookURL = $this->settingsRepo->getSetting("projectsettings." . $projectId. ".slackWebhookURL");
            if($slackWebhookURL !== "" && $slackWebhookURL !== false){

                $data = array(
                    'text'        => '',
                    'attachments' => $attachments
                );

                $data_string = json_encode($data);
                $ch = curl_init($slackWebhookURL);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($data_string))
                );

                //Execute CURL
                $result = curl_exec($ch);

            }

            //Mattermost Webhook Post
            $mattermostWebhookURL = $this->settingsRepo->getSetting("projectsettings." . $projectId. ".mattermostWebhookURL");
            if($mattermostWebhookURL !== "" && $mattermostWebhookURL !== false) {

                $data = array(
                    'username' => "Leantime",
                    "icon_url" => '',
                    'text' => '',
                    'attachments' => $attachments
                );

                $data_string = json_encode($data);
                $ch = curl_init($mattermostWebhookURL);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($data_string))
                );

                //Execute CURL
                $result = curl_exec($ch);
            }


            //Test Zulip
            $zulipWebhookSerialized = $this->settingsRepo->getSetting("projectsettings." . $projectId. ".zulipHook");

            if($zulipWebhookSerialized !== false && $zulipWebhookSerialized !== ""){

                $zulipWebhook = unserialize($zulipWebhookSerialized);

                $botEmail = $zulipWebhook['zulipEmail'];
                $botKey = $zulipWebhook['zulipBotKey'];
                $botURL = $zulipWebhook['zulipURL']."/api/v1/messages";

                $prepareChatMessage = "**Project: ".$projectName."** \n\r".$message;
                if($url !== false){
                    $prepareChatMessage .= "".$url['link']."";
                }

                $data = array(
                    "type" => "stream",
                    "to" => $zulipWebhook['zulipStream'],
                    "topic" => $zulipWebhook['zulipTopic'],
                    'content' => $prepareChatMessage
                );

                $curlUrl = $botURL . '?' . http_build_query($data);

                $ch = curl_init($curlUrl);

                $data_string = json_encode($data);

                curl_setopt($ch, CURLOPT_USERPWD, "$botEmail:$botKey");
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($data_string))
                );

                //Execute CURL
                $result = curl_exec($ch);

            }

        }

        public function getProjectName($projectId)
        {

            $project = $this->projectRepository->getProject($projectId);
            if($project) {
                return $project["name"];
            }

        }

        public function getProjectIdAssignedToUser($userId)
        {

            $project = $this->projectRepository->getUserProjectRelation($userId);

            if($project) {
                return $project;
            }else{
                return false;
            }

        }

        public function getProjectsAssignedToUser($userId, $projectStatus = "open")
        {
            $projects = $this->projectRepository->getUserProjects($userId, $projectStatus);

            if($projects) {
                return $projects;
            }else{
                return false;
            }

        }

        public function setCurrentProject () {

            //If projectId in URL use that as the project
            //$_GET is highest priority. Will overwrite current set session project
            //This comes from emails, shared links etc.
            if(isset($_GET['projectId']) === true){

                $projectId = filter_var($_GET['projectId'], FILTER_SANITIZE_NUMBER_INT);

                if($this->changeCurrentSessionProject($projectId) === true) {
                    return;
                }

            }

            //Find project if nothing is set
            //Login experience. If nothing is set look for the last set project
            //If there is none (new feature, new user) use the first project in the list.
            //Check that the user is still assigned to the project
            if(isset($_SESSION['currentProject']) === false || $_SESSION['currentProject'] == '' || $this->isUserAssignedToProject($_SESSION['userdata']['id'], $_SESSION['currentProject']) === false) {

                $_SESSION['currentProject'] = '';

                //If last project setting is set use that
                $lastProject = $this->settingsRepo->getSetting("usersettings.".$_SESSION['userdata']['id'].".lastProject");

                if($lastProject !== false && $lastProject != '' && $this->isUserAssignedToProject($_SESSION['userdata']['id'], $lastProject) !== false){

                    if($this->changeCurrentSessionProject($lastProject) === true) {
                        return;
                    }

                }else{

                    $allProjects = $this->getProjectsAssignedToUser($_SESSION['userdata']['id']);

                    if($allProjects !== false && count($allProjects) > 0) {

                        if($this->changeCurrentSessionProject($allProjects[0]['id']) === true) {
                            return;
                        }

                    }

                    $route = core\FrontController::getCurrentRoute();

                    if($_SESSION['userdata']['role'] == "manager" || $_SESSION['userdata']['role'] == "admin") {

                        $this->tpl->setNotification("You are not assigned to any projects. Please create a new one", "info");
                        if($route != "projects.newProject") {
                            $this->tpl->redirect(BASE_URL."/projects/newProject");
                        }

                    }else{

                        $this->tpl->setNotification("You are not assigned to any projects. Please ask an administrator to assign you to one.", "info");

                        if($route != "users.editOwn") {
                            $this->tpl->redirect(BASE_URL."/users/editOwn");
                        }

                    }

                }

            }

        }

        public function changeCurrentSessionProject($projectId) {

            if($this->isUserAssignedToProject($_SESSION['userdata']['id'], $projectId) === true) {

                $project = $this->getProject($projectId);

                if ($project) {

                    $_SESSION["currentProject"] = $projectId;

                    if (strlen($project['name']) > 25) {
                        $_SESSION["currentProjectName"] = substr($project['name'], 0, 25) . " (...)";
                    } else {
                        $_SESSION["currentProjectName"] = $project['name'];
                    }

                    $_SESSION["currentProjectClient"] = $project['clientName'];

                    $_SESSION["currentSprint"] = "";
                    $_SESSION['currentLeanCanvas'] = "";
                    $_SESSION['currentIdeaCanvas'] = "";
                    $_SESSION['currentRetroCanvas'] = "";

                    $this->settingsRepo->saveSetting("usersettings.".$_SESSION['userdata']['id'].".lastProject", $_SESSION["currentProject"]);

                    return true;

                } else {

                    return false;

                }

            }else {

                return false;

            }

        }

        public function resetCurrentProject () {

            $_SESSION["currentProject"] = "";
            $_SESSION["currentProjectClient"] = "";
            $_SESSION["currentProjectName"] = "";

            $_SESSION["currentSprint"] = "";
            $_SESSION['currentLeanCanvas'] = "";
            $_SESSION['currentIdeaCanvas'] = "";
            $_SESSION['currentRetroCanvas'] = "";

            $this->settingsRepo->saveSetting("usersettings.".$_SESSION['userdata']['id'].".lastProject", $_SESSION["currentProject"]);

            $this->setCurrentProject();
        }

        public function getUsersAssignedToProject($projectId)
        {
            return $this->projectRepository->getUsersAssignedToProject($projectId);
        }

        public function isUserAssignedToProject($userId, $projectId) {

            return $this->projectRepository->isUserAssignedToProject($userId, $projectId);

        }

    }

}

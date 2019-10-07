<?php

namespace leantime\domain\services {

    use leantime\core;
    use leantime\domain\repositories;
    use \DateTime;
    use \DateInterval;
    use PHPMailer\PHPMailer\PHPMailer;

    class projects
    {

        private $projectRepository;
        private $sprintRepository;
        private $ticketRepository;

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
                $returnValue['estimatedCompletionDate'] = "<a href='/tickets/showAll' class='btn btn-primary'><span class=\"fa fa-thumb-tack\"></span> Complete more To-Dos to see that!</a>";
            }else if($finalPercent == 100) {
                $returnValue['estimatedCompletionDate'] = "<a href='/projects/showAll' class='btn btn-primary'><span class=\"fa fa-suitcase\"></span> This project is complete, onto the next!</a>";

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

        public function changeCurrentSessionProject($projectId) {

            $project = $this->getProject($projectId);


            
            $_SESSION["currentProject"] = $projectId;

            if(strlen($_SESSION['currentProjectName']) > 25){
                $_SESSION["currentProjectName"] = substr($_SESSION['currentProjectName'], 0, 25)." (...)";
            }else{
                $_SESSION["currentProjectName"] = $project['name'];
            }





            $_SESSION["currentProjectClient"] = $project['clientName'];

            $_SESSION["currentSprint"] = "";
            $_SESSION['currentLeanCanvas'] = "";
            $_SESSION['currentIdeaCanvas'] = "";
            $_SESSION['currentRetroCanvas'] = "";

        }

    }

}

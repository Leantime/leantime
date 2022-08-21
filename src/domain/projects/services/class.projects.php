<?php

namespace leantime\domain\services {

    use GuzzleHttp\Exception\RequestException;
    use leantime\core;
    use leantime\domain\repositories;
    use DateTime;
    use DateInterval;
    use League\HTMLToMarkdown\HtmlConverter;
    use GuzzleHttp\Client;
    use Psr\Http\Message\ResponseInterface;
    use leantime\domain\models\auth\roles;

    class projects
    {

        private $tpl;
        private $projectRepository;
        private $ticketRepository;
        private $settingsRepo;
        private $language;

        public function __construct()
        {

            $this->tpl = new core\template();
            $this->projectRepository = new repositories\projects();
            $this->ticketRepository = new repositories\tickets();
            $this->settingsRepo = new repositories\setting();
            $this->filesRepository = new repositories\files();
            $this->language = new core\language();
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
            $inFiveYears = intval($currentDate->format("Y")) + 5;
            
            if(intval($today->format("Y")) >= $inFiveYears) {
                $completionDate = "Past ".$inFiveYears;
            }else{
                $completionDate = $today->format($this->language->__('language.dateformat'));
            }


            $returnValue = array("percent" => $finalPercent, "estimatedCompletionDate" => $completionDate , "plannedCompletionDate" => '');
            if($numberOfClosedTickets < 10) {
                $returnValue['estimatedCompletionDate'] = "<a href='".BASE_URL."/tickets/showAll' class='btn btn-primary'><span class=\"fa fa-thumb-tack\"></span> Complete more To-Dos to see that!</a>";
            }elseif($finalPercent == 100) {
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

            $httpClient = new Client();

            //Email
            $users = $this->getUsersToNotify($projectId);
            $users = array_filter($users, function($user, $k) { 
                return $user != $_SESSION['userdata']['mail']; 
            }, ARRAY_FILTER_USE_BOTH);


            $mailer = new core\mailer();
            $mailer->setSubject($subject);

            $emailMessage = $message;
            if($url !== false){
                $emailMessage .= " <a href='".$url['link']."'>".$url['text']."</a>";
            }
            $mailer->setHtml($emailMessage);
            //$mailer->sendMail($users, $_SESSION["userdata"]["name"]);

	    // NEW Queuing messaging system
	    $queue = new repositories\queue();
            $queue->queueMessageToUsers($users, $emailMessage, $subject, $projectId);


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
                        'title' => $this->language->__("headlines.project_with_name")." ".$projectName,
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

                try {
                    $httpClient->post($slackWebhookURL, [
                        'body' => $data_string,
                        'headers' => [ 'Content-Type' => 'application/json' ]
                    ]);
                }catch (\Exception $e) {
                    error_log($e->getMessage());
                }

            }

            //Discord Webhook post
            $converter = FALSE;
            for ($i = 1; 3 >= $i ; $i++) {
              $discordWebhookURL = $this->settingsRepo->getSetting('projectsettings.' . $projectId . '.discordWebhookURL' . $i);
              if ($discordWebhookURL !== "" && $discordWebhookURL !== FALSE) {
                if (!$converter) {
                  $converter = new HtmlConverter();
                }
                $timestamp = date('c', strtotime('now'));
                $fields = [
                  // Additional data to be sent; e.g.:
                  //[
                  //  'name' => $subject,
                  //  'value' => $message,
                  //  'inline' => FALSE
                  //],
                ];
                $url_link = (
                  empty($url['link'])
                    ? ''
                    : $url['link']
                );

                // For details on the JSON layout: https://birdie0.github.io/discord-webhooks-guide/index.html
                $data_string = json_encode([
                  'content' => 'Leantime' . ' - ' . $_SESSION['companysettings.sitename'],
                  'avatar_url' => 'https://s3-us-west-2.amazonaws.com/leantime-website/wp-content/uploads/2019/03/22224016/logoIcon.png',
                  'tts' => FALSE,
                  'embeds' => [
                    [
                      'title' => $subject,
                      'type' => 'rich',
                      'description' => $converter->convert($message),
                      'url' => $url_link,
                      'timestamp' => $timestamp,
                      'color' => hexdec('1b75bb'),
                      'footer' => [
                        'text' => 'Leantime',
                        'icon_url' => $url_link,
                      ],
                      'author' => [
                        'name' =>  $projectName,
                        'url' => $url_link
                      ],
                      'fields' => $fields,
                    ]
                  ]

                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

                try {
                      $httpClient->post($discordWebhookURL, [
                          'body' => $data_string,
                          'headers' => [ 'Content-Type' => 'application/json' ]
                      ]);
                }catch (\Exception $e) {
                      error_log($e->getMessage());
                }
              }
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

                try {
                    $httpClient->post($mattermostWebhookURL, [
                        'body' => $data_string
                    ]);
                }catch (\Exception $e) {
                    error_log($e->getMessage());
                }

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
                    $prepareChatMessage .= " ".$url['link']."";
                }

                $data = array(
                    "type" => "stream",
                    "to" => $zulipWebhook['zulipStream'],
                    "topic" => $zulipWebhook['zulipTopic'],
                    'content' => $prepareChatMessage
                );

                $curlUrl = $botURL . '?' . http_build_query($data);

                $data_string = json_encode($data);

                try {

                    $httpClient->post($curlUrl, [
                        'body' => $data_string,
                        'headers' => [ 'Content-Type' => 'application/json' ],
                        'auth' => [
                            $botEmail,
                            $botKey
                        ]
                    ]);

                }catch (\Exception $e) {
                    error_log($e->getMessage());
                }

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

            $projects = $this->projectRepository->getUserProjectRelation($userId);

            if($projects) {
                return $projects;
            }else{
                return false;
            }

        }



        public function getProjectsAssignedToUser($userId, $projectStatus = "open", $clientId = "")
        {
            $projects = $this->projectRepository->getUserProjects($userId, $projectStatus, $clientId);

            if($projects) {
                return $projects;
            }else{
                return false;
            }

        }

        public function getProjectRole($userId, $projectId) {

            $project = $this->projectRepository->getUserProjectRelation($userId, $projectId);

            if(is_array($project)) {
                if(isset($project[0]['projectRole']) && $project[0]['projectRole'] != ''){
                    return $project[0]['projectRole'];
                }else{
                    return "";
                }
            }else{
                return "";
            }
        }

        public function getProjectsUserHasAccessTo($userId, $projectStatus = "open", $clientId = "")
        {
            $projects = $this->projectRepository->getProjectsUserHasAccessTo($userId, $projectStatus, $clientId);

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

                }

            }

        }

        public function changeCurrentSessionProject($projectId) {

            if($this->isUserAssignedToProject($_SESSION['userdata']['id'], $projectId) === true) {

                //Get user project role

                $project = $this->getProject($projectId);

                if ($project) {

                    $projectRole = $this->getProjectRole($_SESSION['userdata']['id'], $projectId);


                    $_SESSION["currentProject"] = $projectId;

                    if (mb_strlen($project['name']) > 25) {
                        $_SESSION["currentProjectName"] = mb_substr($project['name'], 0, 25) . " (...)";
                    } else {
                        $_SESSION["currentProjectName"] = $project['name'];
                    }

                    $_SESSION["currentProjectClient"] = $project['clientName'];

                    $_SESSION['userdata']['projectRole'] = '';
                    if($projectRole != '') {
                        $_SESSION['userdata']['projectRole'] = roles::getRoleString($projectRole);
                    }

                    $_SESSION["currentSprint"] = "";
                    $_SESSION['currentLeanCanvas'] = "";
                    $_SESSION['currentIdeaCanvas'] = "";
                    $_SESSION['currentRetroCanvas'] = "";
                    $_SESSION['lastTicketView'] = "";
                    $_SESSION['lastFilterdTicketTableView'] = "";
                    $_SESSION['lastFilterdTicketKanbanView'] = "";

                    $this->settingsRepo->saveSetting("usersettings.".$_SESSION['userdata']['id'].".lastProject", $_SESSION["currentProject"]);

                    unset($_SESSION["projectsettings"]);

                    $_SESSION["projectsettings"]['commentOrder'] = $this->settingsRepo->getSetting("projectsettings." . $projectId . ".commentOrder");
                    $_SESSION["projectsettings"]['ticketLayout'] = $this->settingsRepo->getSetting("projectsettings." . $projectId . ".ticketLayout");

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
            unset($_SESSION["projectsettings"]);

            $this->settingsRepo->saveSetting("usersettings.".$_SESSION['userdata']['id'].".lastProject", $_SESSION["currentProject"]);

            $this->setCurrentProject();
        }

        public function getUsersAssignedToProject($projectId): array
        {
            $users = $this->projectRepository->getUsersAssignedToProject($projectId);

            if($users) {

                foreach ($users as &$user) {

                    $file = $this->filesRepository->getFile($user['profileId']);

                    $return = '/images/default-user.png';
                    if ($file) {
                        $return = "/download.php?module=" . $file['module'] . "&encName=" . $file['encName'] . "&ext=" . $file['extension'] . "&realName=" . $file['realName'];
                    }

                    $user["profilePicture"] = $return;

                }

                return $users;

            }

            return array();

        }

        public function isUserAssignedToProject($userId, $projectId) {

            return $this->projectRepository->isUserAssignedToProject($userId, $projectId);

        }

        public function duplicateProject(int $projectId, int $clientId, string $projectName, string $startDate, bool $assignSameUsers) {

            //Ignoring
            //Comments, files, timesheets, personalCalendar Events
            $oldProjectId = $projectId;

            //Copy project Entry
            $projectValues = $this->getProject($projectId);

            $copyProject = array(
                "name" => $projectName,
                "clientId" => $clientId,
                "details" => $projectValues['details'],
                "state" => $projectValues['state'],
                "hourBudget" => $projectValues['hourBudget'],
                "dollarBudget" => $projectValues['dollarBudget'],
                'assignedUsers' => array(),
            );

            if($assignSameUsers == true){
                $projectUsers = $this->projectRepository->getUsersAssignedToProject($projectId);

                foreach($projectUsers as $user) {
                    $copyProject['assignedUsers'][] = $user['id'];
                }
            }

            $projectSettingsKeys = array("researchlabels", "retrolabels", "ticketlabels", "idealabels");
            $newProjectId = $this->projectRepository->addProject($copyProject);

            //ProjectSettings
            foreach($projectSettingsKeys as $key) {
                $setting = $this->settingsRepo->getSetting("projectsettings.".$projectId.".".$key);

                if($setting !== false){
                    $this->settingsRepo->saveSetting("projectsettings.".$newProjectId.".".$key, $setting);
                }

            }

            //Duplicate all todos without dependent Ticket set
            $allTickets = $this->ticketRepository->getAllByProjectId($projectId);

            //Checks the oldest editFrom date and makes this the start date
            $oldestTicket = new DateTime();

            foreach($allTickets as $ticket) {
                if( $ticket->editFrom != null && $ticket->editFrom != "" && $ticket->editFrom != "0000-00-00 00:00:00" && $ticket->editFrom != "1969-12-31 00:00:00"){
                    $ticketDateTimeObject = datetime::createFromFormat("Y-m-d H:i:s", $ticket->editFrom);
                    if($oldestTicket > $ticketDateTimeObject){
                        $oldestTicket = $ticketDateTimeObject;

                    }

                }

                if($ticket->dateToFinish != null && $ticket->dateToFinish != "" && $ticket->dateToFinish != "0000-00-00 00:00:00" && $ticket->dateToFinish != "1969-12-31 00:00:00"){
                    $ticketDateTimeObject = datetime::createFromFormat("Y-m-d H:i:s", $ticket->dateToFinish);
                    if($oldestTicket > $ticketDateTimeObject){
                        $oldestTicket = $ticketDateTimeObject;

                    }

                }
            }


            $projectStart = new DateTime($startDate);
            $interval = $oldestTicket->diff($projectStart);

            //oldId = > newId
            $ticketIdList = array();

            //Iterate through root tickets first
            foreach($allTickets as $ticket) {
                if ($ticket->dependingTicketId == 0 || $ticket->dependingTicketId == "" || $ticket->dependingTicketId == null){

                    $dateToFinishValue = "";
                    if( $ticket->dateToFinish != null && $ticket->dateToFinish != "" && $ticket->dateToFinish != "0000-00-00 00:00:00" && $ticket->dateToFinish != "1969-12-31 00:00:00") {
                        $dateToFinish = new DateTime($ticket->dateToFinish);
                        $dateToFinish->add($interval);
                        $dateToFinishValue = $dateToFinish->format('Y-m-d H:i:s');
                    }

                    $editFromValue = "";
                    if( $ticket->editFrom != null && $ticket->editFrom != "" && $ticket->editFrom != "0000-00-00 00:00:00" && $ticket->editFrom != "1969-12-31 00:00:00") {
                        $editFrom = new DateTime($ticket->editFrom);
                        $editFrom->add($interval);
                        $editFromValue = $editFrom->format('Y-m-d H:i:s');
                    }

                    $editToValue = "";
                    if( $ticket->editTo != null && $ticket->editTo != "" && $ticket->editTo != "0000-00-00 00:00:00" && $ticket->editTo != "1969-12-31 00:00:00") {
                        $editTo = new DateTime($ticket->editTo);
                        $editTo->add($interval);
                        $editToValue = $editTo->format('Y-m-d H:i:s');
                    }

                    $ticketValues = array(
                        'headline' => $ticket->headline,
                        'type' => $ticket->type,
                        'description' => $ticket->description,
                        'projectId' => $newProjectId,
                        'editorId' => $ticket->editorId,
                        'userId' => $_SESSION['userdata']['id'],
                        'date' => date("Y-m-d H:i:s"),
                        'dateToFinish' => $dateToFinishValue,
                        'status' => $ticket->status,
                        'storypoints' => $ticket->storypoints,
                        'hourRemaining' => $ticket->hourRemaining,
                        'planHours' => $ticket->planHours,
                        'sprint' => "",
                        'acceptanceCriteria' => $ticket->acceptanceCriteria,
                        'tags' => $ticket->tags,
                        'editFrom' => $editFromValue,
                        'editTo' => $editToValue,
                        'dependingTicketId' => "",
                    );

                    $newTicketId = $this->ticketRepository->addTicket($ticketValues);

                    $ticketIdList[$ticket->id] = $newTicketId;
                }

            }

            //Iterate through childObjects
            foreach($allTickets as $ticket) {
                if ($ticket->dependingTicketId != "" && $ticket->dependingTicketId > 0){

                    $dateToFinishValue = "";
                    if( $ticket->dateToFinish != null && $ticket->dateToFinish != "" && $ticket->dateToFinish != "0000-00-00 00:00:00" && $ticket->dateToFinish != "1969-12-31 00:00:00") {
                        $dateToFinish = new DateTime($ticket->dateToFinish);
                        $dateToFinish->add($interval);
                        $dateToFinishValue = $dateToFinish->format('Y-m-d H:i:s');
                    }

                    $editFromValue = "";
                    if( $ticket->editFrom != null && $ticket->editFrom != "" && $ticket->editFrom != "0000-00-00 00:00:00" && $ticket->editFrom != "1969-12-31 00:00:00") {
                        $editFrom = new DateTime($ticket->editFrom);
                        $editFrom->add($interval);
                        $editFromValue = $editFrom->format('Y-m-d H:i:s');
                    }

                    $editToValue = "";
                    if( $ticket->editTo != null && $ticket->editTo != "" && $ticket->editTo != "0000-00-00 00:00:00" && $ticket->editTo != "1969-12-31 00:00:00") {
                        $editTo = new DateTime($ticket->editTo);
                        $editTo->add($interval);
                        $editToValue = $editTo->format('Y-m-d H:i:s');
                    }

                    $ticketValues = array(
                        'headline' => $ticket->headline,
                        'type' => $ticket->type,
                        'description' => $ticket->description,
                        'projectId' => $newProjectId,
                        'editorId' => $ticket->editorId,
                        'userId' => $_SESSION['userdata']['id'],
                        'date' => date("Y-m-d H:i:s"),
                        'dateToFinish' => $dateToFinishValue,
                        'status' => $ticket->status,
                        'storypoints' => $ticket->storypoints,
                        'hourRemaining' => $ticket->hourRemaining,
                        'planHours' => $ticket->planHours,
                        'sprint' => "",
                        'acceptanceCriteria' => $ticket->acceptanceCriteria,
                        'tags' => $ticket->tags,
                        'editFrom' => $editFromValue,
                        'editTo' => $editToValue,
                        'dependingTicketId' => $ticketIdList[$ticket->dependingTicketId],
                    );

                    $newTicketId = $this->ticketRepository->addTicket($ticketValues);

                    $ticketIdList[$ticket->id] = $newTicketId;
                }

            }



            //Duplicate Canvas boards
            $canvasIdList = array();

            //LeanCanvas
            $leancanvasRepo = new repositories\leancanvas();
            $canvasBoards = $leancanvasRepo->getAllCanvas($projectId);
            foreach($canvasBoards as $canvas) {

                $canvasValues = array(
                    "title" => $canvas['title'],
                    "author" => $_SESSION['userdata']['id'],
                    "projectId" => $newProjectId

                );

                $newCanvasId = $leancanvasRepo->addCanvas($canvasValues);
                $canvasIdList[$canvas['id']] = $newCanvasId;

                $canvasItems = $leancanvasRepo->getCanvasItemsById($canvas['id']);

                if($canvasItems != false && count($canvasItems) >0) {
                    foreach ($canvasItems as $item) {

                        $milestoneId = "";
                        if(isset($ticketIdList[$item['milestoneId']])){
                            $milestoneId = $ticketIdList[$item['milestoneId']];
                        }

                        $canvasItemValues = array(
                            "description" => $item['description'],
                            "assumptions" => $item['assumptions'],
                            "data" => $item['data'],
                            "conclusion" => $item['conclusion'],
                            "box" => $item['box'],
                            "author" => $item['author'],

                            "canvasId" => $newCanvasId,
                            "sortindex" => $item['sortindex'],
                            "status" => $item['status'],
                            "milestoneId" => $milestoneId
                        );

                        $leancanvasRepo->addCanvasItem($canvasItemValues);
                    }
                }
            }


            //Ideas
            $ideaRepo = new repositories\ideas();
            $canvasBoards = $ideaRepo->getAllCanvas($projectId);
            foreach($canvasBoards as $canvas) {

                $canvasValues = array(
                    "title" => $canvas['title'],
                    "author" => $_SESSION['userdata']['id'],
                    "projectId" => $newProjectId

                );

                $newCanvasId = $ideaRepo->addCanvas($canvasValues);
                $canvasIdList[$canvas['id']] = $newCanvasId;

                $canvasItems = $ideaRepo->getCanvasItemsById($canvas['id']);

                if($canvasItems != false && count($canvasItems) >0) {
                    foreach ($canvasItems as $item) {

                        $milestoneId = "";
                        if(isset($ticketIdList[$item['milestoneId']])){
                            $milestoneId = $ticketIdList[$item['milestoneId']];
                        }

                        $canvasItemValues = array(
                            "description" => $item['description'],
                            "assumptions" => $item['assumptions'],
                            "data" => $item['data'],
                            "conclusion" => $item['conclusion'],
                            "box" => $item['box'],
                            "author" => $item['author'],

                            "canvasId" => $newCanvasId,
                            "sortindex" => $item['sortindex'],
                            "status" => $item['status'],
                            "milestoneId" => $milestoneId
                        );

                        $ideaRepo->addCanvasItem($canvasItemValues);
                    }
                }
            }

            //Retros
            $retroRepo = new repositories\retrospectives();
            $canvasBoards = $retroRepo->getAllCanvas($projectId);
            foreach($canvasBoards as $canvas) {

                $canvasValues = array(
                    "title" => $canvas['title'],
                    "author" => $_SESSION['userdata']['id'],
                    "projectId" => $newProjectId

                );

                $newCanvasId = $retroRepo->addCanvas($canvasValues);
                $canvasIdList[$canvas['id']] = $newCanvasId;

                $canvasItems = $retroRepo->getCanvasItemsById($canvas['id']);

                if($canvasItems != false && count($canvasItems) >0) {
                    foreach ($canvasItems as $item) {

                        $milestoneId = "";
                        if(isset($ticketIdList[$item['milestoneId']])){
                            $milestoneId = $ticketIdList[$item['milestoneId']];
                        }

                        $canvasItemValues = array(
                            "description" => $item['description'],
                            "assumptions" => $item['assumptions'],
                            "data" => $item['data'],
                            "conclusion" => $item['conclusion'],
                            "box" => $item['box'],
                            "author" => $item['author'],

                            "canvasId" => $newCanvasId,
                            "sortindex" => $item['sortindex'],
                            "status" => $item['status'],
                            "milestoneId" => $milestoneId
                        );

                        $retroRepo->addCanvasItem($canvasItemValues);
                    }
                }
            }

            return $newProjectId;

        }

    }

}

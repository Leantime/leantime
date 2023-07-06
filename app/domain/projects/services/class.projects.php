<?php

namespace leantime\domain\services {

    use GuzzleHttp\Exception\RequestException;
    use leantime\core;
    use leantime\core\eventhelpers;
    use leantime\domain\models\notification;
    use leantime\domain\repositories;
    use DateTime;
    use DateInterval;
    use League\HTMLToMarkdown\HtmlConverter;
    use GuzzleHttp\Client;
    use leantime\domain\services\notifications\messengers;
    use Psr\Http\Message\ResponseInterface;
    use leantime\domain\models\auth\roles;

    class projects
    {
        use eventhelpers;

        private core\template $tpl;
        private repositories\projects $projectRepository;
        private repositories\tickets $ticketRepository;
        private repositories\setting $settingsRepo;
        private core\language $language;
        private messengers $messengerService;
        private notifications $notificationService;
        private repositories\files $filesRepository;

        public function __construct()
        {

            $this->tpl = new core\template();
            $this->projectRepository = new repositories\projects();
            $this->ticketRepository = new repositories\tickets();
            $this->settingsRepo = new repositories\setting();
            $this->filesRepository = new repositories\files();
            $this->language = core\language::getInstance();
            $this->messengerService = new messengers();
            $this->notificationService = new notifications();
        }

        public function getProject($id)
        {
            return $this->projectRepository->getProject($id);
        }

        //Gets project progress
        public function getProjectProgress($projectId)
        {
            $returnValue = array("percent" => 0, "estimatedCompletionDate" => "We need more data to determine that.", "plannedCompletionDate" => "");

            $averageStorySize = $this->ticketRepository->getAverageTodoSize($projectId);

            //We'll use this as the start date of the project
            $firstTicket = $this->ticketRepository->getFirstTicket($projectId);

            if (is_object($firstTicket) === false) {
                return $returnValue;
            }

            $dateOfFirstTicket =  new DateTime($firstTicket->date);
            $today = new DateTime();
            $totalprojectDays = $today->diff($dateOfFirstTicket)->format("%a");


            //Calculate percent

            $numberOfClosedTickets = $this->ticketRepository->getNumberOfClosedTickets($projectId);

            $numberOfTotalTickets = $this->ticketRepository->getNumberOfAllTickets($projectId);

            if ($numberOfTotalTickets == 0) {
                $percentNum = 0;
            } else {
                $percentNum = ($numberOfClosedTickets / $numberOfTotalTickets) * 100;
            }

            $effortOfClosedTickets = $this->ticketRepository->getEffortOfClosedTickets($projectId, $averageStorySize);
            $effortOfTotalTickets = $this->ticketRepository->getEffortOfAllTickets($projectId, $averageStorySize);

            if ($effortOfTotalTickets == 0) {
                $percentEffort = $percentNum; //This needs to be set to percentNum in case users choose to not use efforts
            } else {
                $percentEffort = ($effortOfClosedTickets / $effortOfTotalTickets) * 100;
            }

            $finalPercent = $percentEffort;

            if ($totalprojectDays > 0) {
                $dailyPercent = $finalPercent / $totalprojectDays;
            } else {
                $dailyPercent = 0;
            }

            $percentLeft = 100 - $finalPercent;

            if ($dailyPercent == 0) {
                $estDaysLeftInProject = 10000;
            } else {
                $estDaysLeftInProject = ceil($percentLeft / $dailyPercent);
            }

            $today->add(new DateInterval('P' . $estDaysLeftInProject . 'D'));


            //Fix this
            $currentDate = new DateTime();
            $inFiveYears = intval($currentDate->format("Y")) + 5;

            if (intval($today->format("Y")) >= $inFiveYears) {
                $completionDate = "Past " . $inFiveYears;
            } else {
                $completionDate = $today->format($this->language->__('language.dateformat'));
            }


            $returnValue = array("percent" => $finalPercent, "estimatedCompletionDate" => $completionDate , "plannedCompletionDate" => '');
            if ($numberOfClosedTickets < 10) {
                $returnValue['estimatedCompletionDate'] = "<a href='" . BASE_URL . "/tickets/showAll' class='btn btn-primary'><span class=\"fa fa-thumb-tack\"></span> Complete more To-Dos to see that!</a>";
            } elseif ($finalPercent == 100) {
                $returnValue['estimatedCompletionDate'] = "<a href='" . BASE_URL . "/projects/showAll' class='btn btn-primary'><span class=\"fa fa-suitcase\"></span> This project is complete, onto the next!</a>";
            }
            return $returnValue;
        }

        public function getUsersToNotify($projectId)
        {

            $users = $this->projectRepository->getUsersAssignedToProject($projectId);

            $to = array();

            //Only users that actually want to be notified and are active
            foreach ($users as $user) {
                if ($user["notifications"] != 0 && strtolower($user["status"]) == 'a') {
                    $to[] = $user["id"];
                }
            }

            return $to;
        }

        public function getAllUserInfoToNotify($projectId)
        {

            $users = $this->projectRepository->getUsersAssignedToProject($projectId);

            $to = array();

            //Only users that actually want to be notified
            foreach ($users as $user) {
                if ($user["notifications"] != 0 && ($user['username'] != $_SESSION['userdata']['mail'])) {
                    $to[] = $user;
                }
            }

            return $to;
        }

        //TODO Split and move to notifications
        public function notifyProjectUsers(\leantime\domain\models\notifications\notification $notification)
        {

            //Email
            $users = $this->getUsersToNotify($notification->projectId);
            $projectName = $this->getProjectName($notification->projectId);

            $users = array_filter($users, function ($user) use ($notification) {
                return $user != $notification->authorId;
            }, ARRAY_FILTER_USE_BOTH);

            /*
            $mailer = new core\mailer();
            $mailer->setContext('notify_project_users');
            $mailer->setSubject($notification->subject);


            $mailer->setHtml($emailMessage);
            //$mailer->sendMail($users, $_SESSION["userdata"]["name"]);
            */

            $emailMessage = $notification->message;
            if ($notification->url !== false) {
                $emailMessage .= " <a href='" . $notification->url['url'] . "'>" . $notification->url['text'] . "</a>";
            }

            // NEW Queuing messaging system
            $queue = new repositories\queue();
            $queue->queueMessageToUsers($users, $emailMessage, $notification->subject, $notification->projectId);

            //Send to messengers
            $this->messengerService->sendNotificationToMessengers($notification, $projectName);

            //Notify users about mentions
            //Fields that should be parsed for mentions
            $mentionFields = array(
                "comments" => array("text"),
                "projects" => array("details"),
                "tickets" => array("description"),
                "canvas" => array("description", "data", "conclusion", "assumptions")
            );

            $contentToCheck = '';
            //Find entity ID & content
            //Todo once all entities are models this if statement can be reduced
            if (isset($notification->entity) && is_array($notification->entity) && isset($notification->entity["id"])) {
                $entityId = $notification->entity["id"];

                if (isset($mentionFields[$notification->module])) {
                    $fields = $mentionFields[$notification->module];

                    foreach ($fields as $field) {
                        if (isset($notification->entity[$field])) {
                            $contentToCheck .= $notification->entity[$field];
                        }
                    }
                }
            } elseif (isset($notification->entity) && is_object($notification->entity) && isset($notification->entity->id)) {
                $entityId = $notification->entity->id;

                if (isset($mentionFields[$notification->module])) {
                    $fields = $mentionFields[$notification->module];

                    foreach ($fields as $field) {
                        if (isset($notification->entity->$field)) {
                            $contentToCheck .= $notification->entity->$field;
                        }
                    }
                }
            } else {
                //Entity id not set use project id
                $entityId = $notification->projectId;
            }

            if ($contentToCheck != '') {
                $this->notificationService->processMentions(
                    $contentToCheck,
                    $notification->module,
                    $entityId,
                    $notification->authorId,
                    $notification->url["url"]
                );
            }

            core\events::dispatch_event("notifyProjectUsers", array("type" => "projectUpdate", "module" => $notification->module, "moduleId" => $entityId, "message" => $notification->message, "subject" => $notification->subject, "users" => $this->getAllUserInfoToNotify($notification->projectId), "url" => $notification->url['url']), "domain.services.projects");
        }

        public function getProjectName($projectId)
        {

            $project = $this->projectRepository->getProject($projectId);
            if ($project) {
                return $project["name"];
            }
        }

        public function getProjectIdAssignedToUser($userId)
        {

            $projects = $this->projectRepository->getUserProjectRelation($userId);

            if ($projects) {
                return $projects;
            } else {
                return false;
            }
        }



        public function getProjectsAssignedToUser($userId, $projectStatus = "open", $clientId = "")
        {
            $projects = $this->projectRepository->getUserProjects($userId, $projectStatus, $clientId);

            if ($projects) {
                return $projects;
            } else {
                return false;
            }
        }


        public function getProjectHierarchyAssignedToUser($userId, $projectStatus = "open", $clientId = "")
        {

            //Build 3 level project selector
            $projectHierarchy = array(
                "strategy"=> array("enabled"=>false, "parents"=> array("noStrategyParent"), "items" => array()), //Only one type allowed
                "program" => array("enabled"=>false, "parents"=> array("noProgramParent"), "items" => array()), //Multiple types possible (projects/programs)
                "project" => array("enabled"=>true, "items" => array()) //Multiple types possible (projects/other)
            );

            $projectHierarchy = self::dispatch_filter('beforeLoadingProjects', $projectHierarchy);

            $projects = $this->projectRepository->getUserProjects($userId, $projectStatus, $clientId, $projectHierarchy);

            $projectHierarchy = self::dispatch_filter('beforePopulatingProjectHierarchy', $projectHierarchy, array("projects"=>$projects));

            //Fill projectColumns
            foreach($projects as $project) {

                //Add all items that have strategy as parent.
                if($project['type'] == '' || $project['type'] == null || $project['type'] == 0){
                    $project['type'] = 'project';
                }

                //Get project items with parent id but user does not have access to parent
                if(!in_array($project['parent'], $projectHierarchy["strategy"]["parents"]) && !in_array($project['parent'], $projectHierarchy["program"]["parents"]) && $project["type"] != "program" && $project["type"] != "strategy"){
                    if($projectHierarchy['strategy']["enabled"] === true) {
                        $project['parent'] = "noStrategyParent";
                    }
                    if($projectHierarchy['program']["enabled"] === true) {
                        $project['parent'] = "noProgramParent";
                    }
                }

                //IF the pgm module is not active, add all items
                if($projectHierarchy['program']["enabled"] === false) {
                    if ($project['type'] != "program" && $project['type'] != "strategy") {
                        $projectHierarchy["project"]["items"]['project'][$project['id']] = $project;
                    }
                } else {

                    //Get items with program parents
                    if(in_array($project['parent'], $projectHierarchy["program"]["parents"]) && $project['type'] != "program" && $project['type'] != "strategy"){
                        $projectHierarchy["project"]["items"][$project['type']][$project['id']] = $project;
                    }

                    //Get items without parents and project type(s)
                    if ($project["type"] !== "program" && $project["type"] !== "strategy" && ($project['parent'] == 0 || $project['parent'] == '' || $project['parent'] == null)) {
                        $project["parent"] = "noparent";
                        $projectHierarchy["project"]["items"][$project['type']][$project['id']] = $project;
                    }
                }
            }

            if ($projectHierarchy) {
                return $projectHierarchy;
            } else {
                return false;
            }
        }

        public function getProjectRole($userId, $projectId)
        {

            $project = $this->projectRepository->getUserProjectRelation($userId, $projectId);

            if (is_array($project)) {
                if (isset($project[0]['projectRole']) && $project[0]['projectRole'] != '') {
                    return $project[0]['projectRole'];
                } else {
                    return "";
                }
            } else {
                return "";
            }
        }

        public function getProjectsUserHasAccessTo($userId, $projectStatus = "open", $clientId = "")
        {
            $projects = $this->projectRepository->getProjectsUserHasAccessTo($userId, $projectStatus, $clientId);

            if ($projects) {
                return $projects;
            } else {
                return false;
            }
        }

        public function setCurrentProject()
        {

            //If projectId in URL use that as the project
            //$_GET is highest priority. Will overwrite current set session project
            //This comes from emails, shared links etc.
            if (isset($_GET['projectId']) === true) {
                $projectId = filter_var($_GET['projectId'], FILTER_SANITIZE_NUMBER_INT);

                if ($this->changeCurrentSessionProject($projectId) === true) {
                    return;
                }
            }

            //Find project if nothing is set
            //Login experience. If nothing is set look for the last set project
            //If there is none (new feature, new user) use the first project in the list.
            //Check that the user is still assigned to the project
            if (isset($_SESSION['currentProject']) === false || $_SESSION['currentProject'] == '' || $this->isUserAssignedToProject($_SESSION['userdata']['id'], $_SESSION['currentProject']) === false) {
                $_SESSION['currentProject'] = '';

                //If last project setting is set use that
                $lastProject = $this->settingsRepo->getSetting("usersettings." . $_SESSION['userdata']['id'] . ".lastProject");

                if ($lastProject !== false && $lastProject != '' && $this->isUserAssignedToProject($_SESSION['userdata']['id'], $lastProject) !== false) {
                    if ($this->changeCurrentSessionProject($lastProject) === true) {
                        return;
                    }
                } else {
                    $allProjects = $this->getProjectsAssignedToUser($_SESSION['userdata']['id']);

                    if ($allProjects !== false && count($allProjects) > 0) {
                        if ($this->changeCurrentSessionProject($allProjects[0]['id']) === true) {
                            return;
                        }
                    }
                }
            }
        }

        public function changeCurrentSessionProject($projectId)
        {

            if(isset($_SESSION["currentProjectName"]) === false){
                $_SESSION["currentProjectName"] = '';
            }

            if ($this->isUserAssignedToProject($_SESSION['userdata']['id'], $projectId) === true) {
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
                    if ($projectRole != '') {
                        $_SESSION['userdata']['projectRole'] = roles::getRoleString($projectRole);
                    }

                    $_SESSION["currentSprint"] = "";
                    $_SESSION['currentIdeaCanvas'] = "";
                    $_SESSION['lastTicketView'] = "";
                    $_SESSION['lastFilterdTicketTableView'] = "";
                    $_SESSION['lastFilterdTicketKanbanView'] = "";
                    $_SESSION['currentWiki'] = '';
                    $_SESSION['lastArticle'] = "";

                    $_SESSION['currentSWOTCanvas'] = "";
                    $_SESSION['currentLEANCanvas'] = "";
                    $_SESSION['currentEMCanvas'] = "";
                    $_SESSION['currentINSIGHTSCanvas'] = "";
                    $_SESSION['currentSBCanvas'] = "";
                    $_SESSION['currentRISKSCanvas'] = "";
                    $_SESSION['currentEACanvas'] = "";
                    $_SESSION['currentLBMCanvas'] = "";
                    $_SESSION['currentOBMCanvas'] = "";
                    $_SESSION['currentDBMCanvas'] = "";
                    $_SESSION['currentSQCanvas'] = "";
                    $_SESSION['currentCPCanvas'] = "";
                    $_SESSION['currentSMCanvas'] = "";
                    $_SESSION['currentRETROSCanvas'] = "";
                    $this->settingsRepo->saveSetting("usersettings." . $_SESSION['userdata']['id'] . ".lastProject", $_SESSION["currentProject"]);


                    $recentProjects =  $this->settingsRepo->getSetting("usersettings." . $_SESSION['userdata']['id'] . ".recentProjects");
                    $recent = unserialize($recentProjects);

                    if(is_array($recent) === false) {
                        $recent = array();
                    }
                    $key = array_search($_SESSION["currentProject"], $recent);
                    if ($key !== false) {
                        unset($recent[$key]);
                    }
                    array_unshift($recent, $_SESSION["currentProject"]);

                    $recent = array_slice($recent, 0, 20);

                    $this->settingsRepo->saveSetting("usersettings." . $_SESSION['userdata']['id'] . ".recentProjects", serialize($recent));

                    unset($_SESSION["projectsettings"]);

                    self::dispatch_event("projects.setCurrentProject");

                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        public function resetCurrentProject()
        {

            $_SESSION["currentProject"] = "";
            $_SESSION["currentProjectClient"] = "";
            $_SESSION["currentProjectName"] = "";

            $_SESSION["currentSprint"] = "";
            $_SESSION['currentIdeaCanvas'] = "";

            $_SESSION['currentSWOTCanvas'] = "";
            $_SESSION['currentLEANCanvas'] = "";
            $_SESSION['currentEMCanvas'] = "";
            $_SESSION['currentINSIGHTSCanvas'] = "";
            $_SESSION['currentSBCanvas'] = "";
            $_SESSION['currentRISKSCanvas'] = "";
            $_SESSION['currentEACanvas'] = "";
            $_SESSION['currentLBMCanvas'] = "";
            $_SESSION['currentOBMCanvas'] = "";
            $_SESSION['currentDBMCanvas'] = "";
            $_SESSION['currentSQCanvas'] = "";
            $_SESSION['currentCPCanvas'] = "";
            $_SESSION['currentSMCanvas'] = "";
            $_SESSION['currentRETROSCanvas'] = "";
            unset($_SESSION["projectsettings"]);

            $this->settingsRepo->saveSetting("usersettings." . $_SESSION['userdata']['id'] . ".lastProject", $_SESSION["currentProject"]);

            $this->setCurrentProject();
        }

        public function getUsersAssignedToProject($projectId): array
        {
            $users = $this->projectRepository->getUsersAssignedToProject($projectId);

            if ($users) {
                return $users;
            }

            return array();
        }

        public function isUserAssignedToProject($userId, $projectId)
        {

            return $this->projectRepository->isUserAssignedToProject($userId, $projectId);
        }

        public function duplicateProject(int $projectId, int $clientId, string $projectName, string $userStartDate, bool $assignSameUsers)
        {

            $startDate = datetime::createFromFormat($this->language->__("language.dateformat"), $userStartDate);

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
                "menuType" => $projectValues['menuType'],
                'psettings' => $projectValues['psettings'],
                'assignedUsers' => array(),
            );

            if ($assignSameUsers == true) {
                $projectUsers = $this->projectRepository->getUsersAssignedToProject($projectId);

                foreach ($projectUsers as $user) {
                    $copyProject['assignedUsers'][] = array("id" => $user['id'], "projectRole" => $user['projectRole']);
                }
            }

            $projectSettingsKeys = array("retrolabels", "ticketlabels", "idealabels");
            $newProjectId = $this->projectRepository->addProject($copyProject);

            //ProjectSettings
            foreach ($projectSettingsKeys as $key) {
                $setting = $this->settingsRepo->getSetting("projectsettings." . $projectId . "." . $key);

                if ($setting !== false) {
                    $this->settingsRepo->saveSetting("projectsettings." . $newProjectId . "." . $key, $setting);
                }
            }

            //Duplicate all todos without dependent Ticket set
            $allTickets = $this->ticketRepository->getAllByProjectId($projectId);

            //Checks the oldest editFrom date and makes this the start date
            $oldestTicket = new DateTime();

            foreach ($allTickets as $ticket) {
                if ($ticket->editFrom != null && $ticket->editFrom != "" && $ticket->editFrom != "0000-00-00 00:00:00" && $ticket->editFrom != "1969-12-31 00:00:00") {
                    $ticketDateTimeObject = datetime::createFromFormat("Y-m-d H:i:s", $ticket->editFrom);
                    if ($oldestTicket > $ticketDateTimeObject) {
                        $oldestTicket = $ticketDateTimeObject;
                    }
                }

                if ($ticket->dateToFinish != null && $ticket->dateToFinish != "" && $ticket->dateToFinish != "0000-00-00 00:00:00" && $ticket->dateToFinish != "1969-12-31 00:00:00") {
                    $ticketDateTimeObject = datetime::createFromFormat("Y-m-d H:i:s", $ticket->dateToFinish);
                    if ($oldestTicket > $ticketDateTimeObject) {
                        $oldestTicket = $ticketDateTimeObject;
                    }
                }
            }


            $projectStart = new DateTime($startDate);
            $interval = $oldestTicket->diff($projectStart);

            //oldId = > newId
            $ticketIdList = array();

            //Iterate through root tickets first
            foreach ($allTickets as $ticket) {
                if ($ticket->milestoneid == 0 || $ticket->milestoneid == "" || $ticket->milestoneid == null) {
                    $dateToFinishValue = "";
                    if ($ticket->dateToFinish != null && $ticket->dateToFinish != "" && $ticket->dateToFinish != "0000-00-00 00:00:00" && $ticket->dateToFinish != "1969-12-31 00:00:00") {
                        $dateToFinish = new DateTime($ticket->dateToFinish);
                        $dateToFinish->add($interval);
                        $dateToFinishValue = $dateToFinish->format('Y-m-d H:i:s');
                    }

                    $editFromValue = "";
                    if ($ticket->editFrom != null && $ticket->editFrom != "" && $ticket->editFrom != "0000-00-00 00:00:00" && $ticket->editFrom != "1969-12-31 00:00:00") {
                        $editFrom = new DateTime($ticket->editFrom);
                        $editFrom->add($interval);
                        $editFromValue = $editFrom->format('Y-m-d H:i:s');
                    }

                    $editToValue = "";
                    if ($ticket->editTo != null && $ticket->editTo != "" && $ticket->editTo != "0000-00-00 00:00:00" && $ticket->editTo != "1969-12-31 00:00:00") {
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
                        'priority' => $ticket->priority,
                        'sprint' => "",
                        'acceptanceCriteria' => $ticket->acceptanceCriteria,
                        'tags' => $ticket->tags,
                        'editFrom' => $editFromValue,
                        'editTo' => $editToValue,
                        'dependingTicketId' => "",
                        'milestoneid' => ''
                    );

                    $newTicketId = $this->ticketRepository->addTicket($ticketValues);

                    $ticketIdList[$ticket->id] = $newTicketId;
                }
            }

            //Iterate through childObjects
            foreach ($allTickets as $ticket) {
                if ($ticket->milestoneid != "" && $ticket->milestoneid > 0) {
                    $dateToFinishValue = "";
                    if ($ticket->dateToFinish != null && $ticket->dateToFinish != "" && $ticket->dateToFinish != "0000-00-00 00:00:00" && $ticket->dateToFinish != "1969-12-31 00:00:00") {
                        $dateToFinish = new DateTime($ticket->dateToFinish);
                        $dateToFinish->add($interval);
                        $dateToFinishValue = $dateToFinish->format('Y-m-d H:i:s');
                    }

                    $editFromValue = "";
                    if ($ticket->editFrom != null && $ticket->editFrom != "" && $ticket->editFrom != "0000-00-00 00:00:00" && $ticket->editFrom != "1969-12-31 00:00:00") {
                        $editFrom = new DateTime($ticket->editFrom);
                        $editFrom->add($interval);
                        $editFromValue = $editFrom->format('Y-m-d H:i:s');
                    }

                    $editToValue = "";
                    if ($ticket->editTo != null && $ticket->editTo != "" && $ticket->editTo != "0000-00-00 00:00:00" && $ticket->editTo != "1969-12-31 00:00:00") {
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
                        'priority' => $ticket->priority,
                        'sprint' => "",
                        'acceptanceCriteria' => $ticket->acceptanceCriteria,
                        'tags' => $ticket->tags,
                        'editFrom' => $editFromValue,
                        'editTo' => $editToValue,
                        'milestoneid' => $ticketIdList[$ticket->milestoneid],
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
            foreach ($canvasBoards as $canvas) {
                $canvasValues = array(
                    "title" => $canvas['title'],
                    "author" => $_SESSION['userdata']['id'],
                    "projectId" => $newProjectId

                );

                $newCanvasId = $leancanvasRepo->addCanvas($canvasValues);
                $canvasIdList[$canvas['id']] = $newCanvasId;

                $canvasItems = $leancanvasRepo->getCanvasItemsById($canvas['id']);

                if ($canvasItems != false && count($canvasItems) > 0) {
                    foreach ($canvasItems as $item) {
                        $milestoneid = "";
                        if (isset($ticketIdList[$item['milestoneid']])) {
                            $milestoneid = $ticketIdList[$item['milestoneid']];
                        }

                        $canvasItemValues = array(
                            "description" => $item['description'],
                            "assumptions" => $item['assumptions'],
                            "data" => $item['data'],
                            "conclusion" => $item['conclusion'],
                            "box" => $item['box'],
                            "author" => $item['author'],
                            "parent" => $item['parent'],
                            "title" => $item['title'],
                            "tags" => $item['tags'],
                            "canvasId" => $newCanvasId,
                            "sortindex" => $item['sortindex'],
                            "status" => $item['status'],
                            "milestoneId" => $milestoneid
                        );

                        $leancanvasRepo->addCanvasItem($canvasItemValues);
                    }
                }
            }


            //Ideas
            $ideaRepo = new repositories\ideas();
            $canvasBoards = $ideaRepo->getAllCanvas($projectId);
            foreach ($canvasBoards as $canvas) {
                $canvasValues = array(
                    "title" => $canvas['title'],
                    "author" => $_SESSION['userdata']['id'],
                    "projectId" => $newProjectId

                );

                $newCanvasId = $ideaRepo->addCanvas($canvasValues);
                $canvasIdList[$canvas['id']] = $newCanvasId;

                $canvasItems = $ideaRepo->getCanvasItemsById($canvas['id']);

                if ($canvasItems != false && count($canvasItems) > 0) {
                    foreach ($canvasItems as $item) {
                        $milestoneId = "";
                        if (isset($ticketIdList[$item['milestoneid']])) {
                            $milestoneId = $ticketIdList[$item['milestoneid']];
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

            return $newProjectId;
        }

        public function getProjectUserRelation($id)
        {
            return $this->projectRepository->getProjectUserRelation($id);
        }

        public function patch($id, $params)
        {
            return $this->projectRepository->patch($id, $params);
        }

        public function getProjectAvatar($id)
        {
            $avatar = $this->projectRepository->getProjectAvatar($id);
            $avatar = eventhelpers::dispatch_filter("afterGettingAvatar", $avatar, array("projectId"=>$id));
            return $avatar;
        }

        public function setProjectAvatar($file, $project) {
            return $this->projectRepository->setPicture($file, $project);
        }

        public function getAllProjects(){
            return $this->projectRepository->getAll();
        }

        public function getProjectSetupChecklist($projectId) {

            $progressSteps = array(
                "define" => array(
                    "title"=>"label.define",
                    "tasks" => array(
                        "description"=> array("title"=>"label.projectDescription", "status"=>""),
                        "defineTeam"=> array("title"=>"label.defineTeam", "status"=>""),
                        "createBlueprint"=> array("title"=>"label.createBlueprint", "status"=>""),
                    ),
                    "status" => '',
                ),
                "goals" => array(
                    "title"=>"label.setGoals",
                    "tasks" => array(
                        "setGoals"=> array("title"=>"label.setGoals", "status"=>""),
                    ),
                    "status" => ''
                ),
                "timeline" => array(
                    "title"=>"label.setTimeline",
                    "tasks" => array(
                        "createMilestones"=> array("title"=>"label.createMilestones", "status"=>""),

                    ),
                    "status" => '',
                ),
                "implementation" => array(
                    "title"=>"label.implementation",
                    "tasks" => array(
                        "createTasks"=>  array("title"=>"label.createTasks", "status"=>""),

                        "finish80percent"=>  array("title"=>"label.finish80percent", "status"=>""),
                    ),
                    "status" => '',
                )
            );

            //Todo determine tasks that are done.
            $project = $this->getProject($projectId);
            //Project Description
            if($project['details'] != ''){
                $progressSteps["define"]["tasks"]["description"]["status"] = "done";
            }

            if($project['numUsers'] > 1){
                $progressSteps["define"]["tasks"]["defineTeam"]["status"] = "done";
            }

            if($project['numDefinitionCanvas'] >= 1){
                $progressSteps["define"]["tasks"]["createBlueprint"]["status"] = "done";
            }

            $goals = new repositories\goalcanvas();
            $allCanvas = $goals->getAllCanvas($projectId);

            $totalGoals = 0;
            foreach($allCanvas as $goalsCanvas){

                $totalGoals = $totalGoals + $goalsCanvas['boxItems'];
            }
            if($totalGoals > 0){
                $progressSteps["define"]["goals"]["setGoals"]["status"] = "done";
            }

            if($project['numberMilestones'] >= 1){
                $progressSteps["timeline"]["tasks"]["createMilestones"]["status"] = "done";
            }

            if($project['numberOfTickets'] >= 1){
                $progressSteps["implementation"]["tasks"]["createTasks"]["status"] = "done";
            }

            $percentDone = $this->getProjectProgress($projectId);
            if($percentDone['percent'] >= 80){
                $progressSteps["implementation"]["tasks"]["finish80percent"]["status"] = "done";
            }

            //Add overrides
            $stepsCompleted = $this->settingsRepo->getSetting("projectsettings.".$projectId.".stepsComplete");

            if($stepsCompleted !== false) {
                $stepsCompleted = unserialize($stepsCompleted);
                foreach ($progressSteps as $key => $step) {

                    $progressSteps[$key]["tasks"] = $step['tasks'];

                    $stepCompleted = true;
                    foreach($progressSteps[$key]["tasks"] as $taskKey => $task) {

                        if (isset($stepsCompleted[$taskKey])) {
                            $progressSteps[$key]["tasks"][$taskKey]['status'] = "done";
                        }else if($progressSteps[$key]["tasks"][$taskKey]['status'] == 'done'){

                        }else{
                            $stepCompleted = false;
                        }

                    }

                    if($stepCompleted) {
                        $progressSteps[$key]['status'] = 'done';
                    }

                }
            }

            return $progressSteps;


        }

        public function updateProjectProgress($stepsComplete, $projectId) {

            $stepsDoneArray = array();

            if($stepsComplete != '') {
                //Steps complete comes in as serialized js string: key=on&key2=on etc. Only on keys will be submitted
                parse_str($stepsComplete, $stepsDoneArray);
                $this->settingsRepo->saveSetting(
                    "projectsettings." . $projectId . ".stepsComplete",
                    serialize($stepsDoneArray)
                );
            }else{
                return;
            }

        }

        public function updateProjectSorting($params) {

            //ticketId: sortIndex
            foreach ($params as $id => $sortKey) {

                if ($this->projectRepository->patch($id, ["sortIndex"=>$sortKey*100]) === false) {
                    return false;
                }

            }
        }

        public function updateProjectStatusAndSorting($params, $handler = null)
        {

            //Jquery sortable serializes the array for kanban in format
            //statusKey: item[]=X&item[]=X2...,
            //statusKey2: item[]=X&item[]=X2...,
            //This represents status & kanban sorting
            foreach ($params as $status => $projectList) {
                if (is_numeric($status) && !empty($projectList)) {
                    $projects = explode("&", $projectList);

                    if (is_array($projects) === true) {
                        foreach ($projects as $key => $projectString) {
                            $id = substr($projectString, 7);

                            $this->projectRepository->patch($id, ["sortIndex"=>$key*100, "state"=>$status]);

                        }
                    }
                }
            }

            /*
            if ($handler) {
                //Assumes format ticket_ID
                $id = substr($handler, 7);

                $ticket = $this->getTicket($id);

                if ($ticket) {
                    $subject = sprintf($this->language->__("email_notifications.todo_update_subject"), $id, $ticket->headline);
                    $actual_link = BASE_URL . "/tickets/showTicket/" . $id;
                    $message = sprintf($this->language->__("email_notifications.todo_update_message"), $_SESSION['userdata']['name'], $ticket->headline);

                    $notification = new models\notifications\notification();
                    $notification->url = array(
                        "url" => $actual_link,
                        "text" => $this->language->__("email_notifications.todo_update_cta")
                    );
                    $notification->entity = $ticket;
                    $notification->module = "tickets";
                    $notification->projectId = $_SESSION['currentProject'];
                    $notification->subject = $subject;
                    $notification->authorId = $_SESSION['userdata']['id'];
                    $notification->message = $message;

                    $this->projectService->notifyProjectUsers($notification);
                }
            }*/



            return true;
        }

    }

}

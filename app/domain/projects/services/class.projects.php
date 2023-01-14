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
                    $to[] = $user["username"];
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

        public function duplicateProject(int $projectId, int $clientId, string $projectName, string $startDate, bool $assignSameUsers)
        {

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
                    $copyProject['assignedUsers'][] = $user['id'];
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
                if ($ticket->dependingTicketId == 0 || $ticket->dependingTicketId == "" || $ticket->dependingTicketId == null) {
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
                    );

                    $newTicketId = $this->ticketRepository->addTicket($ticketValues);

                    $ticketIdList[$ticket->id] = $newTicketId;
                }
            }

            //Iterate through childObjects
            foreach ($allTickets as $ticket) {
                if ($ticket->dependingTicketId != "" && $ticket->dependingTicketId > 0) {
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
                        $milestoneId = "";
                        if (isset($ticketIdList[$item['milestoneId']])) {
                            $milestoneId = $ticketIdList[$item['milestoneId']];
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
                            "milestoneId" => $milestoneId
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
                        if (isset($ticketIdList[$item['milestoneId']])) {
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
            $retroRepo = new repositories\retroscanvas();
            $canvasBoards = $retroRepo->getAllCanvas($projectId);
            foreach ($canvasBoards as $canvas) {
                $canvasValues = array(
                    "title" => $canvas['title'],
                    "author" => $_SESSION['userdata']['id'],
                    "projectId" => $newProjectId

                );

                $newCanvasId = $retroRepo->addCanvas($canvasValues);
                $canvasIdList[$canvas['id']] = $newCanvasId;

                $canvasItems = $retroRepo->getCanvasItemsById($canvas['id']);

                if ($canvasItems != false && count($canvasItems) > 0) {
                    foreach ($canvasItems as $item) {
                        $milestoneId = "";
                        if (isset($ticketIdList[$item['milestoneId']])) {
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

        public function getProjectUserRelation($id)
        {
            return $this->projectRepository->getProjectUserRelation($id);
        }

        public function patch($id, $params)
        {
            return $this->projectRepository->patch($id, $params);
        }
    }

}

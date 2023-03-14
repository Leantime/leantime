<?php

namespace leantime\domain\services {

    use DateTime;
    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class tickets
    {
        private $projectRepository;
        private $ticketRepository;
        private $projectService;
        private $timesheetsRepo;
        private core\language $language;
        private core\template $tpl;
        private repositories\setting $settingsRepo;

        public function __construct()
        {

            $this->tpl = new core\template();
            $this->projectRepository = new repositories\projects();
            $this->ticketRepository = new repositories\tickets();
            $this->language = core\language::getInstance();
            $this->projectService = new services\projects();
            $this->timesheetsRepo = new repositories\timesheets();
            $this->settingsRepo = new repositories\setting();
        }

        //GET Properties
        public function getStatusLabels()
        {

            return $this->ticketRepository->getStateLabels();
        }

        public function getAllStatusLabelsByUserId($userId)
        {

            $statusLabelsByProject = array();

            $userProjects = $this->projectService->getProjectsAssignedToUser($userId);

            if ($userProjects) {
                foreach ($userProjects as $project) {
                    $statusLabelsByProject[$project['id']] = $this->ticketRepository->getStateLabels($project['id']);
                }
            }

            if (isset($_SESSION['currentProject'])) {
                $statusLabelsByProject[$_SESSION['currentProject']] = $this->ticketRepository->getStateLabels($_SESSION['currentProject']);
            }

            //There is a non zero chance that a user has tickets assigned to them without a project assignment.
            //Checking user assigned tickets to see if there are missing projects.
            $searchCriteria = $this->prepareTicketSearchArray(array("currentProject" => "", "users" => $userId, "status" => "not_done", "sprint" => ""));
            $allTickets = $this->ticketRepository->getAllBySearchCriteria($searchCriteria, "duedate");

            foreach ($allTickets as $row) {
                if (!isset($statusLabelsByProject[$row['projectId']])) {
                    $statusLabelsByProject[$row['projectId']] = $this->ticketRepository->getStateLabels($row['projectId']);
                }
            }

            return $statusLabelsByProject;
        }

        public function saveStatusLabels($params)
        {
            if (isset($params['labelKeys']) && is_array($params['labelKeys']) && count($params['labelKeys']) > 0) {
                $statusArray = array();

                foreach ($params['labelKeys'] as $labelKey) {
                    $labelKey = filter_var($labelKey, FILTER_SANITIZE_NUMBER_INT);

                    $statusArray[$labelKey] = array(
                        "name" => $params['label-' . $labelKey] ?? '',
                        "class" => $params['labelClass-' . $labelKey] ?? 'label-default',
                        "statusType" => $params['labelType-' . $labelKey] ?? 'NEW',
                        "kanbanCol" => $params['labelKanbanCol-' . $labelKey] ?? false,
                        "sortKey" => $params['labelSort-' . $labelKey] ?? 99
                    );
                }

                unset($_SESSION["projectsettings"]["ticketlabels"]);

                return $this->settingsRepo->saveSetting("projectsettings." . $_SESSION['currentProject'] . ".ticketlabels", serialize($statusArray));
            } else {
                return false;
            }
        }

        public function getKanbanColumns()
        {

            $statusList = $this->ticketRepository->getStateLabels();

            $visibleCols = array();

            foreach ($statusList as $key => $status) {
                if ($status['kanbanCol']) {
                    $visibleCols[$key] = $status;
                }
            }

            return $visibleCols;
        }

        public function getTypeIcons()
        {

            return $this->ticketRepository->typeIcons;
        }

        public function getEffortLabels()
        {

            return $this->ticketRepository->efforts;
        }

        public function getTicketTypes()
        {

            return $this->ticketRepository->type;
        }

        public function getPriorityLabels()
        {

            return $this->ticketRepository->priority;
        }

        public function prepareTicketSearchArray(array $searchParams)
        {

            $searchCriteria = array(
                "currentProject" => "",
                "users" => "",
                "status" => "",
                "term" => "",
                "type" => "",
                "sprint" => $_SESSION['currentSprint'] ?? '',
                "milestone" => "",
                "orderBy" => "sortIndex",
                "groupBy" => "",
                "priority" => "",
                "currentUser" => $_SESSION['userdata']["id"] ?? '',
                "currentClient" => $_SESSION['userdata']["clientId"] ?? '',
            );

            if (isset($_SESSION["currentProject"]) === true) {
                $searchCriteria["currentProject"] = $_SESSION["currentProject"];
            }

            if (isset($searchParams["currentProject"]) === true) {
                $searchCriteria["currentProject"] = $searchParams["currentProject"];
            }

            if (isset($searchParams["users"]) === true) {
                $searchCriteria["users"] = $searchParams["users"];
            }

            if (isset($searchParams["status"]) === true) {
                $searchCriteria["status"] = $searchParams["status"];
            }

            if (isset($searchParams["term"]) === true) {
                $searchCriteria["term"] = $searchParams["term"];
            }

            if (isset($searchParams["type"]) === true) {
                $searchCriteria["type"] = $searchParams["type"];
            }

            if (isset($searchParams["milestone"]) === true) {
                $searchCriteria["milestone"] = $searchParams["milestone"];
            }

            if (isset($searchParams["groupBy"]) === true) {
                $searchCriteria["groupBy"] = $searchParams["groupBy"];
            }

            if (isset($searchParams["orderBy"]) === true) {
                $searchCriteria["orderBy"] = $searchParams["orderBy"];
            }

            if (isset($searchParams["priority"]) === true) {
                $searchCriteria["priority"] = $searchParams["priority"];
            }

            if (isset($searchParams["currentUser"]) === true) {
                $searchCriteria["currentUser"] = $searchParams["currentUser"];
            }

            if (isset($searchParams["currentClient"]) === true) {
                $searchCriteria["currentClient"] = $searchParams["currentClient"];
            }

            if (isset($searchParams["sprint"]) === true) {
                $searchCriteria["sprint"] = $searchParams["sprint"];
                $_SESSION["currentSprint"] = $searchCriteria["sprint"];
            }

            $config = \leantime\core\environment::getInstance();
            setcookie(
                "searchCriteria",
                serialize($searchCriteria),
                [
                        'expires' => time() + 3600,
                        'path' => $config->appUrlRoot . "/tickets/",
                        'samesite' => 'Strict'
                    ]
            );

            return $searchCriteria;
        }

        public function countSetFilters(array $searchCriteria): int
        {
            $count = 0;
            foreach ($searchCriteria as $key => $value) {
                if ($key != "groupBy" && $key != "currentProject" && $key != "orderBy" && $key != "currentUser" &&  $key != "currentClient") {
                    if ($value != '') {
                        $count++;
                    }
                }
            }

            return $count;
        }

        //GET
        public function getAll($searchCriteria)
        {

            return $this->ticketRepository->getAllBySearchCriteria($searchCriteria, $searchCriteria['orderBy']);
        }

        public function getTicket($id)
        {

            $ticket = $this->ticketRepository->getTicket($id);

            //Check if user is allowed to see ticket
            if ($ticket && $this->projectService->isUserAssignedToProject($_SESSION['userdata']['id'], $ticket->projectId)) {
                //Fix date conversion
                //Todo: Move to views
                $ticket->date = $this->language->getFormattedDateString($ticket->date);
                $ticket->timeToFinish = $this->language->extractTime($ticket->dateToFinish);
                $ticket->dateToFinish = $this->language->getFormattedDateString($ticket->dateToFinish);
                $ticket->editFrom = $this->language->getFormattedDateString($ticket->editFrom);
                $ticket->editTo = $this->language->getFormattedDateString($ticket->editTo);

                return $ticket;
            }

            return false;
        }

        public function getOpenUserTicketsThisWeekAndLater($userId, $projectId)
        {

            $searchCriteria = $this->prepareTicketSearchArray(array("currentProject" => $projectId, "currentUser"=> $userId, "users" => $userId, "status" => "not_done", "sprint" => ""));
            $allTickets = $this->ticketRepository->getAllBySearchCriteria($searchCriteria, "duedate");

            $statusLabels = $this->getAllStatusLabelsByUserId($userId);

            $tickets = array(
            );

            foreach ($allTickets as $row) {
                //There is a non zero chance that a user has tasks assigned to them while not being part of the project
                //Need to get those status labels as well
                if (!isset($statusLabels[$row['projectId']])) {
                    $statusLabels[$row['projectId']] = $this->ticketRepository->getStateLabels($row['projectId']);
                }

                //There is a chance that the status was removed after it was assigned to a ticket
                if (isset($statusLabels[$row['projectId']][$row['status']]) && $statusLabels[$row['projectId']][$row['status']]['statusType'] != "DONE") {
                    if ($row['dateToFinish'] == "0000-00-00 00:00:00" || $row['dateToFinish'] == "1969-12-31 00:00:00") {
                        if (isset($tickets["later"]["tickets"])) {
                            $tickets["later"]["tickets"][] = $row;
                        } else {
                            $tickets['later'] = array(
                                "labelName" => "subtitles.todos_later",
                                "tickets" => array($row)
                            );
                        }
                    } else {
                        $date = new DateTime($row['dateToFinish']);

                        $nextFriday = strtotime('friday this week');
                        $nextFridayDateTime = new DateTime();
                        $nextFridayDateTime->setTimestamp($nextFriday);
                        if ($date <= $nextFridayDateTime) {
                            if (isset($tickets["thisWeek"]["tickets"])) {
                                $tickets["thisWeek"]["tickets"][] = $row;
                            } else {
                                $tickets['thisWeek'] = array(
                                    "labelName" => "subtitles.todos_this_week",
                                    "tickets" => array($row)
                                );
                            }
                        } else {
                            if (isset($tickets["later"]["tickets"])) {
                                $tickets["later"]["tickets"][] = $row;
                            } else {
                                $tickets['later'] = array(
                                    "labelName" => "subtitles.todos_later",
                                    "tickets" => array($row)
                                );
                            }
                        }
                    }
                }
            }

            return $tickets;
        }

        public function getLastTickets($projectId, $limit = 5)
        {

            $searchCriteria = $this->prepareTicketSearchArray(array("currentProject" => $projectId, "users" => "", "status" => "not_done", "sprint" => "", "limit" => $limit));
            $allTickets = $this->ticketRepository->getAllBySearchCriteria($searchCriteria, "date", $limit);

            return $allTickets;
        }

        public function getOpenUserTicketsByProject($userId, $projectId)
        {

            $searchCriteria = $this->prepareTicketSearchArray(array("currentProject" => $projectId, "users" => $userId, "status" => "", "sprint" => ""));
            $allTickets = $this->ticketRepository->getAllBySearchCriteria($searchCriteria, "duedate");

            $statusLabels = $this->getAllStatusLabelsByUserId($userId);

            $tickets = array();

            foreach ($allTickets as $row) {
                //Only include todos that are not done
                if ($statusLabels[$row['projectId']][$row['status']]['statusType'] != "DONE") {
                    if (isset($tickets[$row['projectId']])) {
                        $tickets[$row['projectId']]['tickets'][] = $row;
                    } else {
                        $tickets[$row['projectId']] = array(
                            "labelName" => $row['clientName'] . "//" . $row['projectName'],
                            "tickets" => array($row)
                        );
                    }
                }
            }

            return $tickets;
        }

        public function getAllMilestones($projectId, $includeArchived = false, $sortBy = "duedate", $includeTasks = false)
        {

            if ($projectId > 0) {
                return $this->ticketRepository->getAllMilestones($projectId, $includeArchived, $sortBy, $includeTasks);
            }

            return false;
        }

        public function getAllMilestonesOverview($includeArchived = false, $sortBy = "duedate", $includeTasks = false, $clientId = false)
        {
            return $this->ticketRepository->getAllMilestones(0, $includeArchived, $sortBy, $includeTasks, $clientId);
        }

        public function getAllMilestonesByUserProjects($userId)
        {

            $milestones = array();

            $userProjects = $this->projectService->getProjectsAssignedToUser($userId);
            if ($userProjects) {
                foreach ($userProjects as $project) {
                    $milestones[$project['id']] = $this->ticketRepository->getAllMilestones($project['id']);
                }
            }

            if (isset($_SESSION['currentProject'])) {
                $milestones[$_SESSION['currentProject']] = $this->ticketRepository->getAllMilestones($_SESSION['currentProject']);
            }

            //There is a non zero chance that a user has tickets assigned to them without a project assignment.
            //Checking user assigned tickets to see if there are missing projects.
            $searchCriteria = $this->prepareTicketSearchArray(array("currentProject" => "", "users" => $userId, "status" => "not_done", "sprint" => ""));
            $allTickets = $this->ticketRepository->getAllBySearchCriteria($searchCriteria, "duedate");

            foreach ($allTickets as $row) {
                if (!isset($milestones[$row['projectId']])) {
                    $milestones[$row['projectId']] = $this->ticketRepository->getAllMilestones($row['projectId']);
                }
            }

            return $milestones;
        }

        public function getAllSubtasks($ticketId)
        {
            $values = $this->ticketRepository->getAllSubtasks($ticketId);
            return $values;
        }

        //Add
        public function quickAddTicket($params)
        {

            $values = array(
                'headline' => $params['headline'],
                'type' => 'Task',
                'description' => isset($params['description']) ? $params['description'] : '',
                'projectId' => $params['projectId'] ?? $_SESSION['currentProject'],
                'editorId' => $_SESSION['userdata']['id'],
                'userId' => $_SESSION['userdata']['id'],
                'date' => date("Y-m-d H:i:s"),
                'dateToFinish' => isset($params['dateToFinish']) ? strip_tags($params['dateToFinish']) : "",
                'status' => isset($params['status']) ? (int) $params['status'] : 3,
                'storypoints' => '',
                'hourRemaining' => '',
                'planHours' => '',
                'sprint' => isset($params['sprint']) ? (int) $params['sprint'] : "",
                'acceptanceCriteria' => '',
                'priority' => '',
                'tags' => '',
                'editFrom' => '',
                'editTo' => '',
                'dependingTicketId' => isset($params['milestone']) ? (int) $params['milestone'] : ""
            );

            if ($values['headline'] == "") {
                $error = array("status" => "error", "message" => "Headline Missing");
                return $error;
            }

            $result = $this->ticketRepository->addTicket($values);

            if ($result > 0) {
                $values['id'] = $result;
                $actual_link = BASE_URL . "/tickets/showTicket/" . $result;
                $message = sprintf($this->language->__("email_notifications.new_todo_message"), $_SESSION["userdata"]["name"], $params['headline']);
                $subject = $this->language->__("email_notifications.new_todo_subject");

                $notification = new models\notifications\notification();
                $notification->url = array(
                    "url" => $actual_link,
                    "text" => $this->language->__("email_notifications.new_todo_cta")
                );
                $notification->entity = $values;
                $notification->module = "tickets";
                $notification->projectId = $_SESSION['currentProject'];
                $notification->subject = $subject;
                $notification->authorId = $_SESSION['userdata']['id'];
                $notification->message = $message;

                $this->projectService->notifyProjectUsers($notification);


                return $result;
            } else {
                return false;
            }
        }

        public function quickAddMilestone($params)
        {

            $values = array(
                'headline' => $params['headline'],
                'type' => 'milestone',
                'description' => '',
                'projectId' => $_SESSION['currentProject'],
                'editorId' => $_SESSION['userdata']['id'],
                'userId' => $_SESSION['userdata']['id'],
                'date' => date("Y-m-d H:i:s"),
                'dateToFinish' => "",
                'status' => 3,
                'storypoints' => '',
                'hourRemaining' => '',
                'planHours' => '',
                'sprint' => '',
                'priority' => 3,
                'dependingTicketId' => $params['dependentMilestone'],
                'acceptanceCriteria' => '',
                'tags' => $params['tags'],
                'editFrom' => $this->language->getISODateString($params['editFrom']),
                'editTo' => $this->language->getISODateString($params['editTo'])
            );

            if ($values['headline'] == "") {
                $error = array("status" => "error", "message" => "Headline Missing");
                return $error;
            }

            //$params is an array of field names. Exclude id
            return $this->ticketRepository->addTicket($values);
        }

        public function addTicket($values)
        {

            $values = array(
                'id' => '',
                'headline' => $values['headline'],
                'type' => $values['type'],
                'description' => $values['description'],
                'projectId' => $_SESSION['currentProject'],
                'editorId' => $values['editorId'],
                'userId' => $_SESSION['userdata']['id'],
                'date' => date('Y-m-d  H:i:s'),
                'dateToFinish' => $values['dateToFinish'],
                'timeToFinish' => $values['timeToFinish'],
                'status' => $values['status'],
                'planHours' => $values['planHours'],
                'tags' => $values['tags'],
                'sprint' => $values['sprint'],
                'storypoints' => $values['storypoints'],
                'hourRemaining' => $values['hourRemaining'],
                'priority' => $values['priority'],
                'acceptanceCriteria' => $values['acceptanceCriteria'],
                'editFrom' => $values['editFrom'],
                'editTo' => $values['editTo'],
                'dependingTicketId' => $values['dependingTicketId']
            );

            if (!$this->projectService->isUserAssignedToProject($_SESSION['userdata']['id'], $values['projectId'])) {
                return array("msg" => "notifications.ticket_save_error_no_access", "type" => "error");
            }

            if ($values['headline'] === '') {
                return array("msg" => "notifications.ticket_save_error_no_headline", "type" => "error");
            } else {
                //Prepare dates for db
                if ($values['dateToFinish'] != "" && $values['dateToFinish'] != null) {
                    $values['dateToFinish'] = $this->language->getISODateString($values['dateToFinish']);

                    if (isset($values['timeToFinish']) && $values['timeToFinish'] != null) {
                        $values['dateToFinish'] = str_replace("00:00:00", $values['timeToFinish'] . ":00", $values['dateToFinish']);
                    }
                }

                if ($values['editFrom'] != "" && $values['editFrom'] != null) {
                    $values['editFrom'] = $this->language->getISODateString($values['editFrom']);
                }

                if ($values['editTo'] != "" && $values['editTo'] != null) {
                    $values['editTo'] = $this->language->getISODateString($values['editTo']);
                }

                //Update Ticket
                $addTicketResponse = $this->ticketRepository->addTicket($values);
                if ($addTicketResponse !== false) {
                    $values["id"] = $addTicketResponse;
                    $subject = sprintf($this->language->__("email_notifications.new_todo_subject"), $addTicketResponse, $values['headline']);
                    $actual_link = BASE_URL . "/tickets/showTicket/" . $addTicketResponse;
                    $message = sprintf($this->language->__("email_notifications.new_todo_message"), $_SESSION['userdata']['name'], $values['headline']);

                    $notification = new models\notifications\notification();
                    $notification->url = array(
                        "url" => $actual_link,
                        "text" => $this->language->__("email_notifications.new_todo_cta")
                    );
                    $notification->entity = $values;
                    $notification->module = "tickets";
                    $notification->projectId = $_SESSION['currentProject'];
                    $notification->subject = $subject;
                    $notification->authorId = $_SESSION['userdata']['id'];
                    $notification->message = $message;

                    $this->projectService->notifyProjectUsers($notification);

                    return $addTicketResponse;
                }
            }
        }

        //Update
        public function updateTicket($id, $values)
        {

            $values = array(
                'id' => $id,
                'headline' => $values['headline'],
                'type' => $values['type'],
                'description' => $values['description'],
                'projectId' => $values['projectId'] ?? $_SESSION['currentProject'],
                'editorId' => $values['editorId'],
                'date' => date('Y-m-d  H:i:s'),
                'dateToFinish' => $values['dateToFinish'],
                'timeToFinish' => $values['timeToFinish'],
                'status' => $values['status'],
                'planHours' => $values['planHours'],
                'tags' => $values['tags'],
                'sprint' => $values['sprint'],
                'storypoints' => $values['storypoints'],
                'hourRemaining' => $values['hourRemaining'],
                'priority' => $values['priority'],
                'acceptanceCriteria' => $values['acceptanceCriteria'],
                'editFrom' => $values['editFrom'],
                'editTo' => $values['editTo'],
                'dependingTicketId' => $values['dependingTicketId']
            );

            if (!$this->projectService->isUserAssignedToProject($_SESSION['userdata']['id'], $values['projectId'])) {
                return array("msg" => "notifications.ticket_save_error_no_access", "type" => "error");
            }

            if ($values['headline'] === '') {
                return array("msg" => "notifications.ticket_save_error_no_headline", "type" => "error");
            } else {
                //Prepare dates for db
                if ($values['dateToFinish'] != "" && $values['dateToFinish'] != null) {
                    $values['dateToFinish'] = $this->language->getISODateString($values['dateToFinish']);

                    if (isset($values['timeToFinish']) && $values['timeToFinish'] != null) {
                        $values['dateToFinish'] = str_replace("00:00:00", $values['timeToFinish'] . ":00", $values['dateToFinish']);
                    }
                }

                if ($values['editFrom'] != "" && $values['editFrom'] != null) {
                    $values['editFrom'] = $this->language->getISODateString($values['editFrom']);
                }

                if ($values['editTo'] != "" && $values['editTo'] != null) {
                    $values['editTo'] = $this->language->getISODateString($values['editTo']);
                }
                //Update Ticket
                if ($this->ticketRepository->updateTicket($values, $id) === true) {
                    $subject = sprintf($this->language->__("email_notifications.todo_update_subject"), $id, $values['headline']);
                    $actual_link = BASE_URL . "/tickets/showTicket/" . $id;
                    $message = sprintf($this->language->__("email_notifications.todo_update_message"), $_SESSION['userdata']['name'], $values['headline']);


                    $notification = new models\notifications\notification();
                    $notification->url = array(
                        "url" => $actual_link,
                        "text" => $this->language->__("email_notifications.todo_update_cta")
                    );
                    $notification->entity = $values;
                    $notification->module = "tickets";
                    $notification->projectId = $_SESSION['currentProject'];
                    $notification->subject = $subject;
                    $notification->authorId = $_SESSION['userdata']['id'];
                    $notification->message = $message;

                    $this->projectService->notifyProjectUsers($notification);


                    return true;
                }
            }
        }

        public function patchTicket($id, $params)
        {

            //$params is an array of field names. Exclude id
            unset($params["id"]);

            return $this->ticketRepository->patchTicket($id, $params);
        }

        /**
         * moveTicket - Moves a ticket from one project to another. Milestone children will be moved as well
         *
         * @param int $id
         * @param int $projectId
         * @return bool
         */
        public function moveTicket(int $id, int $projectId): bool
        {

            $ticket = $this->getTicket($id);

            if($ticket) {

                //If milestone, move child todos
                if($ticket->type == "milestone"){
                    $milestoneTickets = $this->getAll(array("milestone"=>$ticket->id));
                    //Update child todos
                    foreach($milestoneTickets as $childTicket) {
                        $this->patchTicket($childTicket["id"], ["projectId" => $projectId, "sprint" => ""]);

                    }
                }

                //Update ticket
                return $this->patchTicket($ticket->id, ["projectId" => $projectId, "sprint" => "", "dependingTicketId" => ""]);


            }

            return false;

        }

        public function quickUpdateMilestone($params)
        {

            $values = array(
                'headline' => $params['headline'],
                'type' => 'milestone',
                'description' => '',
                'projectId' => $_SESSION['currentProject'],
                'editorId' => $params['editorId'],
                'userId' => $_SESSION['userdata']['id'],
                'date' => date("Y-m-d H:i:s"),
                'dateToFinish' => "",
                'status' => $params['status'],
                'storypoints' => '',
                'hourRemaining' => '',
                'planHours' => '',
                'sprint' => '',
                'acceptanceCriteria' => '',
                'priority' => 3,
                'dependingTicketId' => $params['dependentMilestone'],
                'tags' => $params['tags'],
                'editFrom' => $this->language->getISODateString($params['editFrom']),
                'editTo' => $this->language->getISODateString($params['editTo'])
            );

            if ($values['headline'] == "") {
                $error = array("status" => "error", "message" => "Headline Missing");
                return $error;
            }

            //$params is an array of field names. Exclude id
            return $this->ticketRepository->updateTicket($values, $params["id"]);
        }

        public function upsertSubtask($values, $parentTicket)
        {

            $subtaskId = $values['subtaskId'];

            $values = array(
                'headline' => $values['headline'],
                'type' => 'subtask',
                'description' => $values['description'] ?? '',
                'projectId' => $parentTicket->projectId,
                'editorId' => $_SESSION['userdata']['id'],
                'userId' => $_SESSION['userdata']['id'],
                'date' => date("Y-m-d H:i:s"),
                'dateToFinish' => "",
                'priority' => $values['priority'] ?? 3,
                'status' => $values['status'],
                'storypoints' => "",
                'hourRemaining' => $values['hourRemaining'] ?? 0,
                'planHours' => $values['planHours'] ?? 0,
                'sprint' => "",
                'acceptanceCriteria' => "",
                'tags' => "",
                'editFrom' => "",
                'editTo' => "",
                'dependingTicketId' => $parentTicket->id,
            );

            if ($subtaskId == "new" || $subtaskId == "") {
                //New Ticket
                if (!$this->ticketRepository->addTicket($values)) {
                    return false;
                }
            } else {
                //Update Ticket

                if (!$this->ticketRepository->updateTicket($values, $subtaskId)) {
                    return false;
                }
            }

            return true;
        }

        public function updateTicketSorting($params) {

            //ticketId: sortIndex
            foreach ($params as $id => $sortKey) {

                if ($this->ticketRepository->patchTicket($id, ["sortIndex"=>$sortKey*100]) === false) {
                    return false;
                }

            }
        }

        public function updateTicketStatusAndSorting($params, $handler = null)
        {

            //Jquery sortable serializes the array for kanban in format
            //statusKey: ticket[]=X&ticket[]=X2...,
            //statusKey2: ticket[]=X&ticket[]=X2...,
            //This represents status & kanban sorting
            foreach ($params as $status => $ticketList) {
                if (is_numeric($status) && !empty($ticketList)) {
                    $tickets = explode("&", $ticketList);

                    if (is_array($tickets) === true) {
                        foreach ($tickets as $key => $ticketString) {
                            $id = substr($ticketString, 9);

                            if ($this->ticketRepository->updateTicketStatus($id, $status, ($key * 100)) === false) {
                                return false;
                            }
                        }
                    }
                }
            }

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
            }



            return true;
        }

        //Delete
        public function deleteTicket($id)
        {

            $ticket = $this->getTicket($id);

            if (!$this->projectService->isUserAssignedToProject($_SESSION['userdata']['id'], $ticket->projectId)) {
                return array("msg" => "notifications.ticket_delete_error", "type" => "error");
            }

            if ($this->ticketRepository->delticket($id)) {
                return true;
            }

            return false;
        }

        public function deleteMilestone($id)
        {

            $ticket = $this->getTicket($id);

            if (!$this->projectService->isUserAssignedToProject($_SESSION['userdata']['id'], $ticket->projectId)) {
                return array("msg" => "notifications.milestone_delete_error", "type" => "error");
            }

            if ($this->ticketRepository->delMilestone($id)) {
                return true;
            }

            return false;
        }

        public function getLastTicketViewUrl()
        {

            $url = BASE_URL . "/tickets/showKanban";

            if (isset($_SESSION['lastTicketView']) && $_SESSION['lastTicketView'] != "") {

                if ($_SESSION['lastTicketView'] == "kanban" && isset($_SESSION['lastFilterdTicketKanbanView']) && $_SESSION['lastFilterdTicketKanbanView'] != "") {
                    return $_SESSION['lastFilterdTicketKanbanView'];
                }

                if ($_SESSION['lastTicketView'] == "table" && isset($_SESSION['lastFilterdTicketTableView']) && $_SESSION['lastFilterdTicketTableView'] != "") {
                    return $_SESSION['lastFilterdTicketTableView'];
                }

                if ($_SESSION['lastTicketView'] == "list" && isset($_SESSION['lastFilterdTicketListView']) && $_SESSION['lastFilterdTicketListView'] != "") {
                    return $_SESSION['lastFilterdTicketListView'];
                }

                return $url;
            } else {
                return $url;
            }
        }

        public function getGroupByFieldOptions()
        {
            return [
                [
                    'id' => 'groupByNothingLink',
                    'status' => '',
                    'label' => 'no_group'
                ],
                [
                    'id' => 'groupByStatusLink',
                    'status' => 'status',
                    'label' => 'todo_status'
                ],
                [
                    'id' => 'groupByPriorityLink',
                    'status' => 'priority',
                    'label' => 'priority'
                ],
                [
                    'id' => 'groupByMilestoneLink',
                    'status' => 'milestone',
                    'label' => 'milestone'
                ],
                [
                    'id' => 'groupByUserLink',
                    'status' => 'user',
                    'label' => 'user'
                ],
                [
                    'id' => 'groupBySprintLink',
                    'status' => 'sprint',
                    'label' => 'sprint'
                ],
                [
                    'id' => 'groupByTagsLink',
                    'status' => 'tags',
                    'label' => 'tags'
                ]
            ];
        }

        public function getNewFieldOptions()
        {
            if (!defined('BASE_URL')) {
                return [];
            }

            $baseUrl = BASE_URL;

            return [
                [
                    'url' => "$baseUrl/tickets/newTicket",
                    'text' => 'links.add_todo',
                    'class' => 'ticketModal'
                ],
                [
                    'url' => "$baseUrl/tickets/editMilestone",
                    'text' => 'links.add_milestone',
                    'class' => 'milestoneModal'
                ],
                [
                    'url' => "$baseUrl/sprints/editSprint",
                    'text' => 'links.add_sprint',
                    'class' => 'sprintModal'
                ]
            ];
        }
    }

}

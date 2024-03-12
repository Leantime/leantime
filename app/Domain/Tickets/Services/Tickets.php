<?php

namespace Leantime\Domain\Tickets\Services {

    use Carbon\CarbonInterface;
    use DateTime;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Eventhelpers;
    use Leantime\Core\Service;
    use Leantime\Core\Support\DateTimeHelper;
    use Leantime\Core\Support\FromFormat;
    use Leantime\Core\Template as TemplateCore;
    use Leantime\Core\Language as LanguageCore;
    use Leantime\Core\Environment as EnvironmentCore;
    use Leantime\Domain\Goalcanvas\Services\Goalcanvas;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
    use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
    use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
    use Leantime\Domain\Sprints\Services\Sprints as SprintService;
    use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
    use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
    use Leantime\Domain\Tickets\Repositories\TicketHistory as TicketHistory;

    /**
     *
     */
    class Tickets
    {

        Use Eventhelpers;


        /**
         * Constructor method for the class.
         *
         * @param TemplateCore $tpl The template core instance.
         * @param LanguageCore $language The language core instance.
         * @param EnvironmentCore $config The environment core instance.
         * @param ProjectRepository $projectRepository The project repository instance.
         * @param TicketRepository $ticketRepository The ticket repository instance.
         * @param TimesheetRepository $timesheetsRepo The timesheet repository instance.
         * @param SettingRepository $settingsRepo The setting repository instance.
         * @param ProjectService $projectService The project service instance.
         * @param TimesheetService $timesheetService The timesheet service instance.
         * @param SprintService $sprintService The sprint service instance.
         * @param TicketHistory $ticketHistoryRepo The ticket history repository instance.
         * @param Goalcanvas $goalcanvasService The goal canvas service instance.
         * @param DateTimeHelper $dateTimeHelper The date time helper instance.
         */
        public function __construct(
            private TemplateCore $tpl,
            private LanguageCore $language,
            private EnvironmentCore $config,
            private ProjectRepository $projectRepository,
            private TicketRepository $ticketRepository,
            private TimesheetRepository $timesheetsRepo,
            private SettingRepository $settingsRepo,
            private ProjectService $projectService,
            private TimesheetService $timesheetService,
            private SprintService $sprintService,
            private TicketHistory $ticketHistoryRepo,
            private Goalcanvas $goalcanvasService,
            private DateTimeHelper $dateTimeHelper
        ) {
        }

        /**
         * getStatusLabels - Gets all status labels for the current set project
         *
         * @access public
         * @param null $projectId
         * @return array
         */
        public function getStatusLabels($projectId = null): array
        {

            return $this->ticketRepository->getStateLabels($projectId);
        }

        /**
         * getAllStatusLabelsByUserId - Gets all the status labels a specific user might encounter and groups them by project.
         * Used to get all the status dropdowns for user home dashboards
         *
         * @access public
         * @params int $userId User Id
         * @param $userId
         * @return array
         */
        public function getAllStatusLabelsByUserId($userId): array
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
            $allTickets = $this->ticketRepository->getAllBySearchCriteria(array("currentProject" => "", "users" => $userId, "status" => "not_done", "sprint" => ""), "duedate");

            foreach ($allTickets as $row) {
                if (!isset($statusLabelsByProject[$row['projectId']])) {
                    $statusLabelsByProject[$row['projectId']] = $this->ticketRepository->getStateLabels($row['projectId']);
                }
            }

            return $statusLabelsByProject;
        }

        /**
         * saveStatusLabels - Saves the description/label of a status
         *
         * @access public
         * @params array $params label information
         * @param $params
         * @return bool
         */
        public function saveStatusLabels($params): bool
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
                        "sortKey" => $params['labelSort-' . $labelKey] ?? 99,
                    );
                }

                unset($_SESSION["projectsettings"]["ticketlabels"]);

                return $this->settingsRepo->saveSetting("projectsettings." . $_SESSION['currentProject'] . ".ticketlabels", serialize($statusArray));
            } else {
                return false;
            }
        }

        /**
         * @return array
         */
        public function getKanbanColumns(): array
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

        /**
         * @return array|string[]
         */
        public function getTypeIcons(): array
        {

            return $this->ticketRepository->typeIcons;
        }

        /**
         * @return array|string[]
         */
        public function getEffortLabels(): array
        {

            return $this->ticketRepository->efforts;
        }

        /**
         * @return array|string[]
         */
        public function getTicketTypes(): array
        {

            return $this->ticketRepository->type;
        }

        /**
         * @return array|string[]
         */
        public function getPriorityLabels(): array
        {

            return $this->ticketRepository->priority;
        }


        /**
         * @param array $searchParams
         * @return array
         */
        public function prepareTicketSearchArray(array $searchParams): array
        {

            $searchCriteria = array(
                "currentProject" => $_SESSION["currentProject"] ?? '',
                "currentUser" => $_SESSION['userdata']["id"] ?? '',
                "currentClient" => $_SESSION['userdata']["clientId"] ?? '',
                "sprint" => $_SESSION['currentSprint'] ?? '',
                "users" => "",
                "clients" => "",
                "status" => "",
                "term" => "",
                "effort" => "",
                "type" => "",
                "milestone" => "",
                "priority" => "",
                "orderBy" => "sortIndex",
                "orderDirection" => "DESC",
                "groupBy" => "",
            );

            if (isset($searchParams["currentProject"]) === true) {
                $searchCriteria["currentProject"] = $searchParams["currentProject"];
            }

            if (isset($searchParams["currentUser"]) === true) {
                $searchCriteria["currentUser"] = $searchParams["currentUser"];
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

            if (isset($searchParams["effort"]) === true) {
                $searchCriteria["effort"] = $searchParams["effort"];
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

            if (isset($searchParams["orderDirection"]) === true) {
                $searchCriteria["orderDirection"] = $searchParams["orderDirection"];
            }

            if (isset($searchParams["priority"]) === true) {
                $searchCriteria["priority"] = $searchParams["priority"];
            }

            if (isset($searchParams["clients"]) === true) {
                $searchCriteria["clients"] = $searchParams["clients"];
            }

            //The sprint selector is just a filter but remains in place across the session. Setting session here when it's selected
            if (isset($searchParams["sprint"]) === true) {
                $searchCriteria["sprint"] = $searchParams["sprint"];
                $_SESSION['currentSprint'] =  $searchCriteria["sprint"];
            }

            return $searchCriteria;
        }

        /**
         * @param array $searchCriteria
         * @return int
         */
        public function countSetFilters(array $searchCriteria): int
        {
            $count = 0;
            $setFilters = array();
            foreach ($searchCriteria as $key => $value) {
                if (
                    $key != "groupBy"
                    && $key != "currentProject"
                    && $key != "orderBy"
                    && $key != "currentUser"
                    && $key != "currentClient"
                    && $key != "sprint"
                    && $key != "orderDirection"
                ) {
                    if ($value != '') {
                        $count++;
                        $setFilters[$key] = $value;
                    }
                }
            }

            return $count;
        }

        /**
         * @param array $searchCriteria
         * @param bool  $includeGroup
         * @return array
         */
        public function getSetFilters(array $searchCriteria, bool $includeGroup = false): array
        {
            $setFilters = array();
            foreach ($searchCriteria as $key => $value) {
                if (
                    $key != "currentProject"
                    && $key != "orderBy"
                    && $key != "currentUser"
                    && $key != "clients"
                    && $key != "sprint"
                    && $key != "orderDirection"
                ) {
                    if ($includeGroup === true && $key == "groupBy" && $value != '') {
                        $setFilters[$key] = $value;
                    } elseif ($value != '') {
                        $setFilters[$key] = $value;
                    }
                }
            }

            return $setFilters;
        }

        /**
         * @param $searchCriteria
         * @return array|bool
         */
        public function getAll(?array $searchCriteria = null): array|false
        {
            return $this->ticketRepository->getAllBySearchCriteria(
                $searchCriteria ?? [],
                $searchCriteria['orderBy'] ?? 'date'
            );
        }

        public function simpleTicketCounter(?int $userId = null, ?int $project = null, string $status = ""): int {

            $tickets = $this->ticketRepository->simpleTicketQuery($userId, $project);


            if($status != '' && is_array($tickets)) {

                $ticketCounter = 0;
                $projectStatusLabels = [];
                foreach($tickets as $ticket) {

                    if(!isset($projectStatusLabels[$ticket['projectId']])) {
                        $projectStatusLabels[$ticket['projectId']] = $this->ticketRepository->getStateLabels($ticket['projectId']);
                    }

                    if($status == "not_done" &&
                        (
                            !isset($projectStatusLabels[$ticket['projectId']][$ticket['status']]) ||
                            $projectStatusLabels[$ticket['projectId']][$ticket['status']]["statusType"] !== "DONE"
                        )
                    ){
                            $ticketCounter++;
                            continue;
                    }

                    if(
                        isset($projectStatusLabels[$ticket['projectId']][$ticket['status']]["statusType"]) && $projectStatusLabels[$ticket['projectId']][$ticket['status']]["statusType"] == $status){
                        $ticketCounter++;
                    }
                }

                return $ticketCounter;
            }

            if(is_array($tickets)) return count($tickets);

            return 0;

        }

        public function getAllOpenUserTickets(?int $userId = null, ?int $project = null): array {

            $tickets = $this->ticketRepository->simpleTicketQuery($userId, $project);

            $ticketArray = [];

            if(is_array($tickets)) {

                $ticketCounter = 0;
                $projectStatusLabels = [];
                foreach($tickets as $ticket) {

                    if(!isset($projectStatusLabels[$ticket['projectId']])) {
                        $projectStatusLabels[$ticket['projectId']] = $this->ticketRepository->getStateLabels($ticket['projectId']);
                    }

                    if($projectStatusLabels[$ticket['projectId']][$ticket['status']]["statusType"] !== "DONE"){
                        $ticketArray[] = $ticket;

                    }

                }

            }

            return $ticketArray;

        }

        public function getScheduledTasks(DateTime $dateFrom, DateTime $dateTo, ?int $userId)
        {

            $totalTasks = $this->ticketRepository->getScheduledTasks($dateFrom, $dateTo, $userId);

            $statusLabels = [];
            $doneTasks = [];

            foreach ($totalTasks as $ticket) {
                if (!isset($statusLabels[$ticket['projectId']])) {
                    $statusLabels[$ticket['projectId']] = $this->ticketRepository->getStateLabels($ticket['projectId']);
                }

                if (isset($statusLabels[$ticket['projectId']][$ticket['status']]) && $statusLabels[$ticket['projectId']][$ticket['status']]['statusType'] == "DONE") {
                    $doneTasks[] = $ticket;
                }
            }

            return array("totalTasks" => $totalTasks, "doneTasks" => $doneTasks);
        }


        /**
         * @param $searchCriteria
         * @return array
         * @throws BindingResolutionException
         */
        public function getAllGrouped($searchCriteria): array
        {
            $ticketGroups = array();

            $tickets = $this->ticketRepository->getAllBySearchCriteria(
                $searchCriteria,
                $searchCriteria['orderBy'] ?? 'date'
            );

            if (
                $searchCriteria['groupBy'] == null
                || $searchCriteria['groupBy'] == ''
                || $searchCriteria['groupBy'] == 'all'
            ) {
                $ticketGroups['all'] = array(
                    "label" => "all",
                    "id" => 'all',
                    'class' => '',
                    'items' => $tickets,
                );

                return $ticketGroups;
            }

            $groupByOptions = $this->getGroupByFieldOptions();


            foreach ($tickets as $ticket) {
                $class = '';

                if (isset($ticket[$searchCriteria['groupBy']])) {
                    $groupedFieldValue = strtolower($ticket[$searchCriteria['groupBy']]);

                    if (isset($ticketGroups[$groupedFieldValue])) {
                        $ticketGroups[$groupedFieldValue]['items'][] = $ticket;
                    } else {
                        switch ($searchCriteria['groupBy']) {
                            case "status":
                                $status = $this->getStatusLabels();

                                if(isset($status[$groupedFieldValue])) {
                                    $label = $status[$groupedFieldValue]["name"];
                                    $class = $status[$groupedFieldValue]["class"];
                                }else{
                                    $label = "New";
                                }

                                break;
                            case "priority":
                                $priorities = $this->getPriorityLabels();
                                if (isset($priorities[$groupedFieldValue])) {
                                    $label = $priorities[$groupedFieldValue];
                                    $class = "priority-text-" . $groupedFieldValue;
                                } else {
                                    $label = "No Priority Set";
                                }
                                break;
                            case "storypoints":
                                $efforts  =  $this->getEffortLabels();
                                $label = $efforts[$groupedFieldValue] ?? "No Effort Set";
                                break;
                            case "milestoneid":
                                $label = "No Milestone Set";
                                if ($ticket["milestoneid"] > 0) {
                                    $milestone = $this->getTicket($ticket["milestoneid"]);
                                    $color = $milestone->tags;
                                    $class = '" style="color:' . $color . '"';

                                    $label = $ticket["milestoneHeadline"] . " <a href='#/tickets/editMilestone/" . $ticket["milestoneid"] . "' style='float:right;'><i class='fa fa-edit'></i></a><a>";
                                }

                                break;
                            case "editorId":
                                $label = "<div class='profileImage'><img src='" . BASE_URL . "/api/users?profileImage=" . $ticket["editorId"] . "' /></div> " . $ticket["editorFirstname"] . " " . $ticket["editorLastname"];

                                if ($ticket["editorFirstname"] == '' && $ticket["editorLastname"] == '') {
                                    $label = "Not Assigned to Anyone";
                                }

                                break;
                            case "sprint":
                                $label = $ticket["sprintName"];
                                if ($label == '') {
                                    $label = "Not assigned to a sprint";
                                }
                                break;
                            case "type":
                                $icon = $this->getTypeIcons();
                                $label = "<i class='fa " . ($icon[strtolower($ticket["type"])] ?? "") . "'></i>" . $ticket["type"];
                                break;
                            default:
                                $label = $groupedFieldValue;
                                break;
                        }

                        $ticketGroups[$groupedFieldValue] = array(
                            "label" => $label,
                            "id" => strtolower($groupedFieldValue),
                            "class" => $class,
                            'items' => [$ticket],
                        );
                    }
                }
            }

            //Sort main groups

            switch ($searchCriteria['groupBy']) {
                case "status":
                case "priority":
                case "storypoints":
                    $ticketGroups = array_sort($ticketGroups, 'id');
                // no break
                default:
                    $ticketGroups = array_sort($ticketGroups, 'label');
                    break;
            }

            return $ticketGroups;
        }

        /**
         * @param TicketModel $ticket
         * @param string      $projectId
         * @return array
         */
        public function getAllPossibleParents(TicketModel $ticket, string $projectId = 'currentProject'): array
        {

            if ($projectId == 'currentProject') {
                $projectId = $_SESSION['currentProject'];
            }

            $results = $this->ticketRepository->getAllPossibleParents($ticket, $projectId);

            if (is_array($results)) {
                return $results;
            } else {
                return array();
            }
        }

        /**
         * @param $id
         * @return bool|TicketModel
         * @throws BindingResolutionException
         */
        public function getTicket($id): TicketModel|bool
        {

            $ticket = $this->ticketRepository->getTicket($id);

            //Check if user is allowed to see ticket
            if ($ticket && $this->projectService->isUserAssignedToProject($_SESSION['userdata']['id'], $ticket->projectId)) {
                return $ticket;
            }

            return false;
        }

        /**
         * @param $userId
         * @param $projectId
         * @param false     $includeDoneTickets
         * @return array
         * @throws \Exception
         */
        public function getOpenUserTicketsThisWeekAndLater($userId, $projectId, bool $includeDoneTickets = false): array
        {

            if ($includeDoneTickets === true) {
                $searchStatus = "all";
            } else {
                $searchStatus = "not_done";
            }
            $searchCriteria = $this->prepareTicketSearchArray(array("currentProject" => $projectId, "currentUser" => $userId, "users" => $userId, "status" => $searchStatus, "sprint" => ""));
            $allTickets = $this->ticketRepository->getAllBySearchCriteria($searchCriteria, "duedate");

            $statusLabels = $this->getAllStatusLabelsByUserId($userId);

            $tickets = array();

            foreach ($allTickets as $row) {
                //There is a non zero chance that a user has tasks assigned to them while not being part of the project
                //Need to get those status labels as well
                if (!isset($statusLabels[$row['projectId']])) {
                    $statusLabels[$row['projectId']] = $this->ticketRepository->getStateLabels($row['projectId']);
                }

                //There is a chance that the status was removed after it was assigned to a ticket
                if (isset($statusLabels[$row['projectId']][$row['status']]) && ($statusLabels[$row['projectId']][$row['status']]['statusType'] != "DONE" || $includeDoneTickets === true)) {
                    if ($row['dateToFinish'] == "0000-00-00 00:00:00" || $row['dateToFinish'] == "1969-12-31 00:00:00" || $row['dateToFinish'] == null) {
                        if (isset($tickets["later"]["tickets"])) {
                            $tickets["later"]["tickets"][] = $row;
                        } else {
                            $tickets['later'] = array(
                                "labelName" => "subtitles.due_later",
                                "groupValue" => "",
                                "tickets" => array($row),
                                "order" => 3,
                            );
                        }
                    } else {
                        $dtHelper = new DateTimeHelper();

                        $today = $dtHelper->userNow()->setToDbTimezone();
                        $dbDueDate = $dtHelper->parseDbDateTime($row['dateToFinish']);
                        $nextFriday = $dtHelper->userNow()->endOfWeek(CarbonInterface::FRIDAY)->setToDbTimezone();


                        if ($dbDueDate <= $nextFriday && $dbDueDate >= $today) {
                            if (isset($tickets["thisWeek"]["tickets"])) {
                                $tickets["thisWeek"]["tickets"][] = $row;
                            } else {
                                $tickets['thisWeek'] = array(
                                    "labelName" => "subtitles.due_this_week",
                                    "tickets" => array($row),
                                    "groupValue" => $dbDueDate,
                                    "order" => 2,
                                );
                            }
                        } else if ($dbDueDate <= $today) {
                            if (isset($tickets["overdue"]["tickets"])) {
                                $tickets["overdue"]["tickets"][] = $row;
                            } else {


                                $tickets['overdue'] = array(
                                    "labelName" => "subtitles.overdue",
                                    "tickets" => array($row),
                                    "groupValue" => $dbDueDate,
                                    "order" => 1,
                                );
                            }
                        } else {
                            if (isset($tickets["later"]["tickets"])) {
                                $tickets["later"]["tickets"][] = $row;
                            } else {
                                $tickets['later'] = array(
                                    "labelName" => "subtitles.due_later",
                                    "tickets" => array($row),
                                    "groupValue" => "",
                                    "order" => 3,
                                );
                            }
                        }
                    }
                }
            }

            //$ticketsSorted = array_sort($tickets, 'order');

            return $tickets;
        }

        /**
         * @param $projectId
         * @param int       $limit
         * @return array|bool
         */
        public function getLastTickets($projectId, int $limit = 5): bool|array
        {

            $searchCriteria = $this->prepareTicketSearchArray(array("currentProject" => $projectId, "users" => "", "status" => "not_done", "sprint" => "", "limit" => $limit));
            $allTickets = $this->ticketRepository->getAllBySearchCriteria($searchCriteria, "date", $limit);

            return $allTickets;
        }

        /**
         * @param $userId
         * @param $projectId
         * @return array
         */
        public function getOpenUserTicketsByProject($userId, $projectId): array
        {

            $searchCriteria = $this->prepareTicketSearchArray(array("currentProject" => $projectId, "users" => $userId, "status" => "", "sprint" => ""));
            $allTickets = $this->ticketRepository->getAllBySearchCriteria($searchCriteria, "duedate");

            $statusLabels = $this->getAllStatusLabelsByUserId($userId);

            $tickets = array();

            foreach ($allTickets as $row) {

                //Only include todos that are not done
                if (isset( $statusLabels[$row['projectId']]) &&
                    isset( $statusLabels[$row['projectId']][$row['status']]) &&
                    $statusLabels[$row['projectId']][$row['status']]['statusType'] != "DONE") {

                    if (isset($tickets[$row['projectId']])) {
                        $tickets[$row['projectId']]['tickets'][] = $row;
                    } else {
                        $tickets[$row['projectId']] = array(
                            "labelName" => $row['clientName'] . " / " . $row['projectName'],
                            "tickets" => array($row),
                            "groupValue" => $row['projectId']
                        );
                    }
                }
            }

            return $tickets;
        }


        /**
         * @param $userId
         * @param $projectId
         * @return array
         */
        public function getOpenUserTicketsBySprint($userId, $projectId): array
        {

            $searchCriteria = $this->prepareTicketSearchArray(array("currentProject" => $projectId, "users" => $userId, "status" => "", "sprint" => ""));
            $allTickets = $this->ticketRepository->getAllBySearchCriteria($searchCriteria, "duedate");

            $statusLabels = $this->getAllStatusLabelsByUserId($userId);

            $tickets = array();

            foreach ($allTickets as $row) {

                $sprint = $row['sprint'] ?? "backlog";
                $sprintName =  empty($row['sprintName']) ? $this->language->__("label.not_assigned_to_list") : $row['sprintName'];

                //Only include todos that are not done
                if (isset($statusLabels[$row['projectId'] ?? '']) &&
                    isset( $statusLabels[$row['projectId']][$row['status']]) &&
                    $statusLabels[$row['projectId']][$row['status']]['statusType'] != "DONE") {

                    if (isset($tickets[$sprint])) {
                        $tickets[$sprint]['tickets'][] = $row;
                    } else {
                        $tickets[$sprint] = array(
                            "labelName" => $row['projectName'] . " / " . $sprintName,
                            "tickets" => array($row),
                            "groupValue" => $row['sprint'] ."-".$row['projectId']
                        );
                    }
                }
            }

            return $tickets;
        }

        /**
         * @param $searchCriteria
         * @param string         $sortBy
         * @return array|false
         */
        public function getAllMilestones($searchCriteria, string $sortBy = "duedate"): false|array
        {

            if (is_array($searchCriteria) && $searchCriteria['currentProject'] > 0) {
                return $this->ticketRepository->getAllMilestones($searchCriteria, $sortBy);
            }

            return false;
        }

        /**
         * @param bool   $includeArchived
         * @param string $sortBy
         * @param bool   $includeTasks
         * @param int    $clientId
         * @return array|false
         */
        public function getAllMilestonesOverview(bool $includeArchived = false, string $sortBy = "duedate", bool $includeTasks = false, int $clientId = 0): false|array
        {

            $allProjectMilestones = $this->ticketRepository->getAllMilestones(["sprint" => '', "type" => "milestone", "clients" => $clientId]);
            return $allProjectMilestones;
        }

        /**
         * @param $userId
         * @return array
         */
        public function getAllMilestonesByUserProjects($userId): array
        {

            $milestones = array();

            $userProjects = $this->projectService->getProjectsAssignedToUser($userId);
            if ($userProjects) {
                foreach ($userProjects as $project) {
                    $allProjectMilestones = $this->getAllMilestones(["sprint" => '', "type" => "milestone", "currentProject" => $project['id']]);
                    $milestones[$project['id']] = $allProjectMilestones;
                }
            }

            if (isset($_SESSION['currentProject'])) {
                $allProjectMilestones = $this->getAllMilestones(["sprint" => '', "type" => "milestone", "currentProject" => $_SESSION["currentProject"]]);
                $milestones[$_SESSION['currentProject']] = $allProjectMilestones;
            }

            //There is a non zero chance that a user has tickets assigned to them without a project assignment.
            //Checking user assigned tickets to see if there are missing projects.
            $allTickets = $this->ticketRepository->getAllBySearchCriteria(array("currentProject" => "", "users" => $userId, "status" => "not_done", "sprint" => ""), "duedate");

            foreach ($allTickets as $row) {
                if (!isset($milestones[$row['projectId']])) {
                    $allProjectMilestones = $this->getAllMilestones(["sprint" => '', "type" => "milestone", "currentProject" => $_SESSION["currentProject"]]);

                    $milestones[$row['projectId']] = $allProjectMilestones;
                }
            }

            return $milestones;
        }

        public function getRecentlyCompletedTicketsByUser(int $userId, ?int $projectId = null): array
        {

            //Get status labels
            $statusLabelsByProject = array();

            if ($projectId === null) {
                $userProjects = $this->projectService->getProjectsAssignedToUser($userId);

                if ($userProjects) {
                    foreach ($userProjects as $project) {
                        $statusLabelsByProject[$project['id']] = $this->ticketRepository->getStateLabels($project['id']);
                    }
                }
            } else {
                $statusLabelsByProject[$projectId] = $this->ticketRepository->getStateLabels($projectId);
            }

            //Get tickets recently set to done (history table)

            $searchCriteria = $this->prepareTicketSearchArray(array("currentProject" => '', "users" => $userId, "status" => "done", "sprint" => "", "limit" => null));
            $myCompletedTasks = $this->getAll($searchCriteria);
            $dateTime = new DateTime();
            $dateTime->modify('-1 week');

            $doneTasks = [];
            foreach ($myCompletedTasks as $ticket) {
                $history = $this->ticketHistoryRepo->getRecentTicketHistory($dateTime, $ticket['id']);

                foreach ($history as $activity) {
                    if (
                        $activity['changeType'] == "status"
                        && isset($statusLabelsByProject[$ticket['projectId']][$activity['changeValue']])
                        && $statusLabelsByProject[$ticket['projectId']][$activity['changeValue']]['statusType'] == "DONE"
                    ) {
                        $doneTasks[] = $ticket;
                    }
                }
            }

            return $doneTasks;
        }

        public function goalsRelatedToWork(int $userId, $projectId = null)
        {

            $statusLabelsByProject = array();

            if ($projectId === null) {
                $userProjects = $this->projectService->getProjectsAssignedToUser($userId);

                if ($userProjects) {
                    foreach ($userProjects as $project) {
                        $statusLabelsByProject[$project['id']] = $this->ticketRepository->getStateLabels($project['id']);
                    }
                }
            } else {
                $statusLabelsByProject[$projectId] = $this->ticketRepository->getStateLabels($projectId);
            }

            //Get tickets recently set to done (history table)

            $searchCriteria = $this->prepareTicketSearchArray(array("currentProject" => '', "users" => $userId, "status" => "not_done", "sprint" => "", "limit" => null));
            $myTask = $this->getAll($searchCriteria);

            $contributedToGoal = [];

            foreach ($myTask as $task) {
                if ($task['milestoneid'] !== '' && $task['milestoneid'] > 0) {
                    $goals = $this->goalcanvasService->getGoalsByMilestone($task['milestoneid']);
                    foreach ($goals as $goal) {
                        if (!isset($contributedToGoal[$goal['id']])) {
                            $contributedToGoal[$goal['id']] = $goal;
                        }
                    }
                }
            }

            return $contributedToGoal;
        }



        /**
         * @param int $ticketId
         * @return array|false
         */
        public function getAllSubtasks(int $ticketId): false|array
        {

            //TODO: Refactor to be recursive
            $values = $this->ticketRepository->getAllSubtasks($ticketId);
            return $values;
        }


        /**
         * @param $params
         * @return bool|string[]
         * @throws BindingResolutionException
         */
        public function quickAddTicket($params): array|bool
        {

            $values = array(
                'headline' => $params['headline'],
                'type' => 'Task',
                'description' => $params['description'] ?? '',
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
                'editFrom' => $params['editFrom'] ?? '',
                'editTo' => $params['editTo'] ?? '',
                'milestoneid' => isset($params['milestone']) ? (int) $params['milestone'] : "",
                'dependingTicketId' => '',
            );

            if ($values['headline'] == "") {
                $error = array("status" => "error", "message" => "Headline Missing");
                return $error;
            }

            $values = $this->prepareTicketDates($values);

            $result = $this->ticketRepository->addTicket($values);

            if ($result > 0) {
                $values['id'] = $result;
                $actual_link = BASE_URL . "/dashboard/home#/tickets/showTicket/" . $result;
                $message = sprintf($this->language->__("email_notifications.new_todo_message"), $_SESSION["userdata"]["name"], $params['headline']);
                $subject = $this->language->__("email_notifications.new_todo_subject");

                $notification = app()->make(NotificationModel::class);
                $notification->url = array(
                    "url" => $actual_link,
                    "text" => $this->language->__("email_notifications.new_todo_cta"),
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

        /**
         * @param $params
         * @return array|bool|int
         */
        public function quickAddMilestone($params): array|bool|int
        {

            $values = array(
                'headline' => $params['headline'],
                'type' => 'milestone',
                'description' => '',
                'projectId' => $params['projectId'] ?? $_SESSION['currentProject'],
                'editorId' =>  $params['editorId'] ?? $_SESSION['userdata']['id'],
                'userId' => $_SESSION['userdata']['id'],
                'date' => $this->dateTimeHelper->userNow()->formatDateTimeForDb(),
                'dateToFinish' => "",
                'status' => 3,
                'storypoints' => '',
                'hourRemaining' => '',
                'planHours' => '',
                'sprint' => '',
                'priority' => 3,
                'dependingTicketId' => '',
                'milestoneid' => $params['dependentMilestone'] ?? '',
                'acceptanceCriteria' => '',
                'tags' => $params['tags'],
                'editFrom' => $params['editFrom'] ?? '',
                'editTo' => $params['editTo'] ?? ''
            );

            $values = $this->prepareTicketDates($values);

            if ($values['headline'] == "") {
                $error = array("status" => "error", "message" => "Headline Missing");
                return $error;
            }

            //$params is an array of field names. Exclude id
            return $this->ticketRepository->addTicket($values);
        }

        /**
         * @param $values
         * @return bool|string[]|void
         * @throws BindingResolutionException
         */
        public function addTicket($values)
        {
            $values = array(
                'id' => '',
                'headline' => $values['headline'] ?? "",
                'type' => $values['type'] ?? "Task",
                'description' => $values['description'] ?? "",
                'projectId' => $values['projectId'] ?? $_SESSION['currentProject'] ,
                'editorId' => $values['editorId'] ?? "",
                'userId' => $_SESSION['userdata']['id'],
                'date' => gmdate("Y-m-d H:i:s"),
                'dateToFinish' => $values['dateToFinish'] ?? "",
                'timeToFinish' => $values['timeToFinish'] ?? "",
                'status' => (int) $values['status'] ?? 3,
                'planHours' => $values['planHours'] ?? "",
                'tags' => $values['tags'] ?? "",
                'sprint' => $values['sprint'] ?? "",
                'storypoints' => $values['storypoints'] ?? "",
                'hourRemaining' => $values['hourRemaining'] ?? "",
                'priority' => $values['priority'] ?? "",
                'acceptanceCriteria' => $values['acceptanceCriteria'] ?? "",
                'editFrom' => $values['editFrom'] ?? '',
                'timeFrom' => $values['timeFrom'] ?? "",
                'editTo' => $values['editTo'] ?? "",
                'timeTo' => $values['timeTo'] ?? "",
                'dependingTicketId' => $values['dependingTicketId'] ?? "",
                'milestoneid' => $values['milestoneid'] ?? "",
            );

            if (!$this->projectService->isUserAssignedToProject($_SESSION['userdata']['id'], $values['projectId'])) {
                return array("msg" => "notifications.ticket_save_error_no_access", "type" => "error");
            }

            if ($values['headline'] === '') {
                return array("msg" => "notifications.ticket_save_error_no_headline", "type" => "error");
            } else {

                $values = $this->prepareTicketDates($values);

                //Update Ticket
                $addTicketResponse = $this->ticketRepository->addTicket($values);
                if ($addTicketResponse !== false) {
                    $values["id"] = $addTicketResponse;
                    $subject = sprintf($this->language->__("email_notifications.new_todo_subject"), $addTicketResponse, $values['headline']);
                    $actual_link = BASE_URL . "/dashboard/home#/tickets/showTicket/" . $addTicketResponse;
                    $message = sprintf($this->language->__("email_notifications.new_todo_message"), $_SESSION['userdata']['name'], $values['headline']);

                    $notification = app()->make(NotificationModel::class);
                    $notification->url = array(
                        "url" => $actual_link,
                        "text" => $this->language->__("email_notifications.new_todo_cta"),
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

        /**
         * Updates a ticket with the given values.
         *
         * @param array $values The array containing the ticket values to update.
         *                      Accepted keys are:
         *                      - 'id' => The ticket ID.
         *                      - 'headline' => The ticket headline. (optional)
         *                      - 'type' => The ticket type. (optional)
         *                      - 'description' => The ticket description. (optional)
         *                      - 'projectId' => The project ID. Defaults to $_SESSION['currentProject']. (optional)
         *                      - 'editorId' => The editor ID. (optional)
         *                      - 'date' => The ticket date. Defaults to the current date and time. (optional)
         *                      - 'dateToFinish' => The ticket deadline date. (optional)
         *                      - 'timeToFinish' => The ticket deadline time. (optional)
         *                      - 'status' => The ticket status. (optional)
         *                      - 'planHours' => The planned hours for the ticket. (optional)
         *                      - 'tags' => The tags for the ticket. (optional)
         *                      - 'sprint' => The sprint for the ticket. (optional)
         *                      - 'storypoints' => The story points for the ticket. (optional)
         *                      - 'hourRemaining' => The remaining hours for the ticket. (optional)
         *                      - 'priority' => The ticket priority. (optional)
         *                      - 'acceptanceCriteria' => The ticket acceptance criteria. (optional)
         *                      - 'editFrom' => The ticket edit 'from' date-time. (optional)
         *                      - 'time*/
        public function updateTicket($values): array|bool
        {

            $values = array(
                'id' => $values['id'],
                'headline' => $values['headline'] ?? "",
                'type' => $values['type'] ?? "",
                'description' => $values['description'] ?? "",
                'projectId' => $values['projectId'] ?? $_SESSION['currentProject'],
                'editorId' => $values['editorId'] ?? "",
                'date' =>  $this->dateTimeHelper->userNow()->formatDateTimeForDb(),
                'dateToFinish' => $values['dateToFinish'] ?? "",
                'timeToFinish' => $values['timeToFinish'] ?? "",
                'status' => $values['status'] ?? "",
                'planHours' => $values['planHours'] ?? "",
                'tags' => $values['tags'] ?? "",
                'sprint' => $values['sprint'] ?? "",
                'storypoints' => $values['storypoints'] ?? "",
                'hourRemaining' => $values['hourRemaining'] ?? "",
                'priority' => $values['priority'] ?? "",
                'acceptanceCriteria' => $values['acceptanceCriteria'] ?? "",
                'editFrom' => $values['editFrom'] ?? "",
                'timeFrom' => $values['timeFrom'] ?? "",
                'editTo' => $values['editTo'] ?? "",
                'timeTo' => $values['timeTo'] ?? "",
                'dependingTicketId' => $values['dependingTicketId'] ?? "",
                'milestoneid' => $values['milestoneid'] ?? "",
            );

            if (!$this->projectService->isUserAssignedToProject($_SESSION['userdata']['id'], $values['projectId'])) {
                return array("msg" => "notifications.ticket_save_error_no_access", "type" => "error");
            }

            if ($values['headline'] === '') {
                return array("msg" => "notifications.ticket_save_error_no_headline", "type" => "error");
            } else {

                $values = $this->prepareTicketDates($values);

                //Update Ticket
                if ($this->ticketRepository->updateTicket($values, $values['id']) === true) {
                    $subject = sprintf($this->language->__("email_notifications.todo_update_subject"), $values['id'], $values['headline']);
                    $actual_link = BASE_URL . "/dashboard/home#/tickets/showTicket/" . $values['id'];
                    $message = sprintf($this->language->__("email_notifications.todo_update_message"), $_SESSION['userdata']['name'], $values['headline']);

                    $notification = app()->make(NotificationModel::class);
                    $notification->url = array(
                        "url" => $actual_link,
                        "text" => $this->language->__("email_notifications.todo_update_cta"),
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



            return false;
        }

        /**
         * @param $id
         * @param $params
         * @return bool
         */
        public function patch($id, $params): bool
        {

            //$params is an array of field names. Exclude id
            unset($params["id"]);

            $params = $this->prepareTicketDates($params);

            $return = $this->ticketRepository->patchTicket($id, $params);

            //Todo: create events and move notification logic to notification module
            if(isset($params['status']) && $return) {

                $ticket = $this->getTicket($id);
                $subject = sprintf($this->language->__("email_notifications.todo_update_subject"), $id, $ticket->headline);
                $actual_link = BASE_URL . "/dashboard/home#/tickets/showTicket/" . $id;
                $message = sprintf($this->language->__("email_notifications.todo_update_message"), $_SESSION['userdata']['name'], $ticket->headline);

                $notification = app()->make(NotificationModel::class);
                $notification->url = array(
                    "url" => $actual_link,
                    "text" => $this->language->__("email_notifications.todo_update_cta"),
                );
                $notification->entity = $ticket;
                $notification->module = "tickets";
                $notification->projectId = $_SESSION['currentProject'];
                $notification->subject = $subject;
                $notification->authorId = $_SESSION['userdata']['id'];
                $notification->message = $message;

                $this->projectService->notifyProjectUsers($notification);

            }

            return $return;
        }



        /**
         * moveTicket - Moves a ticket from one project to another. Milestone children will be moved as well
         *
         * @param int $id
         * @param int $projectId
         * @return bool
         * @throws BindingResolutionException
         */
        public function moveTicket(int $id, int $projectId): bool
        {

            $ticket = $this->getTicket($id);

            if ($ticket) {
                //If milestone, move child todos
                if ($ticket->type == "milestone") {
                    $milestoneTickets = $this->getAll(array("milestone" => $ticket->id));
                    //Update child todos
                    foreach ($milestoneTickets as $childTicket) {
                        $this->patch($childTicket["id"], ["projectId" => $projectId, "sprint" => ""]);
                    }
                }

                //Update ticket
                return $this->patch($ticket->id, ["projectId" => $projectId, "sprint" => "", "dependingTicketId" => "", 'milestoneid' => '']);
            }

            return false;
        }


        /**
         * @param $params
         * @return bool|string[]
         */
        public function quickUpdateMilestone($params): array|bool
        {

            $values = array(
                'headline' => $params['headline'],
                'type' => 'milestone',
                'description' => '',
                'projectId' => $_SESSION['currentProject'],
                'editorId' => $params['editorId'],
                'userId' => $_SESSION['userdata']['id'],
                'date' => $this->dateTimeHelper->userNow()->formatDateTimeForDb(),
                'dateToFinish' => "",
                'status' => $params['status'],
                'storypoints' => '',
                'hourRemaining' => '',
                'planHours' => '',
                'sprint' => '',
                'acceptanceCriteria' => '',
                'priority' => 3,
                'dependingTicketId' => '',
                'milestoneid' => $params['dependentMilestone'],
                'tags' => $params['tags'],
                'editFrom' => $params['editFrom'] ?? '',
                'editTo' => $params['editTo'] ?? ''
            );

            if ($values['headline'] == "") {
                $error = array("status" => "error", "message" => "Headline Missing");
                return $error;
            }

            $values = $this->prepareTicketDates($values);

            //$params is an array of field names. Exclude id
            return $this->ticketRepository->updateTicket($values, $params["id"]);
        }

        /**
         * @param $values
         * @param $parentTicket
         * @return bool
         */
        public function upsertSubtask($values, $parentTicket): bool
        {

            $subtaskId = $values['subtaskId'] ?? 'new';

            $values = array(
                'headline' => $values['headline'],
                'type' => 'subtask',
                'description' => $values['description'] ?? '',
                'projectId' => $parentTicket->projectId,
                'editorId' => $_SESSION['userdata']['id'],
                'userId' => $_SESSION['userdata']['id'],
                'date' =>  $this->dateTimeHelper->userNow()->formatDateTimeForDb(),
                'dateToFinish' => $values['dateToFinish'] ?? '',
                'priority' => $values['priority'] ?? 3,
                'status' => $values['status'],
                'storypoints' => $values['storypoints'] ?? '',
                'hourRemaining' => $values['hourRemaining'] ?? 0,
                'planHours' => $values['planHours'] ?? 0,
                'sprint' => "",
                'acceptanceCriteria' => "",
                'tags' => "",
                'editFrom' => $values['editFrom'] ?? '',
                'editTo' => $values['editTo'] ?? '',
                'dependingTicketId' => $parentTicket->id,
                'milestoneid' => $parentTicket->milestoneid,
            );

            $values = $this->prepareTicketDates($values);

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


        /**
         * @param $params
         * @return false|void
         */
        public function updateTicketSorting($params)
        {

            //ticketId: sortIndex
            foreach ($params as $id => $sortKey) {
                if ($this->ticketRepository->patchTicket($id, ["sortIndex" => $sortKey]) === false) {
                    return false;
                }
            }

            return true;
        }

        /**
         * @param $params
         * @param $handler
         * @return bool
         * @throws BindingResolutionException
         */
        public function updateTicketStatusAndSorting($params, $handler = null): bool
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

                            if ($this->ticketRepository->updateTicketStatus($id, $status, ($key * 100), $handler) === false) {
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
                    $actual_link = BASE_URL . "/dashboard/home#/tickets/showTicket/" . $id;
                    $message = sprintf($this->language->__("email_notifications.todo_update_message"), $_SESSION['userdata']['name'], $ticket->headline);

                    $notification = app()->make(NotificationModel::class);
                    $notification->url = array(
                        "url" => $actual_link,
                        "text" => $this->language->__("email_notifications.todo_update_cta"),
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
        /**
         * @param $id
         * @return bool|string[]
         * @throws BindingResolutionException
         */
        public function delete($id): array|bool
        {

            $ticket = $this->getTicket($id);

            if (!$ticket || !$this->projectService->isUserAssignedToProject($_SESSION['userdata']['id'], $ticket->projectId)) {
                return array("msg" => "notifications.ticket_delete_error", "type" => "error");
            }

            if ($this->ticketRepository->delticket($id)) {
                return true;
            }

            return false;
        }

        /**
         * @param $id
         * @return bool|string[]
         * @throws BindingResolutionException
         */
        public function deleteMilestone($id): array|bool
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

        /**
         * @return mixed|string
         */
        public function getLastTicketViewUrl(): mixed
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

        public function getLastTimelineViewUrl(): mixed
        {

            $url = BASE_URL . "/tickets/roadmap";

            if (isset($_SESSION['lastMilestoneView']) && $_SESSION['lastMilestoneView'] != "") {
                if ($_SESSION['lastMilestoneView'] == "table" && isset($_SESSION['lastFilterdMilestoneTableView']) && $_SESSION['lastFilterdMilestoneTableView'] != "") {
                    return $_SESSION['lastFilterdMilestoneTableView'];
                }

                if ($_SESSION['lastMilestoneView'] == "roadmap" && isset($_SESSION['lastFilterdTicketRoadmapView']) && $_SESSION['lastFilterdTicketRoadmapView'] != "") {
                    return $_SESSION['lastFilterdTicketRoadmapView'];
                }

                if ($_SESSION['lastMilestoneView'] == "calendar" && isset($_SESSION['lastFilterdTicketCalendarView']) && $_SESSION['lastFilterdTicketCalendarView'] != "") {
                    return $_SESSION['lastFilterdTicketCalendarView'];
                }

                return $url;
            } else {
                return $url;
            }
        }

        /**
         * @return array
         */
        public function getGroupByFieldOptions(): array
        {
            return [
                "all" => [
                    'id' => 'all',
                    'field' => 'all',
                    'class' => '',
                    'label' => 'no_group',

                ],
                "type" => [
                    'id' => 'type',
                    'field' => 'type',
                    'label' => 'type',
                    'class' => '',
                    'function' => 'getTicketTypes',
                ],
                "status" => [
                    'id' => 'status',
                    'field' => 'status',
                    'label' => 'todo_status',
                    'class' => '',
                    'function' => 'getStatusLabels',
                ],
                "effort" => [
                    'id' => 'effort',
                    'field' => 'storypoints',
                    'label' => 'effort',
                    'class' => '',
                    'function' => 'getEffortLabels',
                ],
                "priority" => [
                    'id' => 'priority',
                    'field' => 'priority',
                    'label' => 'priority',
                    'class' => '',
                    'function' => 'getPriorityLabels',
                ],
                "milestone" => [
                    'id' => 'milestone',
                    'field' => 'milestoneid',
                    'label' => 'milestone',
                    'class' => '',
                    'function' => null,
                ],
                "user" => [
                    'id' => 'user',
                    'field' => 'editorId',
                    'label' => 'user',
                    'class' => '',
                    'funtion' => 'buildEditorName',
                ],
                "sprint" => [
                    'id' => 'sprint',
                    'field' => 'sprint',
                    'class' => '',
                    'label' => 'list',
                ],

                /*
                "tags" => [
                    'id' => 'groupByTagsLink',
                    'field' => 'tags',
                    'label' => 'tags',
                ],*/
            ];
        }

        /**
         * @return array[]
         */
        public function getSortByFieldOptions(): array
        {
            return [
                [
                    'id' => 'sortByManualLink',
                    'status' => 'manualSort',
                    'label' => 'manualSort',
                ],
                [
                    'id' => 'sortByTypeLink',
                    'status' => 'type',
                    'label' => 'type',
                ],
                [
                    'id' => 'sortByStatusLink',
                    'status' => 'status',
                    'label' => 'todo_status',

                ],
                [
                    'id' => 'sortByEffortLink',
                    'status' => 'effort',
                    'label' => 'effort',

                ],
                [
                    'id' => 'sortByPriorityLink',
                    'status' => 'priority',
                    'label' => 'priority',
                ],
                [
                    'id' => 'sortByMilestoneLink',
                    'status' => 'milestone',
                    'label' => 'milestone',
                ],
                [
                    'id' => 'sortByUserLink',
                    'status' => 'user',
                    'label' => 'user',
                ],
                [
                    'id' => 'sortBySprintLink',
                    'status' => 'sprint',
                    'label' => 'sprint',
                ],
                [
                    'id' => 'sortByTagsLink',
                    'status' => 'tags',
                    'label' => 'tags',
                ],
                [
                    'id' => 'sortByDueDateLink',
                    'status' => 'dateToFinish',
                    'label' => 'dueDate',
                ],
            ];
        }

        /**
         * @return array|array[]
         */
        public function getNewFieldOptions(): array
        {
            if (!defined('BASE_URL')) {
                return [];
            }

            return [
                [
                    'url' => "#/tickets/newTicket",
                    'text' => 'links.add_todo',
                    'class' => 'ticketModal',
                ],
                [
                    'url' => "#/tickets/editMilestone",
                    'text' => 'links.add_milestone',
                    'class' => 'milestoneModal',
                ]
            ];
        }

        /**
         * @param $params
         * @return array
         * @throws BindingResolutionException
         */
        public function getTicketTemplateAssignments($params): array
        {

            $currentSprint = $this->sprintService->getCurrentSprintId($_SESSION['currentProject']);

            $searchCriteria = $this->prepareTicketSearchArray($params);
            $searchCriteria["orderBy"] = "kanbansort";

            $allTickets = $this->getAllGrouped($searchCriteria);
            $allTicketStates  =  $this->getStatusLabels();

            $efforts  =  $this->getEffortLabels();
            $priorities  =  $this->getPriorityLabels();
            $types  =  $this->getTicketTypes();

            //Types are being used for filters. Add milestone as a type
            $types[] = "milestone";

            $ticketTypeIcons  =  $this->getTypeIcons();

            $numOfFilters  =  $this->countSetFilters($searchCriteria);

            $onTheClock  =  $this->timesheetService->isClocked($_SESSION["userdata"]["id"]);

            $sprints  =  $this->sprintService->getAllSprints($_SESSION["currentProject"]);
            $futureSprints  =  $this->sprintService->getAllFutureSprints($_SESSION["currentProject"]);

            $users  =  $this->projectService->getUsersAssignedToProject($_SESSION["currentProject"]);

            $milestones = $this->getAllMilestones([
                "sprint" => '',
                "type" => "milestone",
                "currentProject" => $_SESSION["currentProject"],
            ]);

            $groupByOptions  =  $this->getGroupByFieldOptions();
            $newField  =  $this->getNewFieldOptions();
            $sortOptions  =  $this->getSortByFieldOptions();

            $searchUrlString = "";
            if ($numOfFilters > 0 || $searchCriteria['groupBy'] != '') {
                $searchUrlString = "?" . http_build_query($this->getSetFilters($searchCriteria, true));
            }
            return array(
                'currentSprint' => $_SESSION['currentSprint'],
                'searchCriteria' => $searchCriteria,
                'allTickets' => $allTickets,
                'allTicketStates' =>  $allTicketStates,
                'efforts' => $efforts,
                'priorities' => $priorities,
                'types' => $types,
                'ticketTypeIcons' => $ticketTypeIcons,
                'numOfFilters' => $numOfFilters,
                'onTheClock' => $onTheClock,
                'sprints' => $sprints,
                'futureSprints' => $futureSprints,
                'users' => $users,
                'milestones' => $milestones,
                'groupByOptions' => $groupByOptions,
                'newField' => $newField,
                'sortOptions' => $sortOptions,
                'searchParams' => $searchUrlString,
            );
        }

        public function getToDoWidgetAssignments($params)
        {

            $projectFilter = "";
            if (isset($_SESSION['userHomeProjectFilter'])) {
                $projectFilter = $_SESSION['userHomeProjectFilter'];
            }

            if (isset($params['projectFilter'])) {
                $projectFilter = $params['projectFilter'];
                $_SESSION['userHomeProjectFilter'] = $projectFilter;
            }

            $groupBy = "";
            if (isset($_SESSION['userHomeGroupBy'])) {
                $groupBy = $_SESSION['userHomeGroupBy'];
            }

            if (isset($params['groupBy'])) {
                $groupBy = $params['groupBy'];
                $_SESSION['userHomeGroupBy'] = $groupBy;
            }

            if ($groupBy == "") {
                $groupBy = "time";
            }

            if ($groupBy == "time") {
                $tickets = $this->getOpenUserTicketsThisWeekAndLater($_SESSION["userdata"]["id"], $projectFilter);
            } elseif ($groupBy == "project") {
                $tickets = $this->getOpenUserTicketsByProject($_SESSION["userdata"]["id"], $projectFilter);
            } elseif ($groupBy == "sprint") {
                $tickets = $this->getOpenUserTicketsBySprint($_SESSION["userdata"]["id"], $projectFilter);
            }

            $onTheClock = $this->timesheetService->isClocked($_SESSION["userdata"]["id"]);
            $effortLabels = $this->getEffortLabels();
            $priorityLabels = $this->getPriorityLabels();
            $ticketTypes = $this->getTicketTypes();
            $statusLabels = $this->getAllStatusLabelsByUserId($_SESSION["userdata"]["id"]);

            $milestoneArray = array();
            foreach($tickets as $ticketGroup){
                foreach($ticketGroup["tickets"] as $ticket) {
                    if(isset($milestoneArray[$ticket["projectId"]])){
                        continue;
                    }else{
                        $milestoneArray[$ticket["projectId"]] = $this->getAllMilestones(["sprint" => '', "type" => "milestone", "currentProject" => $ticket["projectId"]]);
                    }
                }
            }


            $allAssignedprojects = $this->projectService->getProjectsAssignedToUser($_SESSION['userdata']['id'], 'open');

            $tickets = self::dispatch_filter("myTodoWidgetTasks", $tickets);

            return array(
                'tickets' => $tickets,
                'onTheClock' => $onTheClock,
                'efforts' => $effortLabels,
                'priorities' => $priorityLabels,
                'ticketTypes' =>  $ticketTypes,
                'statusLabels' => $statusLabels,
                'milestones' => $milestoneArray,
                'allAssignedprojects' => $allAssignedprojects,
                'projectFilter' => $projectFilter,
                'groupBy' => $groupBy,
            );
        }

        public function prepareTicketDates(&$values) {

            //Prepare dates for db

            if (!empty($values['dateToFinish'])) {

                if (isset($values['timeToFinish']) && $values['timeToFinish'] != null) {
                    $values['dateToFinish'] =  $this->dateTimeHelper->parseUserDateTime($values['dateToFinish'], $values['timeToFinish'])->formatDateTimeForDb();
                    unset($values['timeToFinish']);
                }else{
                    $values['dateToFinish'] = $this->dateTimeHelper->parseUserDateTime($values['dateToFinish'], "end")->formatDateTimeForDb();
                }

            }

            if (!empty($values['editFrom'])) {

                if (isset($values['timeFrom']) && $values['timeFrom'] != null) {
                    $values['editFrom'] = $this->dateTimeHelper->parseUserDateTime($values['editFrom'], $values['timeFrom'], FromFormat::UserDateTime)->formatDateTimeForDb();
                    unset($values['timeFrom']);
                }else{
                    $values['editFrom'] = $this->dateTimeHelper->parseUserDateTime($values['editFrom'], "start")->formatDateTimeForDb();
                }
            }

            if (!empty($values['editTo'])) {

                if (isset($values['timeTo']) && $values['timeTo'] != null) {
                    $values['editTo'] = $this->dateTimeHelper->parseUserDateTime($values['editTo'], $values['timeTo'])->formatDateTimeForDb();
                    unset($values['timeTo']);
                }else{
                    $values['editTo'] = $this->dateTimeHelper->parseUserDateTime($values['editTo'], "end")->isoDateTime();
                }
            }

            return $values;
        }
    }

}

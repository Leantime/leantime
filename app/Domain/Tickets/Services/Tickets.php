<?php

namespace Leantime\Domain\Tickets\Services {

    use Carbon\CarbonImmutable;
    use Carbon\CarbonInterface;
    use DateTime;
    use Illuminate\Container\EntryNotFoundException;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Illuminate\Support\Str;
    use Leantime\Core\Configuration\Environment as EnvironmentCore;
    use Leantime\Core\Events\DispatchesEvents;
    use Leantime\Core\Language as LanguageCore;
    use Leantime\Core\Support\DateTimeHelper;
    use Leantime\Core\Support\FromFormat;
    use Leantime\Core\UI\Template as TemplateCore;
    use Leantime\Domain\Goalcanvas\Services\Goalcanvas;
    use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
    use Leantime\Domain\Sprints\Services\Sprints as SprintService;
    use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
    use Leantime\Domain\Tickets\Repositories\TicketHistory;
    use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
    use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
    use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;

    /**
     * @api
     */
    class Tickets
    {
        use DispatchesEvents;

        /**
         * Constructor method for the class.
         *
         * @param  TemplateCore  $tpl  The template core instance.
         * @param  LanguageCore  $language  The language core instance.
         * @param  EnvironmentCore  $config  The environment core instance.
         * @param  ProjectRepository  $projectRepository  The project repository instance.
         * @param  TicketRepository  $ticketRepository  The ticket repository instance.
         * @param  TimesheetRepository  $timesheetsRepo  The timesheet repository instance.
         * @param  SettingRepository  $settingsRepo  The setting repository instance.
         * @param  ProjectService  $projectService  The project service instance.
         * @param  TimesheetService  $timesheetService  The timesheet service instance.
         * @param  SprintService  $sprintService  The sprint service instance.
         * @param  TicketHistory  $ticketHistoryRepo  The ticket history repository instance.
         * @param  Goalcanvas  $goalcanvasService  The goal canvas service instance.
         * @param  DateTimeHelper  $dateTimeHelper  The date time helper instance.
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
        ) {}

        /**
         * Gets all status labels for the current set project
         *
         * @param  int  $projectId  project id to get status labels for
         * @return array returns an array of status labels
         *
         * @api
         */
        public function getStatusLabels($projectId = null): array
        {
            return $this->ticketRepository->getStateLabels($projectId);
        }

        /**
         * getAllStatusLabelsByUserId - Gets all the status labels a specific user might encounter and groups them by project.
         * Used to get all the status dropdowns for user home dashboards
         *
         * @params int $userId User Id
         *
         * @api
         * @api
         */
        public function getAllStatusLabelsByUserId($userId): array
        {

            $statusLabelsByProject = [];

            $userProjects = $this->projectService->getProjectsAssignedToUser($userId);

            if ($userProjects) {
                foreach ($userProjects as $project) {
                    $statusLabelsByProject[$project['id']] = $this->ticketRepository->getStateLabels($project['id']);
                }
            }

            if (session()->exists('currentProject')) {
                $statusLabelsByProject[session('currentProject')] = $this->ticketRepository->getStateLabels(session('currentProject'));
            }

            // There is a non zero chance that a user has tickets assigned to them without a project assignment.
            // Checking user assigned tickets to see if there are missing projects.
            $allTickets = $this->ticketRepository->getAllBySearchCriteria(['currentProject' => '', 'users' => $userId, 'status' => 'not_done', 'sprint' => ''], 'duedate');

            foreach ($allTickets as $row) {
                if (! isset($statusLabelsByProject[$row['projectId']])) {
                    $statusLabelsByProject[$row['projectId']] = $this->ticketRepository->getStateLabels($row['projectId']);
                }
            }

            return $statusLabelsByProject;
        }

        /**
         * saveStatusLabels - Saves the description/label of a status
         *
         * @params array $params label information
         *
         * @api
         * @api
         */
        public function saveStatusLabels($params): bool
        {
            if (isset($params['labelKeys']) && is_array($params['labelKeys']) && count($params['labelKeys']) > 0) {
                $statusArray = [];

                foreach ($params['labelKeys'] as $labelKey) {
                    $labelKey = filter_var($labelKey, FILTER_SANITIZE_NUMBER_INT);

                    $statusArray[$labelKey] = [
                        'name' => $params['label-'.$labelKey] ?? '',
                        'class' => $params['labelClass-'.$labelKey] ?? 'label-default',
                        'statusType' => $params['labelType-'.$labelKey] ?? 'NEW',
                        'kanbanCol' => $params['labelKanbanCol-'.$labelKey] ?? false,
                        'sortKey' => $params['labelSort-'.$labelKey] ?? 99,
                    ];
                }

                session()->forget('projectsettings.ticketlabels');

                self::dispatchEvent('statusLabels_updated');

                return $this->settingsRepo->saveSetting('projectsettings.'.session('currentProject').'.ticketlabels', serialize($statusArray));
            } else {
                return false;
            }
        }

        /**
         * @api
         */
        public function getKanbanColumns(): array
        {

            $statusList = $this->ticketRepository->getStateLabels();

            $visibleCols = [];

            foreach ($statusList as $key => $status) {
                if ($status['kanbanCol']) {
                    $visibleCols[$key] = $status;
                }
            }

            return $visibleCols;
        }

        /**
         * @return array|string[]
         *
         * @api
         */
        public function getTypeIcons(): array
        {

            return $this->ticketRepository->typeIcons;
        }

        /**
         * @return array|string[]
         *
         * @api
         */
        public function getEffortLabels(): array
        {

            return $this->ticketRepository->efforts;
        }

        /**
         * @return array|string[]
         *
         * @api
         */
        public function getTicketTypes(): array
        {

            return $this->ticketRepository->type;
        }

        /**
         * @return array|string[]
         *
         * @api
         */
        public function getPriorityLabels(): array
        {

            return $this->ticketRepository->priority;
        }

        /**
         * @api
         */
        public function prepareTicketSearchArray(array $searchParams): array
        {

            $searchCriteria = [
                'currentProject' => session('currentProject') ?? '',
                'currentUser' => session('userdata.id') ?? '',
                'currentClient' => session('userdata.clientId') ?? '',
                'sprint' => session('currentSprint') ?? '',
                'users' => '',
                'clients' => '',
                'status' => '',
                'term' => '',
                'effort' => '',
                'type' => '',
                'excludeType' => 'milestone',
                'milestone' => '',
                'priority' => '',
                'orderBy' => 'sortIndex',
                'orderDirection' => 'DESC',
                'groupBy' => '',
            ];

            // Isset is all we want to do since empty values are valid
            if (isset($searchParams['currentProject']) === true) {
                $searchCriteria['currentProject'] = $searchParams['currentProject'];
            }

            if (isset($searchParams['currentUser']) === true) {
                $searchCriteria['currentUser'] = $searchParams['currentUser'];
            }

            if (isset($searchParams['users']) === true) {
                $searchCriteria['users'] = $searchParams['users'];
            }

            if (isset($searchParams['status']) === true) {
                $searchCriteria['status'] = $searchParams['status'];
            }

            if (isset($searchParams['term']) === true) {
                $searchCriteria['term'] = $searchParams['term'];
            }

            if (isset($searchParams['effort']) === true) {
                $searchCriteria['effort'] = $searchParams['effort'];
            }

            if (isset($searchParams['excludeType']) === true) {
                $searchCriteria['excludeType'] = $searchParams['excludeType'];
            }

            if (isset($searchParams['type']) === true) {
                $searchCriteria['type'] = $searchParams['type'];

                // Give inclusion higher priority than exclusion for now
                $typeIn = explode(',', $searchCriteria['type']);
                $typeOut = explode(',', $searchCriteria['excludeType']);

                $typeOutFiltered = array_diff($typeOut, $typeIn);
                $searchCriteria['excludeType'] = implode(',', $typeOutFiltered);
            }

            if (isset($searchParams['milestone']) === true) {
                $searchCriteria['milestone'] = $searchParams['milestone'];
            }

            if (isset($searchParams['groupBy']) === true) {
                $searchCriteria['groupBy'] = $searchParams['groupBy'];
            }

            if (isset($searchParams['orderBy']) === true) {
                $searchCriteria['orderBy'] = $searchParams['orderBy'];
            }

            if (isset($searchParams['orderDirection']) === true) {
                $searchCriteria['orderDirection'] = $searchParams['orderDirection'];
            }

            if (isset($searchParams['priority']) === true) {
                $searchCriteria['priority'] = $searchParams['priority'];
            }

            if (isset($searchParams['clients']) === true) {
                $searchCriteria['clients'] = $searchParams['clients'];
            }

            // The sprint selector is just a filter but remains in place across the session. Setting session here when it's selected
            if (isset($searchParams['sprint']) === true) {
                $searchCriteria['sprint'] = $searchParams['sprint'];
                session(['currentSprint' => $searchCriteria['sprint']]);
            }

            return $searchCriteria;
        }

        /**
         * @api
         */
        public function countSetFilters(array $searchCriteria): int
        {
            $count = 0;
            $setFilters = [];
            foreach ($searchCriteria as $key => $value) {
                if (
                    $key != 'groupBy'
                    && $key != 'currentProject'
                    && $key != 'orderBy'
                    && $key != 'currentUser'
                    && $key != 'currentClient'
                    && $key != 'sprint'
                    && $key != 'orderDirection'
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
         * @api
         */
        public function getSetFilters(array $searchCriteria, bool $includeGroup = false): array
        {
            $setFilters = [];
            foreach ($searchCriteria as $key => $value) {
                if (
                    $key != 'currentProject'
                    && $key != 'orderBy'
                    && $key != 'currentUser'
                    && $key != 'clients'
                    && $key != 'sprint'
                    && $key != 'orderDirection'
                ) {
                    if ($includeGroup === true && $key == 'groupBy' && $value != '') {
                        $setFilters[$key] = $value;
                    } elseif ($value != '') {
                        $setFilters[$key] = $value;
                    }
                }
            }

            return $setFilters;
        }

        /**
         * @return array|bool
         *
         * @api
         */
        public function getAll(?array $searchCriteria = null): array|false
        {
            return $this->ticketRepository->getAllBySearchCriteria(
                searchCriteria: $searchCriteria ?? [],
                sort: $searchCriteria['orderBy'] ?? 'date',
                includeCounts: false

            );
        }

        public function simpleTicketCounter(?int $userId = null, ?int $project = null, string $status = ''): int
        {

            $tickets = $this->ticketRepository->simpleTicketQuery($userId, $project);

            if ($status != '' && is_array($tickets)) {
                $ticketCounter = 0;
                $projectStatusLabels = [];
                foreach ($tickets as $ticket) {
                    if (! isset($projectStatusLabels[$ticket['projectId']])) {
                        $projectStatusLabels[$ticket['projectId']] = $this->ticketRepository->getStateLabels($ticket['projectId']);
                    }

                    if (
                        $status == 'not_done' &&
                        (
                            ! isset($projectStatusLabels[$ticket['projectId']][$ticket['status']]) ||
                            $projectStatusLabels[$ticket['projectId']][$ticket['status']]['statusType'] !== 'DONE'
                        )
                    ) {
                        $ticketCounter++;

                        continue;
                    }

                    if (
                        isset($projectStatusLabels[$ticket['projectId']][$ticket['status']]['statusType']) && $projectStatusLabels[$ticket['projectId']][$ticket['status']]['statusType'] == $status
                    ) {
                        $ticketCounter++;
                    }
                }

                return $ticketCounter;
            }

            if (is_array($tickets)) {
                return count($tickets);
            }

            return 0;
        }

        public function getAllOpenUserTickets(?int $userId = null, ?int $project = null): array
        {

            $tickets = $this->ticketRepository->simpleTicketQuery($userId, $project);

            $ticketArray = [];

            if (is_array($tickets)) {
                $ticketCounter = 0;
                $projectStatusLabels = [];

                foreach ($tickets as $ticket) {

                    if ($ticket['type'] !== 'milestone') {
                        if (! isset($projectStatusLabels[$ticket['projectId']])) {
                            $projectStatusLabels[$ticket['projectId']] = $this->ticketRepository->getStateLabels($ticket['projectId']);
                        }

                        if (isset($projectStatusLabels[$ticket['projectId']][$ticket['status']]) &&
                            $projectStatusLabels[$ticket['projectId']][$ticket['status']]['statusType'] !== 'DONE') {
                            $ticketArray[] = $ticket;
                        }
                    }
                }
            }

            return $ticketArray;
        }

        public function getScheduledTasks(CarbonImmutable $dateFrom, CarbonImmutable $dateTo, ?int $userId)
        {

            if (! $dateFrom->isUtc()) {
                $dateFrom->setTimezone('UTC');
            }
            if (! $dateTo->isUtc()) {
                $dateFrom->setTimezone('UTC');
            }

            $totalTasks = $this->ticketRepository->getScheduledTasks($dateFrom, $dateTo, $userId);

            $statusLabels = [];
            $doneTasks = [];

            foreach ($totalTasks as $ticket) {
                if (! isset($statusLabels[$ticket['projectId']])) {
                    $statusLabels[$ticket['projectId']] = $this->ticketRepository->getStateLabels($ticket['projectId']);
                }

                if (isset($statusLabels[$ticket['projectId']][$ticket['status']]) && $statusLabels[$ticket['projectId']][$ticket['status']]['statusType'] == 'DONE') {
                    $doneTasks[] = $ticket;
                }
            }

            return ['totalTasks' => $totalTasks, 'doneTasks' => $doneTasks];
        }

        /**
         * @throws BindingResolutionException
         *
         * @api
         */
        public function getAllGrouped($searchCriteria): array
        {
            $ticketGroups = [];

            $tickets = $this->ticketRepository->getAllBySearchCriteria(
                $searchCriteria,
                $searchCriteria['orderBy'] ?? 'date'
            );

            if (
                $searchCriteria['groupBy'] == null
                || $searchCriteria['groupBy'] == ''
                || $searchCriteria['groupBy'] == 'all'
            ) {
                $ticketGroups['all'] = [
                    'label' => 'all',
                    'id' => 'all',
                    'class' => '',
                    'items' => $tickets,
                ];

                return $ticketGroups;
            }

            $groupByOptions = $this->getGroupByFieldOptions();

            foreach ($tickets as $ticket) {
                $class = '';
                $moreInfo = '';

                if (isset($ticket[$searchCriteria['groupBy']])) {
                    $groupedFieldValue = strtolower($ticket[$searchCriteria['groupBy']]);

                    if (isset($ticketGroups[$groupedFieldValue])) {
                        $ticketGroups[$groupedFieldValue]['items'][] = $ticket;
                    } else {
                        switch ($searchCriteria['groupBy']) {
                            case 'status':
                                $status = $this->getStatusLabels();

                                if (isset($status[$groupedFieldValue])) {
                                    $label = $status[$groupedFieldValue]['name'];
                                    $class = $status[$groupedFieldValue]['class'];
                                } else {
                                    $label = 'New';
                                }

                                break;
                            case 'priority':
                                $priorities = $this->getPriorityLabels();
                                if (isset($priorities[$groupedFieldValue])) {
                                    $label = $priorities[$groupedFieldValue];
                                    $class = 'priority-text-'.$groupedFieldValue;
                                } else {
                                    $label = 'No Priority Set';
                                }
                                break;
                            case 'storypoints':
                                $efforts = $this->getEffortLabels();
                                $label = $efforts[$groupedFieldValue] ?? 'No Effort Set';
                                break;
                            case 'milestoneid':
                                $label = 'No Milestone Set';
                                if ($ticket['milestoneid'] > 0) {
                                    $milestone = $this->getTicket($ticket['milestoneid']);
                                    $color = $milestone->tags;
                                    $class = '" style="color:'.$color.'"';

                                    try {
                                        $startDate = dtHelper()->parseDbDateTime($milestone->editFrom)->formatDateForUser();
                                    } catch (\Exception $e) {
                                        $startDate = $this->language->__('text.no_date_defined');
                                    }

                                    try {
                                        $endDate = dtHelper()->parseDbDateTime($milestone->editTo)->formatDateForUser();
                                    } catch (\Exception $e) {
                                        $endDate = $this->language->__('text.no_date_defined');
                                    }

                                    $statusLabels = $this->getStatusLabels($milestone->projectId);
                                    $status = $statusLabels[$milestone->status]['name'];
                                    $class = '" style="color:'.$color.'"';
                                    $moreInfo = $this->language->__('label.start').': '.$startDate.' • '.$this->language->__('label.end').': '.$endDate.' • '.$this->language->__('label.status_lowercase').': '.$status;
                                    $label = $ticket['milestoneHeadline']." <a href='#/tickets/editMilestone/".$ticket['milestoneid']."' style='float:right;'><i class='fa fa-edit'></i></a><a>";
                                }

                                break;
                            case 'editorId':
                                $label = "<div class='profileImage'><img src='".BASE_URL.'/api/users?profileImage='.$ticket['editorId']."' /></div> ".$ticket['editorFirstname'].' '.$ticket['editorLastname'];

                                if ($ticket['editorFirstname'] == '' && $ticket['editorLastname'] == '') {
                                    $label = 'Not Assigned to Anyone';
                                }

                                break;
                            case 'sprint':
                                $label = $ticket['sprintName'];
                                if ($label == '') {
                                    $label = 'Not assigned to a sprint';
                                }
                                break;
                            case 'type':
                                $icon = $this->getTypeIcons();
                                $label = "<i class='fa ".($icon[strtolower($ticket['type'])] ?? '')."'></i>".$ticket['type'];
                                break;
                            default:
                                $label = $groupedFieldValue;
                                break;
                        }

                        $ticketGroups[$groupedFieldValue] = [
                            'label' => $label,
                            'more-info' => $moreInfo,
                            'id' => strtolower($groupedFieldValue),
                            'class' => $class,
                            'items' => [$ticket],
                        ];
                    }
                }
            }

            // Sort main groups

            switch ($searchCriteria['groupBy']) {
                case 'status':
                case 'priority':
                case 'storypoints':
                    $ticketGroups = array_sort($ticketGroups, 'id');
                    // no break
                default:
                    $ticketGroups = array_sort($ticketGroups, 'label');
                    break;
            }

            return $ticketGroups;
        }

        /**
         * @api
         */
        public function getAllPossibleParents(TicketModel $ticket, string $projectId = 'currentProject'): array
        {

            if ($projectId == 'currentProject') {
                $projectId = session('currentProject');
            }

            $results = $this->ticketRepository->getAllPossibleParents($ticket, $projectId);

            if (is_array($results)) {
                return $results;
            } else {
                return [];
            }
        }

        /**
         * @throws BindingResolutionException
         *
         * @api
         */
        public function getTicket($id): TicketModel|bool
        {

            $ticket = $this->ticketRepository->getTicket($id);

            // Check if user is allowed to see ticket
            if ($ticket && $this->projectService->isUserAssignedToProject(session('userdata.id'), $ticket->projectId)) {
                return $ticket;
            }

            return false;
        }

        /**
         * @api
         */
        public function getLastTickets($projectId, int $limit = 5): bool|array
        {

            $searchCriteria = $this->prepareTicketSearchArray(['currentProject' => $projectId, 'users' => '', 'status' => 'not_done', 'sprint' => '', 'limit' => $limit]);
            $allTickets = $this->ticketRepository->getAllBySearchCriteria($searchCriteria, 'date', $limit);

            return $allTickets;
        }

        /**
         * @param  false  $includeDoneTickets
         *
         * @throws \Exception
         *
         * @api
         */
        public function getOpenUserTicketsThisWeekAndLater($userId, $projectId, bool $includeDoneTickets = false, bool $includeMilestones = false): array
        {

            if ($includeDoneTickets === true) {
                $searchStatus = 'all';
            } else {
                $searchStatus = 'not_done';
            }
            $searchCriteria = $this->prepareTicketSearchArray(['currentProject' => $projectId, 'currentUser' => $userId, 'users' => $userId, 'status' => $searchStatus, 'sprint' => '']);
            if ($includeMilestones) {
                $searchCriteria['excludeType'] = '';
            }
            $allTickets = $this->ticketRepository->getAllBySearchCriteria(
                searchCriteria: $searchCriteria,
                sort: 'duedate',
                includeCounts: false);

            $statusLabels = $this->getAllStatusLabelsByUserId($userId);

            $tickets = [];

            foreach ($allTickets as $row) {
                // There is a non zero chance that a user has tasks assigned to them while not being part of the project
                // Need to get those status labels as well
                if (! isset($statusLabels[$row['projectId']])) {
                    $statusLabels[$row['projectId']] = $this->ticketRepository->getStateLabels($row['projectId']);
                }

                // There is a chance that the status was removed after it was assigned to a ticket
                if (isset($statusLabels[$row['projectId']][$row['status']]) && ($statusLabels[$row['projectId']][$row['status']]['statusType'] != 'DONE' || $includeDoneTickets === true)) {
                    if ($row['dateToFinish'] == '0000-00-00 00:00:00' || $row['dateToFinish'] == '1969-12-31 00:00:00' || $row['dateToFinish'] == null) {
                        if (isset($tickets['later']['tickets'])) {
                            $tickets['later']['tickets'][] = $row;
                        } else {
                            $tickets['later'] = [
                                'labelName' => 'subtitles.due_later',
                                'groupValue' => '',
                                'tickets' => [$row],
                                'order' => 3,
                            ];
                        }
                    } else {
                        $today = dtHelper()->userNow()->setToDbTimezone();
                        $dbDueDate = dtHelper()->parseDbDateTime($row['dateToFinish']);
                        $nextFriday = dtHelper()->userNow()->endOfWeek(CarbonInterface::FRIDAY)->setToDbTimezone();

                        if ($dbDueDate <= $nextFriday && $dbDueDate >= $today) {
                            if (isset($tickets['thisWeek']['tickets'])) {
                                $tickets['thisWeek']['tickets'][] = $row;
                            } else {
                                $tickets['thisWeek'] = [
                                    'labelName' => 'subtitles.due_this_week',
                                    'tickets' => [$row],
                                    'groupValue' => $dbDueDate->formatDateTimeForDb(),
                                    'order' => 2,
                                ];
                            }
                        } elseif ($dbDueDate <= $today) {
                            if (isset($tickets['overdue']['tickets'])) {
                                $tickets['overdue']['tickets'][] = $row;
                            } else {
                                $tickets['overdue'] = [
                                    'labelName' => 'subtitles.overdue',
                                    'tickets' => [$row],
                                    'groupValue' => $dbDueDate,
                                    'order' => 1,
                                ];
                            }
                        } else {
                            if (isset($tickets['later']['tickets'])) {
                                $tickets['later']['tickets'][] = $row;
                            } else {
                                $tickets['later'] = [
                                    'labelName' => 'subtitles.due_later',
                                    'tickets' => [$row],
                                    'groupValue' => '',
                                    'order' => 3,
                                ];
                            }
                        }
                    }
                }
            }

            // $ticketsSorted = array_sort($tickets, 'order');

            return $tickets;
        }

        /**
         * @api
         */
        public function getOpenUserTicketsByProject($userId, $projectId, bool $includeMilestones = false): array
        {

            $searchCriteria = $this->prepareTicketSearchArray(['currentProject' => $projectId, 'users' => $userId, 'status' => '', 'sprint' => '']);
            if ($includeMilestones) {
                $searchCriteria['excludeType'] = '';
            }
            $allTickets = $this->ticketRepository->getAllBySearchCriteria(
                searchCriteria: $searchCriteria,
                sort: 'duedate',
                includeCounts: false);

            $statusLabels = $this->getAllStatusLabelsByUserId($userId);

            $tickets = [];

            foreach ($allTickets as $row) {
                // Only include todos that are not done
                if (
                    isset($statusLabels[$row['projectId']]) &&
                    isset($statusLabels[$row['projectId']][$row['status']]) &&
                    $statusLabels[$row['projectId']][$row['status']]['statusType'] != 'DONE'
                ) {
                    if (isset($tickets[$row['projectId']])) {
                        $tickets[$row['projectId']]['tickets'][] = $row;
                    } else {
                        $tickets[$row['projectId']] = [
                            'labelName' => $row['clientName'].' / '.$row['projectName'],
                            'tickets' => [$row],
                            'groupValue' => $row['projectId'],
                        ];
                    }
                }
            }

            return $tickets;
        }

        /**
         * @api
         */
        public function getOpenUserTicketsByPriority($userId, $projectId, bool $includeMilestones = false): array
        {

            $searchCriteria = $this->prepareTicketSearchArray(['currentProject' => $projectId, 'users' => $userId, 'status' => '', 'sprint' => '']);

            $allTickets = $this->ticketRepository->getAllBySearchCriteria(
                searchCriteria: $searchCriteria,
                sort: 'priority',
                includeCounts: false);
            if ($includeMilestones) {
                $searchCriteria['excludeType'] = '';
            }
            $statusLabels = $this->getAllStatusLabelsByUserId($userId);

            $tickets = [];

            foreach ($allTickets as $row) {
                // Only include todos that are not done
                if (
                    isset($statusLabels[$row['projectId']]) &&
                    isset($statusLabels[$row['projectId']][$row['status']]) &&
                    $statusLabels[$row['projectId']][$row['status']]['statusType'] != 'DONE'
                ) {

                    if (empty($row['priority'])) {
                        $row['priority'] = 999;
                        $label = 'Unset';
                    } else {
                        $label = $this->ticketRepository->priority[$row['priority']];
                    }

                    if (isset($tickets[$row['priority']])) {
                        $tickets[$row['priority']]['tickets'][] = $row;
                    } else {
                        // If the priority is not set, the label for priority not defined is used.
                        if (empty($this->ticketRepository->priority[$row['priority']])) {
                            $label = $this->language->__('label.priority_not_defined');
                        }
                        $tickets[$row['priority']] = [
                            'labelName' => $label,
                            'tickets' => [$row],
                            'groupValue' => $row['priority'],
                        ];
                    }
                }
            }

            // Sort by group keys which are priority integers
            ksort($tickets);

            return $tickets;
        }

        /**
         * @api
         */
        public function getOpenUserTicketsBySprint($userId, $projectId, bool $includeMilestones = false): array
        {

            $searchCriteria = $this->prepareTicketSearchArray(['currentProject' => $projectId, 'users' => $userId, 'status' => '', 'sprint' => '']);
            if ($includeMilestones) {
                $searchCriteria['excludeType'] = '';
            }
            $allTickets = $this->ticketRepository->getAllBySearchCriteria(
                searchCriteria: $searchCriteria,
                sort: 'duedate',
                includeCounts: false);

            $statusLabels = $this->getAllStatusLabelsByUserId($userId);

            $tickets = [];

            foreach ($allTickets as $row) {
                $sprint = $row['sprint'] ?? 'backlog';
                $sprintName = empty($row['sprintName']) ? $this->language->__('label.not_assigned_to_sprint') : $row['sprintName'];

                // Only include todos that are not done
                if (
                    isset($statusLabels[$row['projectId'] ?? '']) &&
                    isset($statusLabels[$row['projectId']][$row['status']]) &&
                    $statusLabels[$row['projectId']][$row['status']]['statusType'] != 'DONE'
                ) {
                    if (isset($tickets[$sprint])) {
                        $tickets[$sprint]['tickets'][] = $row;
                    } else {
                        $tickets[$sprint] = [
                            'labelName' => $row['projectName'].' / '.$sprintName,
                            'tickets' => [$row],
                            'groupValue' => $row['sprint'].'-'.$row['projectId'],
                        ];
                    }
                }
            }

            return $tickets;
        }

        /**
         * @api
         */
        public function getAllMilestones($searchCriteria, string $sortBy = 'standard'): array|false
        {
            if (is_array($searchCriteria) && $searchCriteria['currentProject'] > 0) {
                $items = $this->ticketRepository->getAllMilestones($searchCriteria, $sortBy);

                return $this->sortItemsHierarchically($items);
            }

            return [];
        }

        private function buildTicketTree(array $elements, $parentId = 0)
        {

            $branch = [];

            foreach ($elements as $element) {

                $elementParentId = null;
                if ($element->type === 'milestone') {
                    $elementParentId = $element->milestoneid;
                } elseif ($element->dependingTicketId > 0) {
                    $elementParentId = $element->dependingTicketId;
                } elseif ($element->milestoneid > 0) {
                    $elementParentId = $element->milestoneid;
                }

                if (is_null($elementParentId)) {
                    $elementParentId = 0;
                }

                if ($elementParentId === $parentId) {
                    $children = $this->buildTicketTree($elements, $element->id);
                    if ($children) {
                        usort($children, function ($a, $b) {

                            if ($a->sortIndex > 0 && $b->sortIndex > 0) {
                                return $a->sortIndex > $b->sortIndex ? 1 : -1;
                            }

                            // Otherwise compare dates
                            if (dtHelper()->isValidDateString($a->editFrom) && dtHelper()->isValidDateString($b->editFrom)) {
                                if (dtHelper()->parseDbDateTime($a->editFrom) > dtHelper()->parseDbDateTime($b->editFrom)) {
                                    return 1;
                                } elseif (dtHelper()->parseDbDateTime($a->editFrom) < dtHelper()->parseDbDateTime($b->editFrom)) {
                                    return -1;
                                }
                            }

                            return 0;
                        });

                        $element->children = $children;
                    }
                    $branch[] = $element;
                }
            }

            return $branch;
        }

        private function flattenTree($items, &$r)
        {
            foreach ($items as $item) {
                $c = isset($item->children) ? $item->children : null;
                unset($item->children);
                $r[] = $item;
                if ($c) {
                    $this->flattenTree($c, $r);
                }
            }
        }

        private function sortItemsHierarchically($items): array
        {
            $tree = [];
            $lookup = [];

            $tree = $this->buildTicketTree($items);

            $flattened = [];
            if (is_array($tree)) {
                $this->flattenTree($tree, $flattened);
                $final = $flattened;
                $sortKey = 0;
                foreach ($flattened as &$item) {
                    $sortKey++;
                    $item->sortIndex = $sortKey;
                }

                return $flattened;
            }

            return $tree;
        }

        private function sortTicketsWithinMilestone($tickets): array
        {
            usort($tickets, function ($a, $b) {
                // First priority: Dependencies
                if ($a->dependingTicketId == $b->id) {
                    return 1;
                }
                if ($b->dependingTicketId == $a->id) {
                    return -1;
                }

                // Second priority: sortIndex
                if ($a->sortIndex !== '' && $b->sortIndex !== '') {
                    if ($a->sortIndex != $b->sortIndex) {
                        return $a->sortIndex - $b->sortIndex;
                    }
                }

                // Third priority: editFrom date
                if ($a->editFrom && $b->editFrom) {
                    return strtotime($a->editFrom) - strtotime($b->editFrom);
                }

                return $a->id - $b->id;
            });

            return $tickets;
        }

        /**
         * @api
         */
        public function getAllMilestonesOverview(bool $includeArchived = false, string $sortBy = 'duedate', bool $includeTasks = false, int $clientId = 0): false|array
        {

            $allProjectMilestones = $this->ticketRepository->getAllMilestones(['sprint' => '', 'type' => 'milestone', 'clients' => $clientId]);

            return $allProjectMilestones;
        }

        /**
         * @api
         */
        public function getAllMilestonesByUserProjects($userId): array
        {

            $milestones = [];

            $userProjects = $this->projectService->getProjectsAssignedToUser($userId);
            if ($userProjects) {
                foreach ($userProjects as $project) {
                    $allProjectMilestones = $this->getAllMilestones(['sprint' => '', 'type' => 'milestone', 'currentProject' => $project['id']]);
                    $milestones[$project['id']] = $allProjectMilestones;
                }
            }

            if (session()->exists('currentProject')) {
                $allProjectMilestones = $this->getAllMilestones(['sprint' => '', 'type' => 'milestone', 'currentProject' => session('currentProject')]);
                $milestones[session('currentProject')] = $allProjectMilestones;
            }

            // There is a non zero chance that a user has tickets assigned to them without a project assignment.
            // Checking user assigned tickets to see if there are missing projects.
            $allTickets = $this->ticketRepository->getAllBySearchCriteria(['currentProject' => '', 'users' => $userId, 'status' => 'not_done', 'sprint' => ''], 'duedate');

            foreach ($allTickets as $row) {
                if (! isset($milestones[$row['projectId']])) {
                    $allProjectMilestones = $this->getAllMilestones(['sprint' => '', 'type' => 'milestone', 'currentProject' => session('currentProject')]);

                    $milestones[$row['projectId']] = $allProjectMilestones;
                }
            }

            return $milestones;
        }

        /**
         * Calculate the progress of a milestone based on the tickets associated with it.
         *
         * @param  int|string  $milestoneId  ID of the milestone.
         * @return float The progress of the milestone as a percentage.
         *
         * @throws EntryNotFoundException If the milestone with the given ID is not found.
         *
         * @api
         */
        public function getMilestoneProgress(int|string $milestoneId): float
        {

            if (is_numeric($milestoneId)) {
                $milestoneId = (int) $milestoneId;
            }

            $milestone = $this->getTicket($milestoneId);
            if (! $milestone) {
                throw new EntryNotFoundException("Can't find milestone");
            }

            $prepareSearchParams = $this->prepareTicketSearchArray(['milestone' => $milestoneId, 'currentProject' => $milestone->projectId, 'currentSprint' => '']);
            $tickets = $this->ticketRepository->getAllBySearchCriteria($prepareSearchParams);

            $statusLabels = $this->getStatusLabels($milestone->projectId);

            $defaultEffort = 3;
            $defaultPriority = 3; // low number high priority high priority 1-5 low priority

            // We want to take priority into consideration but not make it the main driver.
            $priorityFactor = [
                1 => 2,
                2 => 1.75,
                3 => 1.5,
                4 => 1.25,
                5 => 1,
            ];

            $totalScore = 0;
            $doneScore = 0;
            $inProgressScore = 0;

            foreach ($tickets as $ticket) {
                $effort = empty($ticket['storypoints']) ? $defaultEffort : $ticket['storypoints'];
                $priority = empty($ticket['priority']) ? $defaultPriority : $ticket['priority'];

                $ticketScore = $effort * $priorityFactor[$priority] ?? 1;

                $totalScore += $ticketScore;

                if (
                    isset($statusLabels[$ticket['status']])
                    && $statusLabels[$ticket['status']]['statusType'] == 'DONE'
                ) {
                    $doneScore += $ticketScore;

                    continue;
                }

                if (
                    isset($statusLabels[$ticket['status']])
                    && $statusLabels[$ticket['status']]['statusType'] == 'INPROGRESS'
                ) {
                    $inProgressScore += $ticketScore;
                }
            }

            if ($totalScore == 0) {
                return (float) 0;
            }

            $percentDone = $doneScore / $totalScore * 100;

            return (float) $percentDone;
        }

        public function getBulkMilestoneProgress(array $milestones)
        {
            if (empty($milestones)) {
                return $milestones;
            }

            foreach ($milestones as &$milestone) {
                if ($milestone->type == 'milestone') {
                    $milestoneProgress = $this->getMilestoneProgress($milestone->id);
                    $milestone->percentDone = $milestoneProgress;

                    // Handle associated tickets
                    if (isset($milestone->tickets)) {
                        $milestone->tickets = $this->sortTicketsWithinMilestone($milestone->tickets);
                    }
                }
            }

            return $milestones;
        }

        public function getRecentlyCompletedTicketsByUser(int $userId, ?int $projectId = null): array
        {

            // Get status labels
            $statusLabelsByProject = [];

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

            // Get tickets recently set to done (history table)

            $searchCriteria = $this->prepareTicketSearchArray(['currentProject' => '', 'users' => $userId, 'status' => 'done', 'sprint' => '', 'limit' => null]);
            $myCompletedTasks = $this->getAll($searchCriteria);
            $dateTime = new DateTime;
            $dateTime->modify('-1 week');

            $doneTasks = [];
            foreach ($myCompletedTasks as $ticket) {
                $history = $this->ticketHistoryRepo->getRecentTicketHistory($dateTime, $ticket['id']);

                foreach ($history as $activity) {
                    if (
                        $activity['changeType'] == 'status'
                        && isset($statusLabelsByProject[$ticket['projectId']][$activity['changeValue']])
                        && $statusLabelsByProject[$ticket['projectId']][$activity['changeValue']]['statusType'] == 'DONE'
                    ) {
                        $doneTasks[] = $ticket;
                    }
                }
            }

            return $doneTasks;
        }

        public function goalsRelatedToWork(int $userId, $projectId = null)
        {

            $statusLabelsByProject = [];

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

            // Get tickets recently set to done (history table)

            $searchCriteria = $this->prepareTicketSearchArray(['currentProject' => '', 'users' => $userId, 'status' => 'not_done', 'sprint' => '', 'limit' => null]);
            $myTask = $this->getAll($searchCriteria);

            $contributedToGoal = [];

            foreach ($myTask as $task) {
                if ($task['milestoneid'] !== '' && $task['milestoneid'] > 0) {
                    $goals = $this->goalcanvasService->getGoalsByMilestone($task['milestoneid']);
                    foreach ($goals as $goal) {
                        if (! isset($contributedToGoal[$goal['id']])) {
                            $contributedToGoal[$goal['id']] = $goal;
                        }
                    }
                }
            }

            return $contributedToGoal;
        }

        /**
         * @api
         */
        public function getAllSubtasks(int $ticketId): false|array
        {

            // TODO: Refactor to be recursive
            return $this->ticketRepository->getAllSubtasks($ticketId);
        }

        /**
         * @api
         */
        public function quickAddTicket($params): array|bool
        {

            $values = [
                'headline' => $params['headline'],
                'type' => 'task',
                'description' => $params['description'] ?? '',
                'projectId' => $params['projectId'] ?? session('currentProject'),
                'editorId' => session('userdata.id'),
                'userId' => session('userdata.id'),
                'date' => date('Y-m-d H:i:s'),
                'dateToFinish' => isset($params['dateToFinish']) ? strip_tags($params['dateToFinish']) : '',
                'status' => isset($params['status']) ? (int) $params['status'] : 3,
                'storypoints' => '',
                'hourRemaining' => '',
                'planHours' => '',
                'sprint' => isset($params['sprint']) ? (int) $params['sprint'] : '',
                'acceptanceCriteria' => '',
                'priority' => '',
                'tags' => '',
                'editFrom' => $params['editFrom'] ?? '',
                'editTo' => $params['editTo'] ?? '',
                'milestoneid' => isset($params['milestone']) ? (int) $params['milestone'] : '',
                'dependingTicketId' => '',
            ];

            if ($values['headline'] == '') {
                return ['status' => 'error', 'message' => 'Headline Missing'];
            }

            $values = $this->prepareTicketDates($values);

            $result = $this->ticketRepository->addTicket($values);

            self::dispatchEvent('ticket_created');

            if ($result > 0) {
                $values['id'] = $result;
                $actual_link = BASE_URL.'/dashboard/home#/tickets/showTicket/'.$result;
                $message = sprintf($this->language->__('email_notifications.new_todo_message'), session('userdata.name'), strip_tags($params['headline']));
                $subject = $this->language->__('email_notifications.new_todo_subject');

                $notification = new NotificationModel;
                $notification->url = [
                    'url' => $actual_link,
                    'text' => $this->language->__('email_notifications.new_todo_cta'),
                ];
                $notification->entity = $values;
                $notification->module = 'tickets';
                $notification->projectId = $values['projectId'] ?? session('currentProject') ?? -1;
                $notification->subject = $subject;
                $notification->authorId = session('userdata.id') ?? -1;
                $notification->message = $message;

                $this->projectService->notifyProjectUsers($notification);

                return $result;
            }

            return false;
        }

        /**
         * @api
         */
        public function quickAddMilestone($params): array|bool|int
        {

            $values = [
                'headline' => $params['headline'],
                'type' => 'milestone',
                'description' => '',
                'projectId' => $params['projectId'] ?? session('currentProject'),
                'editorId' => $params['editorId'] ?? session('userdata.id'),
                'userId' => session('userdata.id'),
                'date' => dtHelper()->userNow()->formatDateTimeForDb(),
                'dateToFinish' => '',
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
                'editTo' => $params['editTo'] ?? '',
            ];

            $values = $this->prepareTicketDates($values);

            if ($values['headline'] == '') {
                $error = ['status' => 'error', 'message' => 'Headline Missing'];

                return $error;
            }

            self::dispatchEvent('milestone_created');

            // $params is an array of field names. Exclude id
            return $this->ticketRepository->addTicket($values);
        }

        /**
         * Adds a ticket to the system.
         *
         * @param  array  $values  An array of ticket data.
         *                         - id (optional): The ID of the ticket.
         *                         - headline (optional): The headline of the ticket.
         *                         - type (optional): The type of the ticket. Default is "task".
         *                         - description (optional): The description of the ticket.
         *                         - projectId (optional): The ID of the project the ticket belongs to. Default is the current project.
         *                         - editorId (optional): The ID of the editor of the ticket.
         *                         - userId: The ID of the user creating the ticket.
         *                         - date: The date when the ticket is created.
         *                         - dateToFinish (optional): The date to finish the ticket.
         *                         - timeToFinish (optional): The time to finish the ticket.
         *                         - status (optional): The status of the ticket. Default is 3.
         *                         - planHours (optional): The planned hours for the ticket.
         *                         - tags (optional): The tags associated with the ticket.
         *                         - sprint (optional): The sprint the ticket belongs to.
         *                         - storypoints (optional): The story points assigned to the ticket.
         *                         - hourRemaining (optional): The remaining hours for the ticket.
         *                         - priority (optional): The priority of the ticket.
         *                         - acceptanceCriteria (optional): The acceptance criteria of the ticket.
         *                         - editFrom (optional): The edit from date of the ticket.
         *                         - timeFrom (optional): The edit from time of the ticket.
         *                         - editTo (optional): The edit to date of the ticket.
         *                         - timeTo (optional): The edit to time of the ticket.
         *                         - dependingTicketId (optional): The ID of the depending ticket.
         *                         - milestoneid (optional): The ID of the milestone the ticket belongs to.
         * @return array|int|bool If the ticket is successfully added, returns the ID of the ticket.
         *                        If the user does not have access to the project, returns an error message and type array.
         *                        If the headline is missing, returns an error message and type array.
         *
         * @api
         */
        public function addTicket($values): array|int|bool
        {
            $values = [
                'id' => '',
                'headline' => $values['headline'] ?? '',
                'type' => $values['type'] ?? 'task',
                'description' => $values['description'] ?? '',
                'projectId' => $values['projectId'] ?? session('currentProject'),
                'editorId' => $values['editorId'] ?? '',
                'userId' => session('userdata.id'),
                'date' => gmdate('Y-m-d H:i:s'),
                'dateToFinish' => $values['dateToFinish'] ?? '',
                'timeToFinish' => $values['timeToFinish'] ?? '',
                'status' => $values['status'] ?? 3,
                'planHours' => $values['planHours'] ?? '',
                'tags' => $values['tags'] ?? '',
                'sprint' => $values['sprint'] ?? '',
                'storypoints' => $values['storypoints'] ?? '',
                'hourRemaining' => $values['hourRemaining'] ?? '',
                'priority' => $values['priority'] ?? '',
                'acceptanceCriteria' => $values['acceptanceCriteria'] ?? '',
                'editFrom' => $values['editFrom'] ?? '',
                'timeFrom' => $values['timeFrom'] ?? '',
                'editTo' => $values['editTo'] ?? '',
                'timeTo' => $values['timeTo'] ?? '',
                'dependingTicketId' => $values['dependingTicketId'] ?? '',
                'milestoneid' => $values['milestoneid'] ?? '',
            ];

            if (! $this->projectService->isUserAssignedToProject(session('userdata.id'), $values['projectId'])) {
                return ['msg' => 'notifications.ticket_save_error_no_access', 'type' => 'error'];
            }

            if ($values['headline'] === '') {
                return ['msg' => 'notifications.ticket_save_error_no_headline', 'type' => 'error'];
            } else {
                $values = $this->prepareTicketDates($values);

                // Update Ticket
                $addTicketResponse = $this->ticketRepository->addTicket($values);

                self::dispatchEvent('ticket_created');

                if ($addTicketResponse !== false) {
                    $values['id'] = $addTicketResponse;
                    $subject = sprintf($this->language->__('email_notifications.new_todo_subject'), $addTicketResponse, strip_tags($values['headline']));
                    $actual_link = BASE_URL.'/dashboard/home#/tickets/showTicket/'.$addTicketResponse;
                    $message = sprintf($this->language->__('email_notifications.new_todo_message'), session('userdata.name'), strip_tags($values['headline']));

                    $notification = new NotificationModel;
                    $notification->url = [
                        'url' => $actual_link,
                        'text' => $this->language->__('email_notifications.new_todo_cta'),
                    ];
                    $notification->entity = $values;
                    $notification->module = 'tickets';
                    $notification->projectId = $values['projectId'] ?? session('currentProject') ?? -1;
                    $notification->subject = $subject;
                    $notification->authorId = session('userdata.id') ?? -1;
                    $notification->message = $message;

                    $this->projectService->notifyProjectUsers($notification);

                    return $addTicketResponse;
                }
            }

            return false;
        }

        // Update

        /**
         * Updates a ticket with the given values.
         *
         * @param  array  $values  The array containing the ticket values to update.
         *                         Accepted keys are:
         *                         - 'id' => The ticket ID.
         *                         - 'headline' => The ticket headline. (optional)
         *                         - 'type' => The ticket type. (optional)
         *                         - 'description' => The ticket description. (optional)
         *                         - 'projectId' => The project ID. Defaults to session("currentProject"). (optional);
         *                         - 'editorId' => The editor ID. (optional)
         *                         - 'date' => The ticket date. Defaults to the current date and time. (optional)
         *                         - 'dateToFinish' => The ticket deadline date. (optional)
         *                         - 'timeToFinish' => The ticket deadline time. (optional)
         *                         - 'status' => The ticket status. (optional)
         *                         - 'planHours' => The planned hours for the ticket. (optional)
         *                         - 'tags' => The tags for the ticket. (optional)
         *                         - 'sprint' => The sprint for the ticket. (optional)
         *                         - 'storypoints' => The story points for the ticket. (optional)
         *                         - 'hourRemaining' => The remaining hours for the ticket. (optional)
         *                         - 'priority' => The ticket priority. (optional)
         *                         - 'acceptanceCriteria' => The ticket acceptance criteria. (optional)
         *                         - 'editFrom' => The ticket edit 'from' date-time. (optional)
         *                         - 'time*
         *
         * @api
         */
        public function updateTicket($values): array|bool
        {
            if (! isset($values['headline'])) {
                $currentTicket = $this->getTicket($values['id']);

                if (! $currentTicket) {
                    return ['msg' => 'This ticket id does not exist within your leantime account.', 'type' => 'error'];
                }

                $values['headline'] = $currentTicket->headline;
            }

            $values = [
                'id' => $values['id'],
                'headline' => $values['headline'] ?? '',
                'type' => $values['type'] ?? '',
                'description' => $values['description'] ?? '',
                'projectId' => $values['projectId'] ?? session('currentProject'),
                'editorId' => $values['editorId'] ?? '',
                'date' => dtHelper()->userNow()->formatDateTimeForDb(),
                'dateToFinish' => $values['dateToFinish'] ?? '',
                'timeToFinish' => $values['timeToFinish'] ?? '',
                'status' => $values['status'] ?? '',
                'planHours' => $values['planHours'] ?? '',
                'tags' => $values['tags'] ?? '',
                'sprint' => $values['sprint'] ?? '',
                'storypoints' => $values['storypoints'] ?? '',
                'hourRemaining' => $values['hourRemaining'] ?? '',
                'priority' => $values['priority'] ?? '',
                'acceptanceCriteria' => $values['acceptanceCriteria'] ?? '',
                'editFrom' => $values['editFrom'] ?? '',
                'timeFrom' => $values['timeFrom'] ?? '',
                'editTo' => $values['editTo'] ?? '',
                'timeTo' => $values['timeTo'] ?? '',
                'dependingTicketId' => $values['dependingTicketId'] ?? '',
                'milestoneid' => $values['milestoneid'] ?? '',
            ];

            if ($values['projectId'] === null || $values['projectId'] === '' || $values['projectId'] === false) {
                return ['msg' => 'project id is not set', 'type' => 'error'];
            }

            if (! $this->projectService->isUserAssignedToProject(session('userdata.id'), $values['projectId'])) {
                return ['msg' => 'notifications.ticket_save_error_no_access', 'type' => 'error'];
            }

            $values = $this->prepareTicketDates($values);

            // Update Ticket
            if ($this->ticketRepository->updateTicket($values, $values['id']) === true) {
                $subject = sprintf($this->language->__('email_notifications.todo_update_subject'), $values['id'], strip_tags($values['headline']));
                $actual_link = BASE_URL.'/dashboard/home#/tickets/showTicket/'.$values['id'];
                $message = sprintf($this->language->__('email_notifications.todo_update_message'), session('userdata.name'), $values['headline']);

                $notification = new NotificationModel;
                $notification->url = [
                    'url' => $actual_link,
                    'text' => $this->language->__('email_notifications.todo_update_cta'),
                ];
                $notification->entity = $values;
                $notification->module = 'tickets';
                $notification->projectId = $values['projectId'] ?? session('currentProject') ?? -1;
                $notification->subject = $subject;
                $notification->authorId = session('userdata.id') ?? -1;
                $notification->message = $message;

                $this->projectService->notifyProjectUsers($notification);

                self::dispatchEvent('ticket_updated');

                return true;
            }

            return false;
        }

        /**
         * @api
         */
        public function patch($id, $params): bool
        {

            // $params is an array of field names. Exclude id
            unset($params['id']);
            unset($params['act']);

            $ticket = $this->getTicket($id);

            if (! $ticket) {
                return false;
            }

            $params = $this->prepareTicketDates($params);

            $return = $this->ticketRepository->patchTicket($id, $params);

            self::dispatchEvent('ticket_updated');

            // Todo: create events and move notification logic to notification module
            if (isset($params['status']) && $return) {
                $ticket = $this->getTicket($id);
                $subject = sprintf($this->language->__('email_notifications.todo_update_subject'), $id, strip_tags($ticket->headline));
                $actual_link = BASE_URL.'/dashboard/home#/tickets/showTicket/'.$id;
                $message = sprintf($this->language->__('email_notifications.todo_update_message'), session('userdata.name'), strip_tags($ticket->headline));

                $notification = app()->make(NotificationModel::class);
                $notification->url = [
                    'url' => $actual_link,
                    'text' => $this->language->__('email_notifications.todo_update_cta'),
                ];
                $notification->entity = $ticket;
                $notification->module = 'tickets';
                $notification->projectId = $ticket->projectId ?? session('currentProject') ?? -1;
                $notification->subject = $subject;
                $notification->authorId = session('userdata.id');
                $notification->message = $message;

                $this->projectService->notifyProjectUsers($notification);
            }

            return $return;
        }

        /**
         * moveTicket - Moves a ticket from one project to another. Milestone children will be moved as well
         *
         * @throws BindingResolutionException
         *
         * @api
         */
        public function moveTicket(int $id, int $projectId): bool
        {

            $ticket = $this->getTicket($id);

            if ($ticket) {
                // If milestone, move child todos
                if ($ticket->type == 'milestone') {
                    $milestoneTickets = $this->getAll(['milestone' => $ticket->id]);
                    // Update child todos
                    foreach ($milestoneTickets as $childTicket) {
                        $this->patch($childTicket['id'], ['projectId' => $projectId, 'sprint' => '']);
                    }
                }

                self::dispatchEvent('ticket_updated');

                // Update ticket
                return $this->patch($ticket->id, ['projectId' => $projectId, 'sprint' => '', 'dependingTicketId' => '', 'milestoneid' => '']);
            }

            return false;
        }

        /**
         * @return bool|string[]
         *
         * @api
         */
        public function quickUpdateMilestone($params): array|bool
        {

            $values = [
                'headline' => $params['headline'],
                'type' => 'milestone',
                'description' => '',
                'projectId' => session('currentProject'),
                'editorId' => $params['editorId'],
                'userId' => session('userdata.id'),
                'date' => dtHelper()->userNow()->formatDateTimeForDb(),
                'dateToFinish' => '',
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
                'editTo' => $params['editTo'] ?? '',
            ];

            if ($values['headline'] == '') {
                $error = ['status' => 'error', 'message' => 'Headline Missing'];

                return $error;
            }

            $values = $this->prepareTicketDates($values);

            self::dispatchEvent('milestone_updated');

            // $params is an array of field names. Exclude id
            return $this->ticketRepository->updateTicket($values, $params['id']);
        }

        /**
         * @api
         */
        public function upsertSubtask($values, $parentTicket): bool
        {

            $subtaskId = $values['subtaskId'] ?? 'new';

            $values = [
                'headline' => $values['headline'],
                'type' => 'subtask',
                'description' => $values['description'] ?? '',
                'projectId' => $parentTicket->projectId,
                'editorId' => session('userdata.id'),
                'userId' => session('userdata.id'),
                'date' => $this->dateTimeHelper->userNow()->formatDateTimeForDb(),
                'dateToFinish' => $values['dateToFinish'] ?? '',
                'priority' => $values['priority'] ?? 3,
                'status' => $values['status'],
                'storypoints' => $values['storypoints'] ?? '',
                'hourRemaining' => $values['hourRemaining'] ?? 0,
                'planHours' => $values['planHours'] ?? 0,
                'sprint' => '',
                'acceptanceCriteria' => '',
                'tags' => '',
                'editFrom' => $values['editFrom'] ?? '',
                'editTo' => $values['editTo'] ?? '',
                'dependingTicketId' => $parentTicket->id,
                'milestoneid' => $parentTicket->milestoneid,
            ];

            $values = $this->prepareTicketDates($values);

            if ($subtaskId == 'new' || $subtaskId == '') {
                // New Ticket
                if (! $this->ticketRepository->addTicket($values)) {
                    return false;
                }

                self::dispatchEvent('ticket_created');

            } else {
                // Update Ticket

                if (! $this->ticketRepository->updateTicket($values, $subtaskId)) {
                    return false;
                }

                self::dispatchEvent('ticket_updated');
            }

            return true;
        }

        /**
         * @return false|void
         *
         * @api
         */
        public function updateTicketSorting($params)
        {

            // ticketId: sortIndex
            foreach ($params as $id => $sortKey) {
                if ($this->ticketRepository->patchTicket($id, ['sortIndex' => $sortKey]) === false) {
                    return false;
                }
            }

            self::dispatchEvent('ticket_updated');

            return true;
        }

        /**
         * @throws BindingResolutionException
         *
         * @api
         */
        public function updateTicketStatusAndSorting($params, $handler = null): bool
        {

            // Jquery sortable serializes the array for kanban in format
            // statusKey: ticket[]=X&ticket[]=X2...,
            // statusKey2: ticket[]=X&ticket[]=X2...,
            // This represents status & kanban sorting
            foreach ($params as $status => $ticketList) {
                if (is_numeric($status) && ! empty($ticketList)) {
                    $tickets = explode('&', $ticketList);

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
                // Assumes format ticket_ID
                $id = substr($handler, 7);

                $ticket = $this->getTicket($id);

                if ($ticket) {
                    $subject = sprintf($this->language->__('email_notifications.todo_update_subject'), $id, strip_tags($ticket->headline));
                    $actual_link = BASE_URL.'/dashboard/home#/tickets/showTicket/'.$id;
                    $message = sprintf($this->language->__('email_notifications.todo_update_message'), session('userdata.name'), strip_tags($ticket->headline));

                    $notification = app()->make(NotificationModel::class);
                    $notification->url = [
                        'url' => $actual_link,
                        'text' => $this->language->__('email_notifications.todo_update_cta'),
                    ];
                    $notification->entity = $ticket;
                    $notification->module = 'tickets';
                    $notification->projectId = $ticket->projectId ?? session('currentProject') ?? -1;
                    $notification->subject = $subject;
                    $notification->authorId = session('userdata.id') ?? -1;
                    $notification->message = $message;

                    $this->projectService->notifyProjectUsers($notification);
                }
            }

            self::dispatchEvent('ticket_updated');

            return true;
        }

        // Delete
        /**
         * @return bool|string[]
         *
         * @throws BindingResolutionException
         *
         * @api
         */
        public function delete($id): array|bool
        {

            $ticket = $this->getTicket($id);

            if (! $ticket || ! $this->projectService->isUserAssignedToProject(session('userdata.id'), $ticket->projectId)) {
                return ['msg' => 'notifications.ticket_delete_error', 'type' => 'error'];
            }

            if ($this->ticketRepository->delticket($id)) {

                self::dispatchEvent('ticket_deleted');

                return true;
            }

            return false;
        }

        public function canDelete($id)
        {

            $ticket = $this->getTicket($id);

            if (empty($ticket)) {
                throw new \Exception('Task does not exist');
            }

            $hasLoggedHours = $this->timesheetsRepo->getTimesheetsByTicket($id);

            if ($hasLoggedHours) {
                throw new \Exception('Task has timesheets attached, delete all timesheets first or consider archiving the task');
            }

            return true;

        }

        /**
         * @return bool|string[]
         *
         * @throws BindingResolutionException
         *
         * @api
         */
        public function deleteMilestone($id): array|bool
        {

            $ticket = $this->getTicket($id);

            if (! $this->projectService->isUserAssignedToProject(session('userdata.id'), $ticket->projectId)) {
                return ['msg' => 'notifications.milestone_delete_error', 'type' => 'error'];
            }

            if ($this->ticketRepository->delMilestone($id)) {
                self::dispatchEvent('milestone_deleted');

                return true;
            }

            return false;
        }

        /**
         * @return mixed|string
         *
         * @api
         */
        public function getLastTicketViewUrl(): mixed
        {

            $url = BASE_URL.'/tickets/showKanban';

            if (session()->exists('lastTicketView') && session('lastTicketView') != '') {
                if (session('lastTicketView') === 'kanban' && session()->exists('lastFilterdTicketKanbanView') && session('lastFilterdTicketKanbanView') != '') {
                    return session('lastFilterdTicketKanbanView');
                }

                if (session('lastTicketView') === 'table' && session()->exists('lastFilterdTicketTableView') && session('lastFilterdTicketTableView') != '') {
                    return session('lastFilterdTicketTableView');
                }

                if (session('lastTicketView') === 'list' && session()->exists('lastFilterdTicketListView') && session('lastFilterdTicketListView') != '') {
                    return session('lastFilterdTicketListView');
                }

                return $url;
            } else {
                return $url;
            }
        }

        public function getLastTimelineViewUrl(): mixed
        {

            $url = BASE_URL.'/tickets/roadmap';

            if (session()->exists('lastMilestoneView') && session('lastMilestoneView') != '') {
                if (session('lastMilestoneView') === 'table' && session()->exists('lastFilterdMilestoneTableView') && session('lastFilterdMilestoneTableView') != '') {
                    return session('lastFilterdMilestoneTableView');
                }

                if (session('lastMilestoneView') === 'roadmap' && session()->exists('lastFilterdTicketRoadmapView') && session('lastFilterdTicketRoadmapView') != '') {
                    return session('lastFilterdTicketRoadmapView');
                }

                if (session('lastMilestoneView') === 'calendar' && session()->exists('lastFilterdTicketCalendarView') && session('lastFilterdTicketCalendarView') != '') {
                    return session('lastFilterdTicketCalendarView');
                }

                return $url;
            } else {
                return $url;
            }
        }

        /**
         * @api
         */
        public function getGroupByFieldOptions(): array
        {
            return [
                'all' => [
                    'id' => 'all',
                    'field' => 'all',
                    'class' => '',
                    'label' => 'no_group',

                ],
                'type' => [
                    'id' => 'type',
                    'field' => 'type',
                    'label' => 'type',
                    'class' => '',
                    'function' => 'getTicketTypes',
                ],
                'status' => [
                    'id' => 'status',
                    'field' => 'status',
                    'label' => 'todo_status',
                    'class' => '',
                    'function' => 'getStatusLabels',
                ],
                'effort' => [
                    'id' => 'effort',
                    'field' => 'storypoints',
                    'label' => 'effort',
                    'class' => '',
                    'function' => 'getEffortLabels',
                ],
                'priority' => [
                    'id' => 'priority',
                    'field' => 'priority',
                    'label' => 'priority',
                    'class' => '',
                    'function' => 'getPriorityLabels',
                ],
                'milestone' => [
                    'id' => 'milestone',
                    'field' => 'milestoneid',
                    'label' => 'milestone',
                    'class' => '',
                    'function' => null,
                ],
                'user' => [
                    'id' => 'user',
                    'field' => 'editorId',
                    'label' => 'user',
                    'class' => '',
                    'funtion' => 'buildEditorName',
                ],
                'sprint' => [
                    'id' => 'sprint',
                    'field' => 'sprint',
                    'class' => '',
                    'label' => 'sprint',
                ],

                /*
                "tags" => [
                    'id' => 'groupByTagsLink',
                    'field' => 'tags',
                    'label' => 'tags',
                ],* @api
*
*/
            ];
        }

        /**
         * @return array[]
         *
         * @api
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
         *
         * @api
         */
        public function getNewFieldOptions(): array
        {
            if (! defined('BASE_URL')) {
                return [];
            }

            return [
                [
                    'url' => '#/tickets/newTicket',
                    'text' => 'links.add_todo',
                    'class' => 'ticketModal',
                ],
                [
                    'url' => '#/tickets/editMilestone',
                    'text' => 'links.add_milestone',
                    'class' => 'milestoneModal',
                ],
            ];
        }

        /**
         * @throws BindingResolutionException
         */
        public function getTicketTemplateAssignments($params): array
        {

            $currentSprint = $this->sprintService->getCurrentSprintId((int) session('currentProject'));

            $searchCriteria = $this->prepareTicketSearchArray($params);
            $searchCriteria['orderBy'] = 'kanbansort';

            $allTickets = $this->getAllGrouped($searchCriteria);
            $allTicketStates = $this->getStatusLabels();

            $efforts = $this->getEffortLabels();
            $priorities = $this->getPriorityLabels();
            $types = $this->getTicketTypes();

            // Types are being used for filters. Add milestone as a type
            $types[] = 'milestone';

            $ticketTypeIcons = $this->getTypeIcons();

            $numOfFilters = $this->countSetFilters($searchCriteria);

            $onTheClock = $this->timesheetService->isClocked(session('userdata.id'));

            $sprints = $this->sprintService->getAllSprints(session('currentProject'));
            $futureSprints = $this->sprintService->getAllFutureSprints((int) session('currentProject'));

            $users = $this->projectService->getUsersAssignedToProject(session('currentProject'));

            $milestones = $this->getAllMilestones([
                'sprint' => '',
                'type' => 'milestone',
                'currentProject' => session('currentProject'),
            ]);

            $groupByOptions = $this->getGroupByFieldOptions();
            $newField = $this->getNewFieldOptions();
            $sortOptions = $this->getSortByFieldOptions();

            $searchUrlString = '';
            if ($numOfFilters > 0 || $searchCriteria['groupBy'] != '') {
                $searchUrlString = '?'.http_build_query($this->getSetFilters($searchCriteria, true));
            }

            $allTickets = self::dispatchFilter('filterTickets', $allTickets);

            return [
                'currentSprint' => session('currentSprint'),
                'searchCriteria' => $searchCriteria,
                'allTickets' => $allTickets,
                'allTicketStates' => $allTicketStates,
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
            ];
        }

        /**
         * Retrieves the assignments for the ToDoWidget.
         *
         * @param  array  $params  The parameters for filtering the assignments.
         *                         - projectFilter (optional): The project filter for the assignments.
         *                         - groupBy (optional): The grouping for the assignments (time, project, priority, or sprint).
         * @return array An array containing the assignments for the ToDoWidget.
         *               - tickets: The open user tickets based on the groupBy parameter.
         *               - onTheClock: Indicates whether the user is currently clocked in.
         *               - efforts: The labels for the effort values.
         *               - priorities: The labels for the priority values.
         *               - ticketTypes: The available ticket types.
         *               - statusLabels: The labels for the ticket status values.
         *               - milestones: The milestones for each project.
         *               - allAssignedprojects: The projects assigned to the user.
         *               - projectFilter: The current project filter.
         *               - groupBy: The current grouping for the assignments.
         */
        public function getToDoWidgetAssignments($params)
        {

            $projectFilter = '';
            if (session()->exists('userHomeProjectFilter')) {
                $projectFilter = session('userHomeProjectFilter');
            }

            if (isset($params['projectFilter'])) {
                $projectFilter = $params['projectFilter'] !== 'all' ? $params['projectFilter'] : '';
                session(['userHomeProjectFilter' => $projectFilter]);
            }

            $groupBy = '';
            if (session()->exists('userHomeGroupBy')) {
                $groupBy = session('userHomeGroupBy');
            }

            if (isset($params['groupBy'])) {
                $groupBy = $params['groupBy'];
                session(['userHomeGroupBy' => $groupBy]);
            }

            if ($groupBy == '') {
                $groupBy = 'time';
            }

            if ($groupBy === 'time') {
                $tickets = $this->getOpenUserTicketsThisWeekAndLater(userId: session('userdata.id'), projectId: $projectFilter, includeMilestones: true);
            } elseif ($groupBy === 'project') {
                $tickets = $this->getOpenUserTicketsByProject(userId: session('userdata.id'), projectId: $projectFilter, includeMilestones: true);
            } elseif ($groupBy === 'priority') {
                $tickets = $this->getOpenUserTicketsByPriority(userId: session('userdata.id'), projectId: $projectFilter, includeMilestones: true);
            } elseif ($groupBy === 'sprint') {
                $tickets = $this->getOpenUserTicketsBySprint(userId: session('userdata.id'), projectId: $projectFilter, includeMilestones: true);
            }

            $onTheClock = $this->timesheetService->isClocked(session('userdata.id'));
            $effortLabels = $this->getEffortLabels();
            $priorityLabels = $this->getPriorityLabels();
            $ticketTypes = $this->getTicketTypes();
            $statusLabels = $this->getAllStatusLabelsByUserId(session('userdata.id'));

            $milestoneArray = [];
            foreach ($tickets as $ticketGroup) {
                foreach ($ticketGroup['tickets'] as $ticket) {
                    if (isset($milestoneArray[$ticket['projectId']])) {
                        continue;
                    } else {
                        $milestoneArray[$ticket['projectId']] = $this->getAllMilestones(['sprint' => '', 'type' => 'milestone', 'currentProject' => $ticket['projectId']]);
                    }
                }
            }

            $tickets = self::dispatch_filter('myTodoWidgetTasks', $tickets);

            return [
                'tickets' => $tickets,
                'onTheClock' => $onTheClock,
                'efforts' => $effortLabels,
                'priorities' => $priorityLabels,
                'ticketTypes' => $ticketTypes,
                'statusLabels' => $statusLabels,
                'milestones' => $milestoneArray,
                'allAssignedprojects' => $this->projectService->getProjectsAssignedToUser(session('userdata.id'), 'open'),
                'projectFilter' => $projectFilter,
                'groupBy' => $groupBy,
            ];
        }

        /**
         * Retrieves the hierarchical assignments for the ToDoWidget.
         *
         * @param  array  $params  The parameters for filtering the assignments.
         * @return array An array containing the hierarchical assignments for the ToDoWidget.
         */
        public function getToDoWidgetHierarchicalAssignments($params)
        {
            $projectFilter = '';
            if (session()->exists('userHomeProjectFilter')) {
                $projectFilter = session('userHomeProjectFilter');
            }

            if (isset($params['projectFilter'])) {
                $projectFilter = $params['projectFilter'] !== 'all' ? $params['projectFilter'] : '';
                session(['userHomeProjectFilter' => $projectFilter]);
            }

            $groupBy = '';
            if (session()->exists('userHomeGroupBy')) {
                $groupBy = session('userHomeGroupBy');
            }

            if (isset($params['groupBy'])) {
                $groupBy = $params['groupBy'];
                session(['userHomeGroupBy' => $groupBy]);
            }

            if ($groupBy == '') {
                $groupBy = 'time';
            }

            $userId = session('userdata.id');
            $sortingKey = "user.{$userId}.myTodosSorting";
            $userSorting = $this->settingsRepo->getSetting($sortingKey);

            // Get tickets based on grouping
            if ($groupBy === 'time') {
                $tickets = $this->getOpenUserTicketsThisWeekAndLater(session('userdata.id'), $projectFilter);
            } elseif ($groupBy === 'project') {
                $tickets = $this->getOpenUserTicketsByProject(session('userdata.id'), $projectFilter);
            } elseif ($groupBy === 'priority') {
                $tickets = $this->getOpenUserTicketsByPriority(session('userdata.id'), $projectFilter);
            } elseif ($groupBy === 'sprint') {
                $tickets = $this->getOpenUserTicketsBySprint(session('userdata.id'), $projectFilter);
            }

            if ($userSorting) {
                $sortingArray = json_decode($userSorting, true);
            }

            // Get all milestones that have tasks assigned to the user
            $allMilestones = [];
            $milestoneIds = [];
            $milestoneCache = [];

            // First collect all milestone IDs from the user's tasks
            foreach ($tickets as $groupKey => &$ticketGroup) {
                if (isset($ticketGroup['tickets']) && is_array($ticketGroup['tickets'])) {
                    foreach ($ticketGroup['tickets'] as $ticket) {
                        if (! empty($ticket['milestoneid']) && ! in_array($ticket['milestoneid'], $milestoneIds[$ticketGroup['groupValue']] ?? [])) {

                            $milestoneId = $ticket['milestoneid'];

                            if(!isset($milestoneCache[$milestoneId])) {
                                $milestoneCache[$milestoneId] = (array)$this->getTicket($milestoneId);
                            }
                            $ticketGroup['tickets'][] = $milestoneCache[$milestoneId] ;
                            $milestoneIds[$ticketGroup['groupValue']][] = $milestoneId;
                        }
                    }
                }
            }

            // Fetch the milestone data for all collected milestone IDs
//            if (! empty($milestoneIds)) {
//                foreach ($milestoneIds as $milestoneId) {
//                    $milestone = $this->getTicket($milestoneId);
//                    if ($milestone) {
//
//                        // Add milestone to the appropriate group
//                        foreach ($tickets as $groupKey => &$ticketGroup) {
//                            // Add the milestone to each group that contains tasks belonging to this milestone
//                            foreach ($ticketGroup['tickets'] as $key => $ticket) {
//                                if (! empty($ticket['milestoneid']) && $ticket['milestoneid'] == $milestoneId) {
//                                    // Create milestone entry if it doesn't exist in this group yet
//                                    $milestoneEntry = (array) $milestone;
//                                    $milestoneEntry['percentDone'] = $progress;
//
//                                    // Add to the beginning of the group
//                                    array_unshift($ticketGroup['tickets'], $milestoneEntry);
//                                    break; // Only add once per group
//                                }
//                            }
//                        }
//                    }
//                }
//            }


            // Process tickets to build hierarchical structure
            foreach ($tickets as $groupKey => &$ticketGroup) {
                if (isset($ticketGroup['tickets']) && is_array($ticketGroup['tickets'])) {
                    $ticketGroup['tickets'] = $this->buildTicketHierarchy($ticketGroup['tickets'], $sortingArray);
                }
            }



            $onTheClock = $this->timesheetService->isClocked(session('userdata.id'));
            $effortLabels = $this->getEffortLabels();
            $priorityLabels = $this->getPriorityLabels();
            $ticketTypes = $this->getTicketTypes();
            $statusLabels = $this->getAllStatusLabelsByUserId(session('userdata.id'));

            $milestoneArray = [];
            foreach ($tickets as &$ticketGroup) {
                foreach ($ticketGroup['tickets'] as $ticket) {
                    if (isset($milestoneArray[$ticket['projectId']])) {
                        continue;
                    } else {
                        $milestoneArray[$ticket['projectId']] = $this->getAllMilestones(['sprint' => '', 'type' => 'milestone', 'currentProject' => $ticket['projectId']]);
                    }
                }
            }

            $tickets = self::dispatch_filter('myTodoWidgetTasks', $tickets);

            return [
                'tickets' => $tickets,
                'onTheClock' => $onTheClock,
                'efforts' => $effortLabels,
                'priorities' => $priorityLabels,
                'ticketTypes' => $ticketTypes,
                'statusLabels' => $statusLabels,
                'milestones' => $milestoneArray,
                'allAssignedprojects' => $this->projectService->getProjectsAssignedToUser(
                    session('userdata.id'),
                    'open'
                ),
                'projectFilter' => $projectFilter,
                'groupBy' => $groupBy,
            ];
        }

        /**
         * Build a hierarchical structure of tickets based on dependencies and milestones
         *
         * @param  array  $tickets  Flat array of tickets
         * @param  array  $sortingArray  array of ticket ids and custom sorting index
         * @return array Hierarchical array of tickets
         */
        private function buildTicketHierarchy($tickets, array|bool $sortingArray = false)
        {
            $ticketMap = [];
            $rootTickets = [];

            // First pass: create a map of all tickets by ID
            foreach ($tickets as $ticket) {
                $ticket['children'] = [];

                if ($sortingArray) {
                    $sortIndex = collect($sortingArray)->firstWhere('id', $ticket['id']);
                    $ticket['sortIndex'] = $sortIndex['order'] ?? 10;
                }

                $ticketMap[$ticket['id']] = $ticket;
            }

            // Second pass: build the hierarchy
            foreach ($tickets as $ticket) {

                // If this ticket has a parent (depending ticket)
                if (! empty($ticket['dependingTicketId']) && isset($ticketMap[$ticket['dependingTicketId']])) {
                    // Add this ticket as a child of its parent
                    $ticketMap[$ticket['dependingTicketId']]['children'][] = &$ticketMap[$ticket['id']];
                } // If this ticket belongs to a milestone
                else {
                    if (! empty($ticket['milestoneid']) && isset($ticketMap[$ticket['milestoneid']])) {
                        // Add this ticket as a child of its milestone
                        $ticketMap[$ticket['milestoneid']]['children'][] = &$ticketMap[$ticket['id']];
                    } // Otherwise, it's a root ticket
                    else {
                        $rootTickets[] = &$ticketMap[$ticket['id']];
                    }
                }

            }

            // Sort the tickets at each level
            $this->sortTicketsRecursively($rootTickets);

            return $rootTickets;
        }

        /**
         * Sort tickets recursively at each level of the hierarchy
         *
         * @param  array  &$tickets  Array of tickets to sort
         */
        private function sortTicketsRecursively(&$tickets)
        {
            // Sort the current level by sortIndex
            usort($tickets, function ($a, $b) {
                return ($a['sortIndex'] ?? 0) - ($b['sortIndex'] ?? 0);
            });

            // Recursively sort children
            foreach ($tickets as &$ticket) {
                if (! empty($ticket['children'])) {
                    $this->sortTicketsRecursively($ticket['children']);
                }
            }
        }

        /**
         * Prepare ticket dates for database.
         *
         * @param  array  $values  The values of the ticket fields.
         * @return array The values of the ticket fields after preparing the dates.
         *
         * @api
         */
        public function prepareTicketDates(&$values)
        {
            // Prepare dates for db
            if (! empty($values['dateToFinish'])) {

                if ($values['dateToFinish'] instanceof CarbonImmutable) {
                    $values['dateToFinish'] = $values['dateToFinish']->formatDateTimeForDb();
                } else {
                    if (isset($values['timeToFinish']) && $values['timeToFinish'] != null) {
                        $values['dateToFinish'] = dtHelper()->parseUserDateTime($values['dateToFinish'], $values['timeToFinish'])->formatDateTimeForDb();
                        unset($values['timeToFinish']);
                    } else {
                        $values['dateToFinish'] = dtHelper()->parseUserDateTime($values['dateToFinish'], 'end')->formatDateTimeForDb();
                    }
                }
            }

            if (! empty($values['editFrom'])) {

                if ($values['editFrom'] instanceof CarbonImmutable) {
                    $values['editFrom'] = $values['editFrom']->formatDateTimeForDb();
                } else {
                    if (isset($values['timeFrom']) && $values['timeFrom'] != null) {
                        $values['editFrom'] = dtHelper()->parseUserDateTime(
                            $values['editFrom'],
                            $values['timeFrom'],
                            FromFormat::UserDateTime
                        )->formatDateTimeForDb();
                        unset($values['timeFrom']);
                    } else {
                        $values['editFrom'] = dtHelper()->parseUserDateTime(
                            $values['editFrom'],
                            'start'
                        )->formatDateTimeForDb();
                    }
                }
            }

            if (! empty($values['editTo'])) {

                if ($values['editTo'] instanceof CarbonImmutable) {
                    $values['editTo'] = $values['editTo']->formatDateTimeForDb();
                } else {
                    if (isset($values['timeTo']) && $values['timeTo'] != null) {
                        $values['editTo'] = dtHelper()->parseUserDateTime(
                            $values['editTo'],
                            $values['timeTo']
                        )->formatDateTimeForDb();
                        unset($values['timeTo']);
                    } else {
                        $values['editTo'] = dtHelper()->parseUserDateTime(
                            $values['editTo'],
                            'end'
                        )->formatDateTimeForDb();
                    }
                }
            }

            return $values;
        }

        /**
         * Find milestones that contain a specific term in their headline.
         *
         * @param  string  $term  The term to search for in the headline.
         * @param  int  $projectId  The ID of the project to search milestones in.
         * @return array The array of milestones that match the search term.
         *
         * @api
         */
        public function findMilestone(string $term, int $projectId)
        {

            $milestones = $this->getAllMilestones(['currentProject' => $projectId]);

            foreach ($milestones as $key => $milestone) {
                if (Str::contains($milestones[$key]['headline'], $term, ignoreCase: true)) {
                    $milestones[$key] = $this->prepareDatesForApiResponse($milestone);
                } else {
                    unset($milestones[$key]);
                }
            }

            return $milestones;
        }

        /**
         * Finds tickets based on search term, project ID, and optional user ID.
         *
         * @param  string  $term  The search term to match against ticket headlines.
         * @param  int  $projectId  The ID of the project to search within.
         * @param  int|null  $userId  (Optional) The ID of the user to limit the search to.
         * @return array An array of tickets matching the search criteria.
         *
         * @api
         */
        public function findTicket(string $term, int $projectId, ?int $userId)
        {

            $milestones = $this->getAll([
                'currentProject' => $projectId,
                'term' => $term,
                'users' => $userId,
            ]);

            foreach ($milestones as $key => $milestone) {
                $milestones[$key] = $this->prepareDatesForApiResponse($milestone);
            }

            return $milestones;
        }

        /**
         * Retrieve milestones for a specific project and user.
         *
         * @param  int|null  $projectId  The ID of the project (optional)
         * @param  int|null  $userId  The ID of the user (optional)
         * @return array|false An array of milestones or false if an error occurred
         *
         * @api
         */
        public function pollForNewAccountMilestones(?int $projectId = null, ?int $userId = null): array|false
        {
            $todos = $this->ticketRepository->getAllBySearchCriteria(
                [
                    'type' => 'milestone',
                    'currentProject' => $projectId,
                    'users' => $userId,
                ],
                'date'
            );

            foreach ($todos as $key => $todo) {
                $todos[$key] = $this->prepareDatesForApiResponse($todo);
            }

            return $todos;
        }

        /**
         * Polls for updated account milestones.
         *
         * Retrieves all milestones based on the provided search criteria and prepares the dates for API response.
         *
         * @param  int|null  $projectId  (optional) The ID of the project to filter milestones by.
         * @param  int|null  $userId  (optional) The ID of the user to filter milestones by.
         * @return array|false An array of milestones with prepared dates for API response, or false if an error occurs.
         *
         * @api
         */
        public function pollForUpdatedAccountMilestones(?int $projectId = null, ?int $userId = null): array|false
        {
            $milestones = $this->ticketRepository->getAllBySearchCriteria(
                [
                    'type' => 'milestone',
                    'currentProject' => $projectId,
                    'users' => $userId,
                ],
                'date'
            );

            foreach ($milestones as $key => $milestone) {
                $milestones[$key] = $this->prepareDatesForApiResponse($milestone);
                $milestones[$key]['id'] = $milestone['id'].'-'.$milestone['date'];
            }

            return $milestones;
        }

        /**
         * Polls for new account todos.
         *
         * Retrieves all account todos based on the provided search criteria. If no criteria are provided,
         * it will return all todos. Optionally, a project ID and a user ID can be specified to filter the todos.
         * It excludes todos of type "milestone".
         *
         * @param  int|null  $projectId  The ID of the project to filter the todos (optional).
         * @param  int|null  $userId  The ID of the user to filter the todos (optional).
         * @return array|false The retrieved todos as an array of associative arrays.
         *                     Returns false if an error occurs during retrieval.
         *
         * @api
         */
        public function pollForNewAccountTodos(?int $projectId = null, ?int $userId = null): array|false
        {
            $todos = $this->ticketRepository->getAllBySearchCriteria(
                [
                    'excludeType' => 'milestone',
                    'currentProject' => $projectId,
                    'users' => $userId,
                ],
                'date'
            );

            foreach ($todos as $key => $todo) {
                $todos[$key] = $this->prepareDatesForApiResponse($todo);
            }

            return $todos;
        }

        /**
         * Polls for updated account todos.
         *
         * @param  int|null  $projectId  The ID of the project (optional)
         * @param  int|null  $userId  The ID of the user (optional)
         * @return array|false An array of updated account todos or false if there was an error
         *
         * @api
         */
        public function pollForUpdatedAccountTodos(?int $projectId = null, ?int $userId = null): array|false
        {
            $todos = $this->ticketRepository->getAllBySearchCriteria(
                [
                    'excludeType' => 'milestone',
                    'currentProject' => $projectId,
                    'users' => $userId,
                ],
                'date'
            );

            foreach ($todos as $key => $todo) {
                $todos[$key] = $this->prepareDatesForApiResponse($todo);
                $todos[$key]['id'] = $todo['id'].'-'.$todo['date'];
            }

            return $todos;
        }

        private function prepareDatesForApiResponse($todo)
        {

            if (dtHelper()->isValidDateString($todo['date'])) {
                $todo['date'] = dtHelper()->parseDbDateTime($todo['date'])->toIso8601ZuluString();
            } else {
                $todo['date'] = null;
            }

            if (dtHelper()->isValidDateString($todo['dateToFinish'])) {
                $todo['dateToFinish'] = dtHelper()->parseDbDateTime($todo['dateToFinish'])->toIso8601ZuluString();
            } else {
                $todo['dateToFinish'] = null;
            }

            if (dtHelper()->isValidDateString($todo['editFrom'])) {
                $todo['editFrom'] = dtHelper()->parseDbDateTime($todo['editFrom'])->toIso8601ZuluString();
            } else {
                $todo['editFrom'] = null;
            }

            if (dtHelper()->isValidDateString($todo['editTo'])) {
                $todo['editTo'] = dtHelper()->parseDbDateTime($todo['editTo'])->toIso8601ZuluString();
            } else {
                $todo['editTo'] = null;
            }

            return $todo;
        }
    }
}

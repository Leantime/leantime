<?php

namespace Leantime\Domain\Projects\Services {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Support\FromFormat;
    use Leantime\Core\Template as TemplateCore;
    use Leantime\Core\Language as LanguageCore;
    use Leantime\Core\Mailer as MailerCore;
    use Leantime\Core\Events as EventCore;
    use Leantime\Core\Eventhelpers;
    use Leantime\Domain\Canvas\Repositories\Canvas;
    use Leantime\Domain\Notifications\Models\Notification;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
    use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
    use Leantime\Domain\Files\Repositories\Files as FileRepository;
    use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
    use Leantime\Domain\Leancanvas\Repositories\Leancanvas as LeancanvaRepository;
    use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
    use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas as GoalcanvaRepository;
    use DateTime;
    use DateInterval;
    use GuzzleHttp\Client;
    use Leantime\Domain\Notifications\Services\Messengers;
    use Leantime\Domain\Notifications\Services\Notifications as NotificationService;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Wiki\Repositories\Wiki;

    /**
     *
     */
    class Projects
    {
        use Eventhelpers;

        private TemplateCore $tpl;
        private ProjectRepository $projectRepository;
        private TicketRepository $ticketRepository;
        private SettingRepository $settingsRepo;
        private LanguageCore $language;
        private Messengers $messengerService;
        private NotificationService $notificationService;
        private FileRepository $filesRepository;

        /**
         * @param TemplateCore        $tpl
         * @param ProjectRepository   $projectRepository
         * @param TicketRepository    $ticketRepository
         * @param SettingRepository   $settingsRepo
         * @param FileRepository      $filesRepository
         * @param LanguageCore        $language
         * @param Messengers          $messengerService
         * @param NotificationService $notificationService
         */
        public function __construct(
            TemplateCore $tpl,
            ProjectRepository $projectRepository,
            TicketRepository $ticketRepository,
            SettingRepository $settingsRepo,
            FileRepository $filesRepository,
            LanguageCore $language,
            Messengers $messengerService,
            NotificationService $notificationService
        ) {
            $this->tpl = $tpl;
            $this->projectRepository = $projectRepository;
            $this->ticketRepository = $ticketRepository;
            $this->settingsRepo = $settingsRepo;
            $this->filesRepository = $filesRepository;
            $this->language = $language;
            $this->messengerService = $messengerService;
            $this->notificationService = $notificationService;
        }

        /**
         * @return mixed
         */
        public function getProjectTypes(): mixed
        {

            $types = array("project" => "label.project");

            $filtered = static::dispatch_filter("filterProjectType", $types);

            //Strategy & Program are protected types
            if (isset($filtered["strategy"])) {
                unset($filtered["strategy"]);
            }

            if (isset($filtered["program"])) {
                unset($filtered["program"]);
            }

            return $filtered;
        }

        /**
         * @param int $id
         * @return array|bool
         */
        public function getProject(int $id): bool|array
        {
            return $this->projectRepository->getProject($id);
        }

        //Gets project progress

        /**
         * @param $projectId
         * @return array
         * @throws \Exception
         */
        public function getProjectProgress($projectId): array
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


            $returnValue = array("percent" => $finalPercent, "estimatedCompletionDate" => $completionDate, "plannedCompletionDate" => '');
            if ($numberOfClosedTickets < 10) {
                $returnValue['estimatedCompletionDate'] = "<a href='" . BASE_URL . "/tickets/showAll' class='btn btn-primary'><span class=\"fa fa-thumb-tack\"></span> Complete more To-Dos to see that!</a>";
            } elseif ($finalPercent == 100) {
                $returnValue['estimatedCompletionDate'] = "<a href='" . BASE_URL . "/projects/showAll' class='btn btn-primary'><span class=\"fa fa-suitcase\"></span> This project is complete, onto the next!</a>";
            }
            return $returnValue;
        }

        /**
         * @param $projectId
         * @return array
         */
        public function getUsersToNotify($projectId): array
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

        /**
         * @param $projectId
         * @return array
         */
        public function getAllUserInfoToNotify($projectId): array
        {

            $users = $this->projectRepository->getUsersAssignedToProject($projectId);

            $to = array();

            //Only users that actually want to be notified
            foreach ($users as $user) {
                if ($user["notifications"] != 0 && ($user['username'] != session("userdata.mail"))) {
                    $to[] = $user;
                }
            }

            return $to;
        }

        //TODO Split and move to notifications

        /**
         * @param Notification $notification
         * @return void
         * @throws BindingResolutionException
         */
        public function notifyProjectUsers(Notification $notification): void
        {

            //Filter notifications
            $notification = EventCore::dispatch_filter("notificationFilter", $notification);

            //Email
            $users = $this->getUsersToNotify($notification->projectId);
            $projectName = $this->getProjectName($notification->projectId);

            $users = array_filter($users, function ($user) use ($notification) {
                return $user != $notification->authorId;
            }, ARRAY_FILTER_USE_BOTH);

            /*
            $mailer = app()->make(MailerCore::class);
            $mailer->setContext('notify_project_users');
            $mailer->setSubject($notification->subject);


            $mailer->setHtml($emailMessage);
            //$mailer->sendMail($users, session("userdata.name"));
            */

            $emailMessage = $notification->message;
            if ($notification->url !== false) {
                $emailMessage .= " <a href='" . $notification->url['url'] . "'>" . $notification->url['text'] . "</a>";
            }

            // NEW Queuing messaging system
            $queue = app()->make(QueueRepository::class);
            $queue->queueMessageToUsers($users, $emailMessage, $notification->subject, $notification->projectId);

            //Send to messengers
            $this->messengerService->sendNotificationToMessengers($notification, $projectName);

            //Notify users about mentions
            //Fields that should be parsed for mentions
            $mentionFields = array(
                "comments" => array("text"),
                "projects" => array("details"),
                "tickets" => array("description"),
                "canvas" => array("description", "data", "conclusion", "assumptions"),
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
                    (int)$entityId,
                    $notification->authorId,
                    $notification->url["url"]
                );
            }

            EventCore::dispatch_event("notifyProjectUsers", array("type" => "projectUpdate", "module" => $notification->module, "moduleId" => $entityId, "message" => $notification->message, "subject" => $notification->subject, "users" => $this->getAllUserInfoToNotify($notification->projectId), "url" => $notification->url['url']), "domain.services.projects");
        }

        /**
         * @param $projectId
         * @return mixed|void
         */
        public function getProjectName($projectId)
        {

            $project = $this->projectRepository->getProject($projectId);
            if ($project) {
                return $project["name"];
            }
        }

        /**
         * @param $userId
         * @return array|false
         */
        public function getProjectIdAssignedToUser($userId): false|array
        {

            $projects = $this->projectRepository->getUserProjectRelation($userId);

            if ($projects) {
                return $projects;
            } else {
                return false;
            }
        }

        /**
         * @param $userId
         * @param string   $projectStatus
         * @param $clientId
         * @return array
         */
        public function getProjectsAssignedToUser($userId, string $projectStatus = "open", $clientId = null): array
        {
            $projects = $this->projectRepository->getUserProjects($userId, $projectStatus, $clientId);

            if ($projects) {
                return $projects;
            } else {
                return [];
            }
        }


        /**
         * @param $currentParentId
         * @param array           $projects
         * @return array
         */
        public function findMyChildren($currentParentId, array $projects): array
        {

            $branch = [];

            foreach ($projects as $project) {
                if ($project['parent'] == $currentParentId) {
                    $children = $this->findMyChildren($project['id'], $projects);
                    if ($children) {
                        $project['children'] = $children;
                    }
                    $branch[] = $project;
                }
            }
            return $branch;
        }

        /**
         * Ensures all projects have a valid parent. If not the parent is removed.
         * This way a user can still access a project even if they don't have access to the child.
         *
         * @param array $projects
         * @return array
         */
        public function cleanParentRelationship(array $projects): array
        {

            $parents = [];
            foreach ($projects as $project) {
                $parents[$project['id']] = $project;
            }

            $cleanList = [];
            foreach ($projects as $project) {
                if (isset($parents[$project['parent']])) {
                    $cleanList[] = $project;
                } else {
                    $project['parent'] = 0;
                    $cleanList[] = $project;
                }
            }

            return $cleanList;
        }

        /**
         * @param $userId
         * @param string   $projectStatus
         * @param $clientId
         * @return array
         */
        public function getProjectHierarchyAssignedToUser($userId, string $projectStatus = "open", $clientId = null): array
        {

            //Load all projects user is assigned to
            $projects = $this->projectRepository->getUserProjects(
                userId: $userId,
                projectStatus: $projectStatus,
                clientId: (int)$clientId,
                accessStatus: "assigned"
            );
            $projects = self::dispatch_filter('afterLoadingProjects', $projects);


            //Build project hierarchy
            $projectsClean = $this->cleanParentRelationship($projects);
            $projectHierarchy = $this->findMyChildren(0, $projectsClean);
            $projectHierarchy = self::dispatch_filter('afterPopulatingProjectHierarchy', $projectHierarchy, array("projects" => $projects));

            //Get favorite projects
            $favorites = [];
            foreach ($projects as $project) {
                if (isset($project["isFavorite"]) && $project["isFavorite"] == 1) {
                    $favorites[] = $project;
                }
            }
            $favorites = self::dispatch_filter('afterPopulatingProjectFavorites', $favorites, array("projects" => $projects));

            return [
                "allAssignedProjects" => $projects,
                "allAssignedProjectsHierarchy" => $projectHierarchy,
                "favoriteProjects" => $favorites,
            ];
        }

        /**
         * @param $userId
         * @param string   $projectStatus
         * @param $clientId
         * @return array
         */
        public function getProjectHierarchyAvailableToUser($userId, string $projectStatus = "open", $clientId = null): array
        {

            //Load all projects user is assigned to
            $projects = $this->projectRepository->getUserProjects(
                userId: $userId,
                projectStatus: $projectStatus,
                clientId: (int)$clientId,
                accessStatus: "all"
            );
            $projects = self::dispatch_filter('afterLoadingProjects', $projects);


            //Build project hierarchy
            $projectsClean = $this->cleanParentRelationship($projects);
            $projectHierarchy = $this->findMyChildren(0, $projectsClean);
            $projectHierarchy = self::dispatch_filter('afterPopulatingProjectHierarchy', $projectHierarchy, array("projects" => $projects));

            $clients = $this->getClientsFromProjectList($projects);

            return [
                "allAvailableProjects" => $projects,
                "allAvailableProjectsHierarchy" => $projectHierarchy,
                "clients" => $clients,
            ];
        }


        /**
         * Gets all the clients available to a user.
         * Clients are determined by the projects
         * the user is assigned to.
         *
         * @param int    $userId        The ID of the user.
         * @param string $projectStatus (optional) The status of the projects to consider. Defaults to "open".
         * @return array An array of client objects.
         */
        public function getAllClientsAvailableToUser($userId, string $projectStatus = "open"): array
        {

            //Load all projects user is assigned to
            $projects = $this->projectRepository->getUserProjects(
                userId: $userId,
                projectStatus: $projectStatus,
                clientId: null,
                accessStatus: "all"
            );
            $projects = self::dispatch_filter('afterLoadingProjects', $projects);


            $clients = $this->getClientsFromProjectList($projects);

            return $clients;
        }

        /**
         * @param array $projects
         * @return array
         */
        public function getClientsFromProjectList(array $projects): array
        {

            $clients = [];
            foreach ($projects as $project) {
                if (!array_key_exists($project["clientId"], $clients)) {
                    $clients[$project["clientId"]] = array(
                        "name" => $project['clientName'],
                        "id" => $project["clientId"],
                    );
                }
            }

            return $clients;
        }

        /**
         * @param $userId
         * @param $projectId
         * @return mixed|string
         */
        public function getProjectRole($userId, $projectId): mixed
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

        /**
         * @param $userId
         * @return array|false
         */
        public function getProjectsUserHasAccessTo($userId): false|array
        {
            $projects = $this->projectRepository->getUserProjects(userId: $userId, accessStatus: "all");

            if ($projects) {
                return $projects;
            } else {
                return false;
            }
        }

        /**
         * Sets the current project in the session.
         * If a project ID is provided in the query string, it is used to set the current project.
         * If no project ID is provided, the last visited project or the first assigned project is set as the current project.
         * If no project is found, an exception is thrown.
         *
         * @return void
         * @throws \Exception when unable to set the current project
         */
        public function setCurrentProject(): void
        {

            if (isset($_GET['projectId']) === true) {
                $projectId = filter_var($_GET['projectId'], FILTER_SANITIZE_NUMBER_INT);

                if ($this->changeCurrentSessionProject($projectId) === true) {
                    return;
                }
            }

            if (
                session()->has("currentProject")
                && $this->changeCurrentSessionProject(session("currentProject"))
            ) {
                return;
            }

            session(["currentProject" => 0]);

            //If last project setting is set use that
            $lastProject = $this->settingsRepo->getSetting("usersettings." . session("userdata.id") . ".lastProject");
            if (
                !empty($lastProject)
                && $this->changeCurrentSessionProject($lastProject)
            ) {
                return;
            }

            $allProjects = $this->getProjectsAssignedToUser(session("userdata.id"));
            if (empty($allProjects)) {
                return;
            }

            if ($this->changeCurrentSessionProject($allProjects[0]['id']) === true) {
                return;
            }

            throw new \Exception("Error trying to set a project");
        }

        /**
         * Get current project id or 0 if no current project is set.
         *
         * @return int
         */
        public function getCurrentProjectId(): int
        {
            // Make sure that we never return a value less than 0.
            return max(0, (int) (session("currentProject") ?? 0));
        }

        /**
         * @param $projectId
         * @return bool
         * @throws BindingResolutionException
         */
        public function changeCurrentSessionProject($projectId): bool
        {
            if (!is_numeric($projectId)) {
                return false;
            }

            $projectId = (int)$projectId;

            session(["currentProjectName" => '']);

            if ($this->isUserAssignedToProject(session("userdata.id"), $projectId) === true) {
                //Get user project role

                $project = $this->getProject($projectId);

                if ($project) {
                    if (
                        session()->exists("currentProject") &&
                        session("currentProject") == $project['id']
                    ) {
                        return true;
                    }

                    $projectRole = $this->getProjectRole(session("userdata.id"), $projectId);

                    session(["currentProject" => $projectId]);

                    if (mb_strlen($project['name']) > 25) {
                        session(["currentProjectName" => mb_substr($project['name'], 0, 25) . " (...)"]);
                    } else {
                        session(["currentProjectName" => $project['name']]);
                    }

                    session(["currentProjectClient" => $project['clientName']]);

                    session(["userdata.projectRole" => '']);
                    if ($projectRole != '') {
                        session(["userdata.projectRole" => Roles::getRoleString($projectRole)]);
                    }

                    session(["currentSprint" => ""]);
                    session(["currentIdeaCanvas" => ""]);
                    session(["lastTicketView" => ""]);
                    session(["lastFilterdTicketTableView" => ""]);
                    session(["lastFilterdTicketKanbanView" => ""]);
                    session(["currentWiki" => '']);
                    session(["lastArticle" => ""]);

                    session(["currentSWOTCanvas" => ""]);
                    session(["currentLEANCanvas" => ""]);
                    session(["currentEMCanvas" => ""]);
                    session(["currentINSIGHTSCanvas" => ""]);
                    session(["currentSBCanvas" => ""]);
                    session(["currentRISKSCanvas" => ""]);
                    session(["currentEACanvas" => ""]);
                    session(["currentLBMCanvas" => ""]);
                    session(["currentOBMCanvas" => ""]);
                    session(["currentDBMCanvas" => ""]);
                    session(["currentSQCanvas" => ""]);
                    session(["currentCPCanvas" => ""]);
                    session(["currentSMCanvas" => ""]);
                    session(["currentRETROSCanvas" => ""]);
                    $this->settingsRepo->saveSetting("usersettings." . session("userdata.id") . ".lastProject", session("currentProject"));


                    $recentProjects =  $this->settingsRepo->getSetting("usersettings." . session("userdata.id") . ".recentProjects");
                    $recent = unserialize($recentProjects);

                    if (is_array($recent) === false) {
                        $recent = array();
                    }
                    $key = array_search(session("currentProject"), $recent);
                    if ($key !== false) {
                        unset($recent[$key]);
                    }
                    array_unshift($recent, session("currentProject"));

                    $recent = array_slice($recent, 0, 20);

                    $this->settingsRepo->saveSetting("usersettings." . session("userdata.id") . ".recentProjects", serialize($recent));

                    session()->forget("projectsettings");

                    self::dispatch_event("projects.setCurrentProject", $project);

                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        /**
         * @return void
         * @throws BindingResolutionException
         */
        public function resetCurrentProject(): void
        {

            session(["currentProject" => ""]);
            session(["currentProjectClient" => ""]);
            session(["currentProjectName" => ""]);

            session(["currentSprint" => ""]);
            session(["currentIdeaCanvas" => ""]);

            session(["currentSWOTCanvas" => ""]);
            session(["currentLEANCanvas" => ""]);
            session(["currentEMCanvas" => ""]);
            session(["currentINSIGHTSCanvas" => ""]);
            session(["currentSBCanvas" => ""]);
            session(["currentRISKSCanvas" => ""]);
            session(["currentEACanvas" => ""]);
            session(["currentLBMCanvas" => ""]);
            session(["currentOBMCanvas" => ""]);
            session(["currentDBMCanvas" => ""]);
            session(["currentSQCanvas" => ""]);
            session(["currentCPCanvas" => ""]);
            session(["currentSMCanvas" => ""]);
            session(["currentRETROSCanvas" => ""]);
            session()->forget("projectsettings");

            $this->settingsRepo->saveSetting("usersettings." . session("userdata.id") . ".lastProject", session("currentProject"));

            $this->setCurrentProject();
        }

        /**
         * @param $projectId
         * @return array
         */
        /**
         * @param $projectId
         * @return array
         */
        public function getUsersAssignedToProject($projectId): array
        {
            $users = $this->projectRepository->getUsersAssignedToProject($projectId);

            if ($users) {
                return $users;
            }

            return array();
        }

        /*
         * Checks if a user has access to a project. Either via direct assignment. Via client assignment or in case projects are available to all
         *
         * @param int $userId
         * @param int $projectId
         * @return bool
         * @throws BindingResolutionException
         */
        public function isUserAssignedToProject(int $userId, int $projectId): bool
        {

            return $this->projectRepository->isUserAssignedToProject($userId, $projectId);
        }

        /**
         * Checks if a user is directly assigned to a project.
         * Client assignments or projects available to entire organization are not considered true.
         *
         * @param int $userId
         * @param int $projectId
         * @return bool
         * @throws BindingResolutionException
         */
        public function isUserMemberOfProject(int $userId, int $projectId): bool
        {
            return $this->projectRepository->isUserMemberOfProject($userId, $projectId);
        }


        /**
         * Adds a new project to the system.
         *
         * @param array $values An associative array containing the project details.
         *                      - name: The name of the project.
         *                      - details: Additional details of the project (optional, default: '').
         *                      - clientId: The ID of the client associated with the project.
         *                      - hourBudget: The hour budget for the project (optional, default: 0).
         *                      - assignedUsers: Comma-separated list of user IDs assigned to the project (optional, default: '').
         *                      - dollarBudget: The dollar budget for the project (optional, default: 0).
         *                      - psettings: The settings for the project (optional, default: 'restricted').
         *                      - type: The type of the project (optional, default: 'project').
         *                      - start: The start date of the project in user format (YYYY-MM-DD).
         *                      - end: The end date of the project in user format (YYYY-MM-DD).
         * @return int|false The ID of the newly added project
         */
        public function addProject(array $values): int|false
        {
            $values = array(
                "name" => $values['name'],
                'details' => $values['details'] ?? '',
                'clientId' => $values['clientId'],
                'hourBudget' => $values['hourBudget'] ?? 0,
                'assignedUsers' => $values['assignedUsers'] ?? '',
                'dollarBudget' => $values['dollarBudget'] ?? 0,
                'psettings' => $values['psettings'] ?? 'restricted',
                'type' => "project",
                'start' => $values['start'],
                'end' => $values['end'],
            );
            if ($values['start'] != null) {
                $values['start'] = format(value: $values['start'], fromFormat: FromFormat::UserDateStartOfDay)->isoDateTime();
            }
            if ($values['end'] != null) {
                $values['end'] = format($values['end'], fromFormat: FromFormat::UserDateEndOfDay)->isoDateTime();
            }
            return $this->projectRepository->addProject($values);
        }

        /**
         * @param int    $projectId
         * @param int    $clientId
         * @param string $projectName
         * @param string $userStartDate
         * @param bool   $assignSameUsers
         * @return bool|int
         * @throws BindingResolutionException
         */
        public function duplicateProject(int $projectId, int $clientId, string $projectName, string $userStartDate, bool $assignSameUsers): bool|int
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

            if ($assignSameUsers) {
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
                        'userId' => session("userdata.id"),
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
                        'milestoneid' => '',
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
                        'userId' => session("userdata.id"),
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

            //Ideas
            $this->duplicateCanvas(
                repository: IdeaRepository::class,
                originalProjectId: $projectId,
                newProjectId: $newProjectId
            );

            $this->duplicateCanvas(
                repository: GoalcanvaRepository::class,
                originalProjectId: $projectId,
                newProjectId: $newProjectId
            );

            $this->duplicateCanvas(
                repository: Wiki::class,
                originalProjectId: $projectId,
                newProjectId: $newProjectId,
                canvasTypeName: "wiki"
            );

            $this->duplicateCanvas(
                repository: LeancanvaRepository::class,
                originalProjectId: $projectId,
                newProjectId: $newProjectId
            );

            return $newProjectId;
        }

        /**
         * @param string $repository
         * @param int    $originalProjectId
         * @param int    $newProjectId
         * @param string $canvasTypeName
         * @return bool
         * @throws BindingResolutionException
         */
        private function duplicateCanvas(string $repository, int $originalProjectId, int $newProjectId, string $canvasTypeName = ''): bool
        {

            $canvasIdList = [];
            $canvasRepo = app()->make($repository);
            $canvasBoards = $canvasRepo->getAllCanvas($originalProjectId, $canvasTypeName);

            foreach ($canvasBoards as $canvas) {
                $canvasValues = array(
                    "title" => $canvas['title'],
                    "author" => session("userdata.id"),
                    "projectId" => $newProjectId,
                    "description" => $canvas['description'] ?? '',
                );

                $newCanvasId = $canvasRepo->addCanvas($canvasValues, $canvasTypeName);
                $canvasIdList[$canvas['id']] = $newCanvasId;

                $canvasItems = $canvasRepo->getCanvasItemsById($canvas['id']);

                if ($canvasItems && count($canvasItems) > 0) {
                    //Build parent Array
                    //oldId => newId
                    $idMap = array();

                    foreach ($canvasItems as $item) {

                        $milestoneId = "";
                        if (isset($idMap[$item['milestoneId']])) {
                            $milestoneId = $idMap[$item['milestoneId']];
                        }

                        $canvasItemValues = array(
                            "description" => $item['description'] ?? '',
                            "assumptions" => $item['assumptions'] ?? '',
                            "data" => $item['data'] ?? '',
                            "conclusion" => $item['conclusion'] ?? '',
                            "box" => $item['box'] ?? '',
                            "author" => $item['author'] ?? '',

                            "canvasId" => $newCanvasId,
                            "sortindex" => $item['sortindex'] ?? '',
                            "status" => $item['status'] ?? '',
                            "relates" => $item['relates'] ?? '',
                            "milestoneId" => $milestoneId,
                            "title" => $item['title'] ?? '',
                            "parent" => $item['parent'] ?? '',
                            "featured" => $item['featured'] ?? '',
                            "tags" => $item['tags'] ?? '',
                            "kpi" => $item['kpi'] ?? '',
                            "data1" => $item['data1'] ?? '',
                            "data2" => $item['data2'] ?? '',
                            "data3" => $item['data3'] ?? '',
                            "data4" => $item['data4'] ?? '',
                            "data5" => $item['data5'] ?? '',
                            "startDate" => '',
                            "endDate" => '',
                            "setting" => $item['setting'] ?? '',
                            "metricType" => $item['metricType'] ?? '',
                            "startValue" => '',
                            "currentValue" => '',
                            "endValue" => $item['endValue'] ?? '',
                            "impact" => $item['impact'] ?? '',
                            "effort" => $item['effort'] ?? '',
                            "probability" => $item['probability'] ?? '',
                            "action" => $item['action'] ?? '',
                            "assignedTo" => $item['assignedTo'] ?? '',
                        );

                        $newId = $canvasRepo->addCanvasItem($canvasItemValues);
                        $idMap[$item['id']] = $newId;
                    }

                    //Now fix relates to and parent relationships
                    $newCanvasItems = $canvasRepo->getCanvasItemsById($newCanvasId);
                    foreach ($canvasItems as $newItem) {
                        $newCanvasItemValues = array(
                            "relates" => $idMap[$newItem['relates']] ?? '',
                            "parent" => $idMap[$newItem['parent']] ?? '',
                        );

                        $canvasRepo->patchCanvasItem($newItem['id'], $newCanvasItemValues);
                    }
                }
            }

            return true;
        }

        /**
         * @param $id
         * @return array
         */
        public function getProjectUserRelation($id): array
        {
            return $this->projectRepository->getProjectUserRelation($id);
        }

        /**
         * @param $id
         * @param $params
         * @return bool
         */
        public function patch($id, $params): bool
        {
            return $this->projectRepository->patch($id, $params);
        }

        /**
         * @param $id
         * @return mixed
         * @throws BindingResolutionException
         */
        public function getProjectAvatar($id): mixed
        {
            $avatar = $this->projectRepository->getProjectAvatar($id);
            $avatar = static::dispatch_filter("afterGettingAvatar", $avatar, array("projectId" => $id));
            return $avatar;
        }

        /**
         * @param $file
         * @param $project
         * @return null
         * @throws BindingResolutionException
         */
        public function setProjectAvatar($file, $project): bool
        {
            return $this->projectRepository->setPicture($file, $project);
        }

        public function getAllProjects()
        {
            return $this->projectRepository->getAll();
        }

        /**
         * @param $projectId
         * @return array
         * @throws BindingResolutionException
         */
        public function getProjectSetupChecklist($projectId): array
        {
            $progressSteps = array(
                "define" => array(
                    "title" => "label.define",
                    "description" => "checklist.define.description",
                    "tasks" => array(
                        "description" => array(
                            "title" => "label.projectDescription",
                            "status" => "",
                            "link" => BASE_URL . "/projects/showProject/" . session("currentProject") . "",
                            "description" => "checklist.define.tasks.description",
                        ),
                        "defineTeam" => array(
                            "title" => "label.defineTeam",
                            "status" => "",
                            "link" => BASE_URL . "/projects/showProject/" . session("currentProject") . "#team",
                            "description" => "checklist.define.tasks.defineTeam",
                        ),
                        "createBlueprint" => array(
                            "title" => "label.createBlueprint",
                            "status" => "",
                            "link" => BASE_URL . "/strategy/showBoards/",
                            "description" => "checklist.define.tasks.createBlueprint",
                        ),
                    ),
                    "status" => '',
                ),
                "goals" => array(
                    "title" => "label.setGoals",
                    "description" => "checklist.goals.description",
                    "tasks" => array(
                        "setGoals" => array(
                            "title" => "label.setGoals",
                            "status" => "",
                            "link" => BASE_URL . "/goalcanvas/dashboard",
                            "description" => "checklist.goals.tasks.setGoals",
                        ),
                    ),
                    "status" => '',
                ),
                "timeline" => array(
                    "title" => "label.setTimeline",
                    "description" => "checklist.timeline.description",
                    "tasks" => array(
                        "createMilestones" => array(
                            "title" => "label.createMilestones",
                            "status" => "",
                            "link" => BASE_URL . "/tickets/roadmap",
                            "description" => "checklist.timeline.tasks.createMilestones",
                        ),

                    ),
                    "status" => '',
                ),
                "implementation" => array(
                    "title" => "label.implement",
                    "description" => "checklist.implementation.description",
                    "tasks" => array(
                        "createTasks" =>  array(
                            "title" => "label.createTasks",
                            "status" => "", "link" => BASE_URL . "/tickets/showAll",
                            "description" => "checklist.implementation.tasks.createTasks ",
                        ),
                        "finish80percent" =>  array(
                            "title" => "label.finish80percent",
                            "status" => "",
                            "link" => BASE_URL . "/reports/show",
                            "description" => "checklist.implementation.tasks.finish80percent",
                        ),
                    ),
                    "status" => '',
                ),
            );

            //Todo determine tasks that are done.
            $project = $this->getProject($projectId);
            //Project Description
            if ($project['details'] != '') {
                $progressSteps["define"]["tasks"]["description"]["status"] = "done";
            }

            if ($project['numUsers'] > 1) {
                $progressSteps["define"]["tasks"]["defineTeam"]["status"] = "done";
            }

            if ($project['numDefinitionCanvas'] >= 1) {
                $progressSteps["define"]["tasks"]["createBlueprint"]["status"] = "done";
            }

            $goals = app()->make(GoalcanvaRepository::class);
            $allCanvas = $goals->getAllCanvas($projectId);

            $totalGoals = 0;
            foreach ($allCanvas as $goalsCanvas) {
                $totalGoals = $totalGoals + $goalsCanvas['boxItems'];
            }
            if ($totalGoals > 0) {
                $progressSteps["define"]["goals"]["setGoals"]["status"] = "done";
            }

            if ($project['numberMilestones'] >= 1) {
                $progressSteps["timeline"]["tasks"]["createMilestones"]["status"] = "done";
            }

            if ($project['numberOfTickets'] >= 1) {
                $progressSteps["implementation"]["tasks"]["createTasks"]["status"] = "done";
            }

            $percentDone = $this->getProjectProgress($projectId);
            if ($percentDone['percent'] >= 80) {
                $progressSteps["implementation"]["tasks"]["finish80percent"]["status"] = "done";
            }

            //Add overrides
            if (!$stepsCompleted = $this->settingsRepo->getSetting("projectsettings.$projectId.stepsComplete")) {
                $stepsCompleted = [];
            } else {
                $stepsCompleted = unserialize($stepsCompleted);
            }

            $stepsCompleted = array_map(fn ($status) => 'done', $stepsCompleted);

            $halfStep = (1 / count($progressSteps)) / 2 * 100;
            $position = 0;
            $debug = [];
            foreach ($progressSteps as $name => $step) {
                // set the "left" css position for the step on the progress bar
                $progressSteps[$name]['positionLeft'] = ($position++ / count($progressSteps) * 100) + $halfStep;

                // set the status based on the stepsCompleted setting
                data_set(
                    $progressSteps,
                    "$name.tasks",
                    collect(data_get($progressSteps, "$name.tasks"))
                        ->map(function ($task, $key) use ($stepsCompleted) {
                            $task['status'] = $stepsCompleted[$key] ?? '';
                            return $task;
                        })
                        ->toArray()
                );

                // check for any open tasks
                if (in_array('', data_get($progressSteps, "$name.tasks.*.status"))) {
                    if (
                        $name == array_key_first($progressSteps)
                        || ($previousValue['stepType'] ?? '') == 'complete'
                    ) {
                        $progressSteps[$name]['stepType'] = 'current';
                    } else {
                        $progressSteps[$name]['stepType'] = '';
                    }

                    $progressSteps[$name]['status'] = '';
                    $previousValue = $progressSteps[$name];
                    continue;
                }

                // otherwise, set the step as completed
                $progressSteps[$name]['status'] = 'done';
                if (
                    !in_array($previousValue['stepType'] ?? null, ['current', ''])
                    || $name == array_key_first($progressSteps)
                ) {
                    $progressSteps[$name]['stepType'] = 'complete';
                } else {
                    $progressSteps[$name]['stepType'] = '';
                }
                $previousValue = $progressSteps[$name];
            }

            // Set the Percentage done of the progress Bar
            $numberDone = count(array_filter(data_get($progressSteps, '*.stepType'), fn ($status) => $status == 'complete'));
            $stepsTotal = count($progressSteps);
            $percentDone = $numberDone == $stepsTotal ? 100 : $numberDone / $stepsTotal * 100 + $halfStep;

            return [
                $progressSteps,
                $percentDone,
            ];
        }


        /**
         * @param $stepsComplete
         * @param $projectId
         * @return void
         */
        public function updateProjectProgress($stepsComplete, $projectId): void
        {
            if (empty($stepsComplete)) {
                return;
            }

            $stepsDoneArray = [];
            if (is_string($stepsComplete)) {
                parse_str($stepsComplete, $stepsDoneArray);
            } else {
                $stepsDoneArray = $stepsComplete;
            }

            $this->settingsRepo->saveSetting(
                "projectsettings.$projectId.stepsComplete",
                serialize($stepsDoneArray)
            );
        }

        /**
         * Edits the project relations of a user.
         *
         * @param int $id The ID of the user.
         * @param array $projects An array of project IDs to be assigned to the user.
         * @return bool Returns true if the project relations were successfully edited, false otherwise.
         */
        public function editUserProjectRelations($id, $projects): bool
        {
            return $this->projectRepository->editUserProjectRelations($id, $projects);
        }

        /**
         * Returns the project ID by its name from the given array of projects.
         *
         * @param array  $allProjects An array of projects.
         * @param string $projectName The name of the project to search for.
         * @return int|bool The ID of the project if found, or false if not found.
         */
        public function getProjectIdbyName($allProjects, $projectName)
        {
            foreach ($allProjects as $project) {
                if (strtolower(trim($project['name'])) == strtolower(trim($projectName))) {
                    return $project['id'];
                }
            }
            return false;
        }

        /**
         * @param $params
         * @return false|void
         */
        public function updateProjectSorting($params)
        {
            //ticketId: sortIndex
            foreach ($params as $id => $sortKey) {
                if ($this->projectRepository->patch($id, ["sortIndex" => $sortKey * 100]) === false) {
                    return false;
                }
            }
        }

        /**
         * Edits a project with the given values.
         *
         * @param mixed $values The values to update the project with.
         * @param int   $id     The ID of the project to edit.
         *
         * @return void
         */
        public function editProject($values, $id)
        {
            $this->projectRepository->editProject($values, $id);
        }

        /**
         * @param $params
         * @param $handler
         * @return bool
         */
        public function updateProjectStatusAndSorting($params, $handler = null): bool
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

                            $this->projectRepository->patch($id, ["sortIndex" => $key * 100, "state" => $status]);
                        }
                    }
                }
            }

            return true;
        }

        /**
         * Gets all the projects a company manager has access to.
         * Includes all projects within a client + all assigned projects
         *
         * @param int $userId
         * @param int $clientId
         * @return array
         */
        public function getClientManagerProjects(int $userId, int $clientId): array
        {

            $clientProjects = $this->projectRepository->getClientProjects($clientId);
            $userProjects = $this->projectRepository->getUserProjects($userId);

            $allProjects = [];

            foreach ($clientProjects as $project) {
                if (isset($allProjects[$project['id']]) === false) {
                    $allProjects[$project['id']] = $project;
                }
            }

            foreach ($userProjects as $project) {
                if (isset($allProjects[$project['id']]) === false) {
                    $allProjects[$project['id']] = $project;
                }
            }

            return $userProjects;
        }

        /**
         * @param bool $showClosedProjects
         * @return array
         */
        public function getAll(bool $showClosedProjects = false): array
        {
            return $this->projectRepository->getAll($showClosedProjects);
        }

        public function pollForUpdatedProjects(): array
        {
            $projects = $this->projectRepository->getAll(false);

            foreach ($projects as $key => $project) {
                $projects[$key]['id'] = $project['id'] . '-' . $project['modified'];
            }

            return $projects;
        }
    }
}

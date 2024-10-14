<?php

namespace Leantime\Domain\Projects\Services;

use DateInterval;
use DateTime;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Events\EventDispatcher as EventCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\FromFormat;
use Leantime\Core\Template as TemplateCore;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Files\Repositories\Files as FileRepository;
use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas as GoalcanvaRepository;
use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
use Leantime\Domain\Leancanvas\Repositories\Leancanvas as LeancanvaRepository;
use Leantime\Domain\Notifications\Models\Notification;
use Leantime\Domain\Notifications\Services\Messengers;
use Leantime\Domain\Notifications\Services\Notifications as NotificationService;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Wiki\Repositories\Wiki;

/**
 * The Projects class is responsible for managing projects and project-related operations.
 *
 * @package Domain\Projects
 *
 */
class Projects
{
    use DispatchesEvents;

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
     *
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
     * Gets the project types.
     *
     * @return mixed
     *
     * @api
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
     * Gets the project with the given ID.
     *
     * @param int $id The ID of the project to retrieve.
     * @return bool|array Returns the project data as an associative array if the project exists, otherwise returns false.
     *
     * @api
     */
    public function getProject(int $id): bool|array
    {
        return $this->projectRepository->getProject($id);
    }

    //Gets project progress

    /**
     * Gets the progress of a project.
     * Calculates the completion percentage, estimated completion date,
     * and planned completion date of the project.
     *
     * @param int $projectId The ID of the project.
     * @return array The progress of the project.
     *
     * @api
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
     * Gets an array of user IDs to notify for a given project.
     *
     * @param int $projectId The ID of the project to get users to notify for.
     * @return array An array of user IDs.
     *
     * @api
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
     * Gets all the users who need to be notified for a given project.
     *
     * @param int $projectId The ID of the project.
     * @return array An array of users to notify.
     *
     * @api
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
     * Notifies the users associated with a project about a notification.
     *
     * @param Notification $notification The notification object to send.
     * @return void
     *
     * @api
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
        *
 * @api
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

        /**
         * This event is fired to notify project users of important updates.
         * An event "notifyProjectUsers" is dispatched with an array of variables required for the notification.
         * These variables include the type of update, module affected, entity ID, message and subject of notification,
         * users to be notified, and url if present. This event belongs to the "domain.services.projects" context.
         *
         * @event notifyProjectUsers
         *
         * @param string $type The type of update. E.g., "projectUpdate"
         * @param string $module The name of the module affected by the update.
         * @param int $moduleId The ID of the entity affected by the update.
         * @param string $message The content of the notification message.
         * @param string $subject The subject of the notification message.
         * @param array $users The users to be notified about this update. Retrieved by the 'getAllUserInfoToNotify' method.
         * @param string|null $url The url leading to the update if any.
         * @context domain.services.projects
         */
         EventCore::dispatch_event("notifyProjectUsers", array("type" => "projectUpdate", "module" => $notification->module, "moduleId" => $entityId, "message" => $notification->message, "subject" => $notification->subject, "users" => $this->getAllUserInfoToNotify($notification->projectId), "url" => $notification->url['url']), "domain.services.projects");
    }

    /**
     * Retrieves the name of a project based on its ID.
     *
     * @param int $projectId The ID of the project.
     * @return string|null The name of the project, or null if the project does not exist.
     *
     * @api
     */
    public function getProjectName($projectId)
    {

        $project = $this->projectRepository->getProject($projectId);
        if ($project) {
            return $project["name"];
        }
    }

    /**
     * Gets the project IDs assigned to a specified user.
     *
     * @param int $userId The ID of the user.
     * @return false|array The project IDs assigned to the user, or false if no projects are found.
     *
     * @api
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
     * Gets projects assigned to a user.
     *
     * @param int $userId The ID of the user.
     * @param string $projectStatus The status of the projects. Defaults to "open".
     * @param int|null $clientId The ID of the client. Defaults to null.
     * @return array The projects assigned to the user.
     *
     * @api
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
     * Finds all children projects for a given parent project.
     *
     * @param mixed $currentParentId The ID of the current parent project.
     * @param array $projects An array of projects to search for children.
     * @return array An array of children projects found.
     *
     * @api
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
     * Cleans the parent relationship in the given array of projects.
     * Removes projects that have a parent project that does not exist in the array.
     * Assigns a parent id of 0 to projects that have no parent.
     *
     * @param array $projects An array of projects
     * @return array The cleaned array of projects
     *
     * @api
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
     * Gets the hierarchy of projects assigned to a user.
     *
     * @param int $userId The ID of the user.
     * @param string $projectStatus The project status. Default is "open".
     * @param int|null $clientId The ID of the client. Default is null.
     *
     * @return array An array containing the assigned projects, the project hierarchy, and the favorite projects.
     *
     * @api
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
     * Gets the project hierarchy available to a user.
     *
     * @param int $userId The ID of the user.
     * @param string $projectStatus The status of the projects to retrieve. Defaults to "open".
     * @param int|null $clientId The ID of the client. Defaults to null.
     * @return array Returns an array containing the following keys:
     *               - "allAvailableProjects": An array of all projects available to the user.
     *               - "allAvailableProjectsHierarchy": An array representing the project hierarchy available to the user.
     *               - "clients": An array of clients associated with the projects available to the user.
     *
     * @api
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
     *
     * @param int $userId The ID of the user.
     * @param string $projectStatus The status of the projects to be considered. Defaults to "open".
     * @return array An array of clients available to the user.
     *
     * @api
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
     *
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
     * Gets the role of a user in a specific project.
     *
     * @param mixed $userId The user ID.
     * @param mixed $projectId The project ID.
     * @return mixed The role of the user in the project (string) or an empty string if the user is not assigned to the project or if the project role is not defined.
     *
     * @api
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
     * Gets the projects that a user has access to.
     *
     * @param int $userId The ID of the user.
     * @return array|false The array of projects if the user has access, false otherwise.
     *
     * @api
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
     * Sets the current project for the user.
     * If projectId is present in the query string, it sets the project based on that.
     * If projectId is not present, it checks if the currentProject is set in the session and sets the project based on that.
     * If currentProject is not set, it sets the currentProject to 0.
     * If lastProject setting is set in the user's settings, it sets the project based on that.
     * If lastProject setting is not set, it sets the currentProject to the first project assigned to the user.
     * If no projects are assigned to the user, it throws an Exception.
     *
     * @return void
     *
     * @throws \Exception
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
     * Gets the current project ID.
     * If the session variable "currentProject" is set, it returns its integer value.
     * Otherwise, it returns 0.
     *
     * @return int The current project ID.
     *
     */
    public function getCurrentProjectId(): int
    {
        // Make sure that we never return a value less than 0.
        return max(0, (int) (session("currentProject") ?? 0));
    }

    /**
     * Change the current session project to the specified projectId.
     *
     * @param mixed $projectId The ID of the project to set as current.
     *
     * @return bool Returns true if the current project is successfully changed, false otherwise.
     *
     * @api
     */
    public function changeCurrentSessionProject($projectId): bool
    {
        if (!is_numeric($projectId)) {
            return false;
        }

        $projectId = (int)$projectId;

        if (
            session()->exists("currentProject") &&
            session("currentProject") == $projectId
        ) {
            return true;
        }

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
     * Resets the current project by clearing all session data related to the project.
     *
     * @return void
     *
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
     * Gets all users that have access to a project.
     * For direct access only set the teamOnly flag to true
     *
     * @param int $projectId The ID of the project.
     * @return array An array of users assigned to the project.
     *
     * @api
     */
    public function getUsersAssignedToProject($projectId, $teamOnly = false): array
    {
        $users = $this->projectRepository->getUsersAssignedToProject($projectId, $teamOnly);

        foreach ($users as $key => $user) {

             if(dtHelper()->isValidDateString($user['modified'])) {
                 $users[$key]['modified'] = dtHelper()->parseDbDateTime($user['modified'])->toIso8601ZuluString();
             }else{
                 $users[$key]['modified'] = null;
             }
        }

        if ($users) {
            return $users;
        }

        return array();
    }

    /**
     * Checks if a user is assigned to a particular project.
     *
     * @param int $userId The ID of the user being checked.
     * @param int $projectId The ID of the project being checked.
     * @return bool Returns true if the user is assigned to the project, false otherwise.
     *
     * @api
     */
    public function isUserAssignedToProject(int $userId, int $projectId): bool
    {

        return $this->projectRepository->isUserAssignedToProject($userId, $projectId);
    }

    /**
     * Checks if a user is a member of a specific project.
     *
     * @param int $userId - The ID of the user.
     * @param int $projectId - The ID of the project.
     * @return bool - Returns true if the user is a member of the project, otherwise false.
     *
     * @api
     */
    public function isUserMemberOfProject(int $userId, int $projectId): bool
    {
        return $this->projectRepository->isUserMemberOfProject($userId, $projectId);
    }


    /**
     * Adds a new project.
     *
     * @param array $values The project data.
     *   - name: string (required) The name of the project.
     *   - details: string (optional) Additional details about the project.
     *   - clientId: int (required) The ID of the client associated with the project.
     *   - hourBudget: int (optional) The hour budget for the project (defaults to 0).
     *   - assignedUsers: string (optional) The list of assigned users (defaults to an empty string).
     *   - dollarBudget: int (optional) The dollar budget for the project (defaults to 0).
     *   - psettings: string (optional) The project settings (defaults to 'restricted').
     *   - type: string (fixed value 'project') The type of the project.
     *   - start: string|null The start date of the project in user format or null.
     *   - end: string|null The end date of the project in user format or null.
     * @return int|false The ID of the added project, or false if the project could not be added.
     *
     * @api
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
     * Duplicates a project with the specified details.
     *
     * @param int $projectId The ID of the project to duplicate.
     * @param int $clientId The ID of the client for the duplicate project.
     * @param string $projectName The name of the duplicate project.
     * @param string $userStartDate The start date of the duplicate project in the format specified by the language setting.
     * @param bool $assignSameUsers Whether to assign the same users as the original project.
     * @return bool|int Returns true if the project was successfully duplicated, or the ID of the new project if successful.
     *
     * @api
     */
    public function duplicateProject(int $projectId, int $clientId, string $projectName, string $userStartDate, bool $assignSameUsers): bool|int
    {

        $startDate = datetime::createFromFormat($this->language->__("language.dateformat"), $userStartDate);

        //Ignoring
        //Comments, files, timesheets, personalCalendar EventDispatcher
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
     * Duplicate a canvas from one project to another.
     *
     * @param string $repository The repository class to use for CRUD operations
     * @param int $originalProjectId The ID of the original project
     * @param int $newProjectId The ID of the new project
     * @param string $canvasTypeName The canvas type name (optional)
     * @return bool True if the canvas is duplicated successfully, false otherwise
     *
     * @api
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
     * Retrieves the relation between a project and its users.
     *
     * @param int $id The ID of the project.
     * @return array The relation between the project and its users.
     *
     * @api
     */
    public function getProjectUserRelation($id): array
    {
        return $this->projectRepository->getProjectUserRelation($id);
    }

    /**
     * Updates a project with the given parameters.
     *
     * @param int $id The ID of the project.
     * @param array $params The parameters to update the project.
     * @return bool Returns true if the project was successfully updated, false otherwise.
     *
     * @api
     */
    public function patch($id, $params): bool
    {
        return $this->projectRepository->patch($id, $params);
    }

    /**
     * Retrieves the avatar for a project.
     *
     * @param mixed $id The ID of the project.
     * @return mixed The avatar for the project.
     *
     * @api
     */
    public function getProjectAvatar($id): mixed
    {
        $avatar = $this->projectRepository->getProjectAvatar($id);
        $avatar = self::dispatch_filter("afterGettingAvatar", $avatar, array("projectId" => $id));
        return $avatar;
    }

    /**
     * Sets the avatar for a project.
     *
     * @param mixed $file The file containing the avatar.
     * @param mixed $project The project object.
     * @return bool Indicates whether the avatar was successfully set.
     *
     * @api
     */
    public function setProjectAvatar($file, $project): bool
    {
        return $this->projectRepository->setPicture($file, $project);
    }

    /**
     * Retrieves all projects.
     *
     * @return array The projects.
     * @api
     */
    public function getAllProjects()
    {
        return $this->projectRepository->getAll();
    }

    /**
     * Retrieves the setup checklist for a project.
     *
     * @param int $projectId The ID of the project.
     * @return array The setup checklist for the project
     *
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

            /*
            if ($project['numUsers'] > 1) {
                $progressSteps["define"]["tasks"]["defineTeam"]["status"] = "done";
            }

            if ($project['numDefinitionCanvas'] >= 1) {
                $progressSteps["define"]["tasks"]["createBlueprint"]["status"] = "done";
            }*/

        $goals = app()->make(GoalcanvaRepository::class);
        $allCanvas = $goals->getAllCanvas($projectId);

        $totalGoals = 0;
        foreach ($allCanvas as $goalsCanvas) {
            $totalGoals = $totalGoals + $goalsCanvas['boxItems'];
        }
        if ($totalGoals > 0) {
            $progressSteps["define"]["goals"]["setGoals"]["status"] = "done";
        }

            /*
            if ($project['numberMilestones'] >= 1) {
                $progressSteps["timeline"]["tasks"]["createMilestones"]["status"] = "done";
            }

            if ($project['numberOfTickets'] >= 1) {
                $progressSteps["implementation"]["tasks"]["createTasks"]["status"] = "done";
            }*/

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
     * Updates the progress of a project.
     *
     * @param string|array $stepsComplete The steps completed for the project.
     * @param int $projectId The ID of the project.
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
     * @param array $projects The projects to be edited.
     * @return bool True if the project relations were successfully edited, false otherwise.
     *
     * @api
     *
     */
    public function editUserProjectRelations($id, $projects): bool
    {
        return $this->projectRepository->editUserProjectRelations($id, $projects);
    }

    /**
     * Retrieves the ID of a project by its name.
     *
     * @param array $allProjects The array of all projects.
     * @param string $projectName The name of the project to retrieve the ID for.
     * @return mixed The ID of the project if found, or false if not found.
     *
     *  @api
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
     * Updates the sorting of multiple projects.
     *
     * @param array $params The array containing the project IDs as keys and their corresponding sort index as values (ticketId: sortIndex).
     * @return bool Returns true if the sorting update was successful, false otherwise.
     *
     */
    public function updateProjectSorting($params): bool
    {
        //ticketId: sortIndex
        foreach ($params as $id => $sortKey) {
            if ($this->projectRepository->patch($id, ["sortIndex" => $sortKey * 100]) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Edits a project.
     *
     * @param mixed $values The values to be updated in the project.
     * @param int $id The ID of the project to be edited.
     * @return void
     *
     *  @api
     */
    public function editProject($values, $id)
    {
        $this->projectRepository->editProject($values, $id);
    }

    /**
     * Updates the status and sorting of projects.
     *
     * @param array $params An associative array representing the project status and sorting.
     *                      The key is the status and the value is the serialized project list.
     * @param null $handler Optional parameter for handling the project update process.
     * @return bool Returns true if the update process is successful, false otherwise.
     *
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
     * Retrieves the projects for a client manager.
     *
     * @param int $userId The ID of the user.
     * @param int $clientId The ID of the client.
     * @return array The projects for the client manager.
     *
     *  @api
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
     * Gets all the projects for the current user.
     * By default, closed projects are not included.
     *
     * @param bool $showClosedProjects (optional) Set to true to include closed projects.
     * @return array Returns an array of projects.
     *
     * @api
     */
    public function getAll(bool $showClosedProjects = false): array
    {
        return $this->projectRepository->getUserProjects( userId: session('userdata.id'),
            accessStatus: "all",
            projectTypes: "project");
    }

    /**
     * Finds projects based on a search term.
     *
     * @param string $term The search term (optional)
     * @return array The filtered projects that match the search term
     *
     * @api
     */
    public function findProject(string $term = "")
    {
        $projects = $this->projectRepository->getUserProjects(
            userId: session('userdata.id'),
            accessStatus: "all",
            projectTypes: "project");

        $filteredProjects = [];
        foreach ($projects as $key => $project) {

            if(Str::contains($projects[$key]['name'], $term, ignoreCase: true) || $term =='') {
                $projects[$key] = $this->prepareDatesForApiResponse($project);
                $projects[$key]['id'] = $project['id'] . '-' . $project['modified'];

                $filteredProjects[] =  $projects[$key];
            }
        }

        return $filteredProjects;
    }

    /**
     * Polls for new projects for the current user session.
     * Retrieves all projects for the current user and prepares the dates for API response.
     *
     * @return array An array of projects with prepared dates for API response.
     *
     * @api
     */
    public function pollForNewProjects() {

        $projects = $this->projectRepository->getUserProjects(userId: session('userdata.id'), accessStatus: "all");

        foreach ($projects as $key => $project) {
            $projects[$key] = $this->prepareDatesForApiResponse($project);
        }

        return $projects;

    }


    /**
     * Polls for updated projects.
     * Retrieves all the projects the current user has access to and prepares them for API response.
     * Adds the modified timestamp to the project ID for tracking updates.
     *
     * @return array
     *
     * @api
     */
    public function pollForUpdatedProjects(): array
    {
        $projects = $this->projectRepository->getUserProjects(userId: session('userdata.id'), accessStatus: "all");

        foreach ($projects as $key => $project) {
            $projects[$key] = $this->prepareDatesForApiResponse($project);
            $projects[$key]['id'] = $project['id'] . '-' . $project['modified'];

        }

        return $projects;
    }


    /**
     * Prepares date values in a project for API response.
     *
     * The method takes a project array and converts the 'modified', 'start',
     * and 'end' date values into ISO 8601 Zulu string format. If a date value
     * is not a valid string, it sets it to null.
     *
     * @param array $project The project array to be modified.
     * @return array The modified project array with formatted date values.
     *
     * @internal
     */
    private function prepareDatesForApiResponse($project) {

        if(dtHelper()->isValidDateString($project['modified'])) {
            $project['modified'] = dtHelper()->parseDbDateTime($project['modified'])->toIso8601ZuluString();
        }else{
            $project['modified'] = null;
        }

        if(dtHelper()->isValidDateString($project['start'])) {
            $project['start'] = dtHelper()->parseDbDateTime($project['start'])->toIso8601ZuluString();
        }else{
            $project['start'] = null;
        }

        if(dtHelper()->isValidDateString($project['end'])) {
            $project['end'] = dtHelper()->parseDbDateTime($project['end'])->toIso8601ZuluString();
        }else{
            $project['end'] = null;
        }

        return $project;

    }
}


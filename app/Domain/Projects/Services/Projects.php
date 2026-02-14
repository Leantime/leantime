<?php

namespace Leantime\Domain\Projects\Services;

use DateInterval;
use DateTime;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Events\EventDispatcher as EventCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\Avatarcreator;
use Leantime\Core\Support\FromFormat;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Files\Services\Files;
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
use SVG\SVG;
use Symfony\Component\HttpFoundation\Response;

class Projects
{
    use DispatchesEvents;

    public function __construct(
        private ProjectRepository $projectRepository,
        private TicketRepository $ticketRepository,
        private SettingRepository $settingsRepo,
        private LanguageCore $language,
        private Messengers $messengerService,
        private NotificationService $notificationService,
        protected Files $fileService,
        protected Avatarcreator $avatarcreator
    ) {}

    /**
     * Gets the project types.
     *
     *
     * @api
     */
    public function getProjectTypes(): mixed
    {

        $types = ['project' => 'label.project'];

        $filtered = static::dispatch_filter('filterProjectType', $types);

        // Strategy & Program are protected types
        if (isset($filtered['strategy'])) {
            unset($filtered['strategy']);
        }

        if (isset($filtered['program'])) {
            unset($filtered['program']);
        }

        return $filtered;
    }

    /**
     * Gets the project with the given ID.
     *
     * @param  int  $id  The ID of the project to retrieve.
     * @return bool|array Returns the project data as an associative array if the project exists, otherwise returns false.
     *
     * @api
     */
    public function getProject(int $id): bool|array
    {
        return $this->projectRepository->getProject($id);
    }

    // Gets project progress

    /**
     * Gets the progress of a project.
     * Calculates the completion percentage, estimated completion date,
     * and planned completion date of the project.
     *
     * @param  int  $projectId  The ID of the project.
     * @return array The progress of the project.
     *
     * @api
     */
    public function getProjectProgress($projectId): array
    {
        $returnValue = ['percent' => 0, 'estimatedCompletionDate' => 'We need more data to determine that.', 'plannedCompletionDate' => ''];

        $averageStorySize = $this->ticketRepository->getAverageTodoSize($projectId);

        // We'll use this as the start date of the project
        $firstTicket = $this->ticketRepository->getFirstTicket($projectId);

        if (is_object($firstTicket) === false) {
            return $returnValue;
        }

        $dateOfFirstTicket = new DateTime($firstTicket->date);
        $today = new DateTime;
        $totalprojectDays = $today->diff($dateOfFirstTicket)->format('%a');

        // Calculate percent

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
            $percentEffort = $percentNum; // This needs to be set to percentNum in case users choose to not use efforts
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

        $today->add(new DateInterval('P'.$estDaysLeftInProject.'D'));

        // Fix this
        $currentDate = new DateTime;
        $inFiveYears = intval($currentDate->format('Y')) + 5;

        if (intval($today->format('Y')) >= $inFiveYears) {
            $completionDate = 'Past '.$inFiveYears;
        } else {
            $completionDate = $today->format($this->language->__('language.dateformat'));
        }

        $returnValue = ['percent' => $finalPercent, 'estimatedCompletionDate' => $completionDate, 'plannedCompletionDate' => ''];
        if ($numberOfClosedTickets < 10) {
            $returnValue['estimatedCompletionDate'] = "<a href='".BASE_URL."/tickets/showAll' class='btn btn-primary'><span class=\"fa fa-thumb-tack\"></span> Complete more To-Dos to see that!</a>";
        } elseif ($finalPercent == 100) {
            $returnValue['estimatedCompletionDate'] = "<a href='".BASE_URL."/projects/showAll' class='btn btn-primary'><span class=\"fa fa-suitcase\"></span> This project is complete, onto the next!</a>";
        }

        return $returnValue;
    }

    /**
     * Gets an array of user IDs to notify for a given project.
     *
     * @param  int  $projectId  The ID of the project to get users to notify for.
     * @return array An array of user IDs.
     *
     * @api
     */
    public function getUsersToNotify($projectId): array
    {

        $users = $this->projectRepository->getUsersAssignedToProject($projectId);

        $to = [];

        // Only users that actually want to be notified and are active
        foreach ($users as $user) {
            if ($user['notifications'] != 0 && strtolower($user['status']) == 'a') {
                $to[] = $user['id'];
            }
        }

        return $to;
    }

    /**
     * Gets all the users who need to be notified for a given project.
     *
     * @param  int  $projectId  The ID of the project.
     * @return array An array of users to notify.
     *
     * @api
     */
    public function getAllUserInfoToNotify($projectId): array
    {

        $users = $this->projectRepository->getUsersAssignedToProject($projectId);

        $to = [];

        // Only users that actually want to be notified
        foreach ($users as $user) {
            if ($user['notifications'] != 0 && ($user['username'] != session('userdata.mail'))) {
                $to[] = $user;
            }
        }

        return $to;
    }

    // TODO Split and move to notifications

    /**
     * Notifies the users associated with a project about a notification.
     *
     * Applies two-layer filtering before sending:
     * 1. Per-project mute — users who muted this project are excluded
     * 2. Event-type preference — users who disabled this event category are excluded
     *
     * @mentions always bypass both layers.
     *
     * @param  Notification  $notification  The notification object to send.
     *
     * @api
     */
    public function notifyProjectUsers(Notification $notification): void
    {

        // Filter notifications
        $notification = EventCore::dispatch_filter('notificationFilter', $notification);

        // Email
        $users = $this->getUsersToNotify($notification->projectId);
        $projectName = $this->getProjectName($notification->projectId);

        // Exclude the author
        $users = array_filter($users, function ($user) use ($notification) {
            return $user != $notification->authorId;
        }, ARRAY_FILTER_USE_BOTH);
        $users = array_values($users);

        // Batch-load notification preferences for all candidate users
        $settingKeys = [];
        foreach ($users as $userId) {
            $settingKeys[] = 'usersettings.'.$userId.'.projectNotificationLevels';
            $settingKeys[] = 'usersettings.'.$userId.'.projectMutedNotifications'; // legacy format
            $settingKeys[] = 'usersettings.'.$userId.'.notificationEventTypes';
        }
        $settingKeys[] = 'companysettings.defaultNotificationEventTypes';
        $settingKeys[] = 'companysettings.defaultNotificationRelevance';
        $preloadedSettings = $this->settingsRepo->getSettingsForKeys($settingKeys);

        // Layer 1: Filter by per-project relevance level (all / my_work / muted)
        $users = $this->filterUsersByProjectRelevance($users, $notification, $preloadedSettings);

        // Layer 2: Remove users who disabled this event type category
        $users = $this->filterUsersByEventType($users, $notification->module, $preloadedSettings);

        // Extract mentioned user IDs and re-add them (mentions bypass filters)
        $mentionedUserIds = $this->extractMentionedUserIds($notification);
        foreach ($mentionedUserIds as $mentionedId) {
            if ($mentionedId != $notification->authorId && ! in_array($mentionedId, $users)) {
                $users[] = $mentionedId;
            }
        }

        $emailMessage = $notification->message;
        if ($notification->url !== false) {
            $emailMessage .= " <a href='".$notification->url['url']."'>".$notification->url['text'].'</a>';
        }

        // NEW Queuing messaging system
        $queue = app()->make(QueueRepository::class);
        $queue->queueMessageToUsers($users, $emailMessage, $notification->subject, $notification->projectId);

        // Send to messengers
        $this->messengerService->sendNotificationToMessengers($notification, $projectName);

        // Notify users about mentions
        // Fields that should be parsed for mentions
        $mentionFields = [
            'comments' => ['text'],
            'projects' => ['details'],
            'tickets' => ['description'],
            'canvas' => ['description', 'data', 'conclusion', 'assumptions'],
        ];

        $contentToCheck = '';
        // Find entity ID & content
        // Todo once all entities are models this if statement can be reduced
        if (isset($notification->entity) && is_array($notification->entity) && isset($notification->entity['id'])) {
            $entityId = $notification->entity['id'];

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
            // Entity id not set use project id
            $entityId = $notification->projectId;
        }

        if ($contentToCheck != '') {
            $this->notificationService->processMentions(
                $contentToCheck,
                $notification->module,
                (int) $entityId,
                $notification->authorId,
                $notification->url['url']
            );
        }

        // Apply same two-layer filtering to in-app notification users
        $allUsersToNotify = $this->getAllUserInfoToNotify($notification->projectId);
        $allUserIds = array_map(fn ($u) => $u['id'], $allUsersToNotify);
        $filteredIds = $this->filterUsersByProjectRelevance($allUserIds, $notification, $preloadedSettings);
        $filteredIds = $this->filterUsersByEventType($filteredIds, $notification->module, $preloadedSettings);

        // Re-add mentioned users for in-app notifications too
        foreach ($mentionedUserIds as $mentionedId) {
            if ($mentionedId != $notification->authorId && ! in_array($mentionedId, $filteredIds)) {
                $filteredIds[] = $mentionedId;
            }
        }

        $filteredUsersToNotify = array_filter($allUsersToNotify, fn ($u) => in_array($u['id'], $filteredIds));

        /**
         * This event is fired to notify project users of important updates.
         * An event "notifyProjectUsers" is dispatched with an array of variables required for the notification.
         * These variables include the type of update, module affected, entity ID, message and subject of notification,
         * users to be notified, and url if present. This event belongs to the "domain.services.projects" context.
         *
         * @event notifyProjectUsers
         *
         * @param  string  $type  The type of update. E.g., "projectUpdate"
         * @param  string  $module  The name of the module affected by the update.
         * @param  int  $moduleId  The ID of the entity affected by the update.
         * @param  string  $message  The content of the notification message.
         * @param  string  $subject  The subject of the notification message.
         * @param  array  $users  The users to be notified about this update (filtered by notification preferences).
         * @param  string|null  $url  The url leading to the update if any.
         *
         * @context domain.services.projects
         */
        self::dispatch_event('notifyProjectUsers', ['type' => 'projectUpdate', 'module' => $notification->module, 'moduleId' => $entityId, 'message' => $notification->message, 'subject' => $notification->subject, 'users' => array_values($filteredUsersToNotify), 'url' => $notification->url['url']], 'leantime.domain.projects.services.projects.notifyProjectUsers');
    }

    /**
     * Filters users by their per-project notification relevance level.
     *
     * Supports three levels:
     * - 'all':     User receives all notifications from this project (default).
     * - 'my_work': User only receives notifications for items they are assigned to,
     *              created, or are directly involved in.
     * - 'muted':   User receives no notifications from this project.
     *
     * Performs lazy migration from the old binary mute format (projectMutedNotifications)
     * to the new three-level format (projectNotificationLevels).
     *
     * @param  array<int>  $userIds  User IDs to filter.
     * @param  Notification  $notification  The notification being dispatched.
     * @param  array<string, mixed>  $preloadedSettings  Pre-fetched settings map.
     * @return array<int> Filtered user IDs.
     */
    private function filterUsersByProjectRelevance(array $userIds, Notification $notification, array $preloadedSettings): array
    {
        $projectId = $notification->projectId;

        $companyDefault = $preloadedSettings['companysettings.defaultNotificationRelevance'] ?? Notification::RELEVANCE_ALL;
        if (! Notification::isValidRelevanceLevel($companyDefault)) {
            $companyDefault = Notification::RELEVANCE_ALL;
        }

        return array_values(array_filter($userIds, function (int $userId) use ($projectId, $notification, $preloadedSettings, $companyDefault) {
            $level = $this->getProjectRelevanceLevel($userId, $projectId, $preloadedSettings, $companyDefault);

            if ($level === Notification::RELEVANCE_MUTED) {
                return false;
            }

            if ($level === Notification::RELEVANCE_MY_WORK) {
                return $this->isUserInvolvedInNotification($userId, $notification);
            }

            // RELEVANCE_ALL: keep the user
            return true;
        }));
    }

    /**
     * Determines the notification relevance level for a user on a specific project.
     *
     * Checks the new projectNotificationLevels format first, falls back to
     * the legacy projectMutedNotifications format, then to company default.
     *
     * @param  int  $userId  The user ID.
     * @param  int  $projectId  The project ID.
     * @param  array<string, mixed>  $preloadedSettings  Pre-fetched settings map.
     * @param  string  $companyDefault  The company-level default relevance.
     * @return string The relevance level constant.
     */
    private function getProjectRelevanceLevel(int $userId, int $projectId, array $preloadedSettings, string $companyDefault): string
    {
        // Check new format first
        $newKey = 'usersettings.'.$userId.'.projectNotificationLevels';
        $newSetting = $preloadedSettings[$newKey] ?? false;
        if (! empty($newSetting) && $newSetting !== false) {
            $levels = json_decode($newSetting, true);
            if (is_array($levels) && isset($levels[$projectId])) {
                $level = $levels[$projectId];
                if (Notification::isValidRelevanceLevel($level)) {
                    return $level;
                }
            }
        }

        // Lazy migration: check old muted-projects format
        $oldKey = 'usersettings.'.$userId.'.projectMutedNotifications';
        $oldSetting = $preloadedSettings[$oldKey] ?? false;
        if (! empty($oldSetting) && $oldSetting !== false) {
            $mutedIds = json_decode($oldSetting, true);
            if (is_array($mutedIds) && in_array($projectId, $mutedIds)) {
                return Notification::RELEVANCE_MUTED;
            }
        }

        return $companyDefault;
    }

    /**
     * Checks whether a user is directly involved in the entity being notified about.
     *
     * A user is considered "involved" if they are the assignee (editorId),
     * the creator (userId), or otherwise linked to the entity.
     *
     * @param  int  $userId  The user to check.
     * @param  Notification  $notification  The notification with entity data.
     * @return bool True if the user is involved.
     */
    private function isUserInvolvedInNotification(int $userId, Notification $notification): bool
    {
        $entity = $notification->entity;

        if (is_array($entity)) {
            // Ticket/item: editorId is the assignee, userId is the creator
            if (isset($entity['editorId']) && (int) $entity['editorId'] === $userId) {
                return true;
            }
            if (isset($entity['userId']) && (int) $entity['userId'] === $userId) {
                return true;
            }
            // Canvas items: author field
            if (isset($entity['author']) && (int) $entity['author'] === $userId) {
                return true;
            }
        } elseif (is_object($entity)) {
            if (isset($entity->editorId) && (int) $entity->editorId === $userId) {
                return true;
            }
            if (isset($entity->userId) && (int) $entity->userId === $userId) {
                return true;
            }
            if (isset($entity->author) && (int) $entity->author === $userId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filters out users who have disabled the notification category for a given module.
     *
     * Falls back to company defaults if the user has no personal preference, and treats
     * missing company defaults as all-enabled.
     *
     * @param  array<int>  $userIds  User IDs to filter.
     * @param  string  $module  The notification module value (e.g. 'tickets', 'comments').
     * @param  array<string, mixed>  $preloadedSettings  Pre-fetched settings map.
     * @return array<int> Filtered user IDs.
     */
    private function filterUsersByEventType(array $userIds, string $module, array $preloadedSettings): array
    {
        $category = Notification::getCategoryForModule($module);

        // Unknown module category — let notification through
        if ($category === null) {
            return $userIds;
        }

        $companyDefault = $preloadedSettings['companysettings.defaultNotificationEventTypes'] ?? false;
        $companyEnabledTypes = null;
        if (! empty($companyDefault) && $companyDefault !== false) {
            $companyEnabledTypes = json_decode($companyDefault, true);
            if (! is_array($companyEnabledTypes)) {
                $companyEnabledTypes = null;
            }
        }

        return array_values(array_filter($userIds, function (int $userId) use ($category, $preloadedSettings, $companyEnabledTypes) {
            $key = 'usersettings.'.$userId.'.notificationEventTypes';
            $setting = $preloadedSettings[$key] ?? false;

            if (! empty($setting) && $setting !== false) {
                $enabledTypes = json_decode($setting, true);
                if (is_array($enabledTypes)) {
                    return in_array($category, $enabledTypes);
                }
            }

            // Fall back to company default
            if ($companyEnabledTypes !== null) {
                return in_array($category, $companyEnabledTypes);
            }

            // No preferences set anywhere — all enabled
            return true;
        }));
    }

    /**
     * Gets the count of users who have muted or reduced notifications for a project.
     *
     * Checks both new (projectNotificationLevels) and legacy (projectMutedNotifications) formats.
     *
     * @param  int  $projectId  The project ID.
     * @return int The number of users who have muted this project.
     */

    /**
     * Extracts user IDs mentioned in the notification entity content.
     *
     * @param  Notification  $notification  The notification to scan for mentions.
     * @return array<int> Array of mentioned user IDs.
     */
    private function extractMentionedUserIds(Notification $notification): array
    {
        $mentionFields = [
            'comments' => ['text'],
            'projects' => ['details'],
            'tickets' => ['description'],
            'canvas' => ['description', 'data', 'conclusion', 'assumptions'],
        ];

        $contentToCheck = '';
        if (isset($notification->entity) && is_array($notification->entity)) {
            if (isset($mentionFields[$notification->module])) {
                foreach ($mentionFields[$notification->module] as $field) {
                    if (isset($notification->entity[$field])) {
                        $contentToCheck .= $notification->entity[$field];
                    }
                }
            }
        } elseif (isset($notification->entity) && is_object($notification->entity)) {
            if (isset($mentionFields[$notification->module])) {
                foreach ($mentionFields[$notification->module] as $field) {
                    if (isset($notification->entity->$field)) {
                        $contentToCheck .= $notification->entity->$field;
                    }
                }
            }
        }

        if (empty($contentToCheck)) {
            return [];
        }

        $userIds = [];
        $dom = new \DOMDocument;
        @$dom->loadHTML($contentToCheck);
        $links = $dom->getElementsByTagName('a');

        for ($i = 0; $i < $links->count(); $i++) {
            $taggedUser = $links->item($i)->getAttribute('data-tagged-user-id');
            if ($taggedUser !== '' && is_numeric($taggedUser)) {
                $userIds[] = (int) $taggedUser;
            }
        }

        return array_unique($userIds);
    }

    /**
     * Gets the count of users who have muted notifications for a specific project.
     *
     * @param  int  $projectId  The project ID.
     * @return int Number of users who have muted this project.
     *
     * @api
     */
    public function getMuteCountForProject(int $projectId): int
    {
        $db = app()->make(\Illuminate\Database\ConnectionInterface::class);
        $count = 0;

        // Check new format: projectNotificationLevels
        $newRows = $db->table('zp_settings')
            ->where('key', 'LIKE', 'usersettings.%.projectNotificationLevels')
            ->get(['value']);

        foreach ($newRows as $row) {
            $levels = json_decode($row->value, true);
            if (is_array($levels) && isset($levels[$projectId]) && $levels[$projectId] === Notification::RELEVANCE_MUTED) {
                $count++;
            }
        }

        // Also check legacy format: projectMutedNotifications
        $oldRows = $db->table('zp_settings')
            ->where('key', 'LIKE', 'usersettings.%.projectMutedNotifications')
            ->get(['value']);

        foreach ($oldRows as $row) {
            $mutedProjects = json_decode($row->value, true);
            if (is_array($mutedProjects) && in_array($projectId, $mutedProjects)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Retrieves the name of a project based on its ID.
     *
     * @param  int  $projectId  The ID of the project.
     * @return string|null The name of the project, or null if the project does not exist.
     *
     * @api
     */
    public function getProjectName($projectId)
    {

        $project = $this->projectRepository->getProject($projectId);
        if ($project) {
            return $project['name'];
        }
    }

    /**
     * Gets the project IDs assigned to a specified user.
     *
     * @param  int  $userId  The ID of the user.
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
     * @param  int  $userId  The ID of the user.
     * @param  string  $projectStatus  The status of the projects. Defaults to "open".
     * @param  int|null  $clientId  The ID of the client. Defaults to null.
     * @return array The projects assigned to the user.
     *
     * @api
     */
    public function getProjectsAssignedToUser($userId, string $projectStatus = 'open', $clientId = null, string $projectTypes = 'all'): array
    {
        $projects = $this->projectRepository->getUserProjects(userId: $userId, projectStatus: $projectStatus, clientId: $clientId, projectTypes: $projectTypes);

        if ($projects) {
            return $projects;
        } else {
            return [];
        }
    }

    /**
     * Finds all children projects for a given parent project.
     *
     * @param  mixed  $currentParentId  The ID of the current parent project.
     * @param  array  $projects  An array of projects to search for children.
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
     * @param  array  $projects  An array of projects
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
     * @param  int  $userId  The ID of the user.
     * @param  string  $projectStatus  The project status. Default is "open".
     * @param  int|null  $clientId  The ID of the client. Default is null.
     * @return array An array containing the assigned projects, the project hierarchy, and the favorite projects.
     *
     * @api
     */
    public function getProjectHierarchyAssignedToUser($userId, string $projectStatus = 'open', $clientId = null): array
    {

        // Load all projects user is assigned to
        $projects = $this->projectRepository->getUserProjects(
            userId: $userId,
            projectStatus: $projectStatus,
            clientId: (int) $clientId,
            accessStatus: 'assigned'
        );
        $projects = self::dispatch_filter('afterLoadingProjects', $projects);

        // Build project hierarchy
        $projectsClean = $this->cleanParentRelationship($projects);
        $projectHierarchy = $this->findMyChildren(0, $projectsClean);
        $projectHierarchy = self::dispatch_filter('afterPopulatingProjectHierarchy', $projectHierarchy, ['projects' => $projects]);

        // Get favorite projects
        $favorites = [];
        foreach ($projects as $project) {
            if (isset($project['isFavorite']) && $project['isFavorite'] == 1) {
                $favorites[] = $project;
            }
        }
        $favorites = self::dispatch_filter('afterPopulatingProjectFavorites', $favorites, ['projects' => $projects]);

        return [
            'allAssignedProjects' => $projects,
            'allAssignedProjectsHierarchy' => $projectHierarchy,
            'favoriteProjects' => $favorites,
        ];
    }

    /**
     * Gets the project hierarchy available to a user.
     *
     * @param  int  $userId  The ID of the user.
     * @param  string  $projectStatus  The status of the projects to retrieve. Defaults to "open".
     * @param  int|null  $clientId  The ID of the client. Defaults to null.
     * @return array Returns an array containing the following keys:
     *               - "allAvailableProjects": An array of all projects available to the user.
     *               - "allAvailableProjectsHierarchy": An array representing the project hierarchy available to the user.
     *               - "clients": An array of clients associated with the projects available to the user.
     *
     * @api
     */
    public function getProjectHierarchyAvailableToUser($userId, string $projectStatus = 'open', $clientId = null): array
    {

        // Load all projects user is assigned to
        $projects = $this->projectRepository->getProjectsUserHasAccessTo(
            userId: $userId,
            status: $projectStatus,
            clientId: (int) $clientId,
        );
        $projects = self::dispatch_filter('afterLoadingProjects', $projects);

        // Build project hierarchy
        $projectsClean = $this->cleanParentRelationship($projects);
        $projectHierarchy = $this->findMyChildren(0, $projectsClean);
        $projectHierarchy = self::dispatch_filter('afterPopulatingProjectHierarchy', $projectHierarchy, ['projects' => $projects]);

        $clients = $this->getClientsFromProjectList($projects);

        return [
            'allAvailableProjects' => $projects,
            'allAvailableProjectsHierarchy' => $projectHierarchy,
            'clients' => $clients,
        ];
    }

    /**
     * Gets all the clients available to a user.
     *
     * @param  int  $userId  The ID of the user.
     * @param  string  $projectStatus  The status of the projects to be considered. Defaults to "open".
     * @return array An array of clients available to the user.
     *
     * @api
     */
    public function getAllClientsAvailableToUser($userId, string $projectStatus = 'open'): array
    {

        // Load all projects user is assigned to
        $projects = $this->projectRepository->getUserProjects(
            userId: $userId,
            projectStatus: $projectStatus,
            clientId: null,
            accessStatus: 'all'
        );
        $projects = self::dispatch_filter('afterLoadingProjects', $projects);

        $clients = $this->getClientsFromProjectList($projects);

        return $clients;
    }

    public function getClientsFromProjectList(array $projects): array
    {

        $clients = [];
        foreach ($projects as $project) {
            if (! array_key_exists($project['clientId'], $clients)) {
                $clients[$project['clientId']] = [
                    'name' => $project['clientName'],
                    'id' => $project['clientId'],
                ];
            }
        }

        return $clients;
    }

    /**
     * Gets the role of a user in a specific project.
     *
     * @param  mixed  $userId  The user ID.
     * @param  mixed  $projectId  The project ID.
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
                return '';
            }
        } else {
            return '';
        }
    }

    /**
     * Gets the projects that a user has access to.
     *
     * @param  int  $userId  The ID of the user.
     * @return array|false The array of projects if the user has access, false otherwise.
     *
     * @api
     */
    public function getProjectsUserHasAccessTo($userId): false|array
    {
        $projects = $this->projectRepository->getUserProjects(userId: $userId, accessStatus: 'all');

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
            session()->has('currentProject')
            && $this->changeCurrentSessionProject(session('currentProject'))
        ) {
            return;
        }

        session(['currentProject' => 0]);

        // If last project setting is set use that
        $lastProject = $this->settingsRepo->getSetting('usersettings.'.session('userdata.id').'.lastProject');
        if (
            ! empty($lastProject)
            && $this->changeCurrentSessionProject($lastProject)
        ) {
            return;
        }

        $allProjects = $this->getProjectsAssignedToUser(session('userdata.id'));
        if (empty($allProjects)) {
            return;
        }

        if ($this->changeCurrentSessionProject($allProjects[0]['id']) === true) {
            return;
        }

        throw new \Exception('Error trying to set a project');
    }

    /**
     * Gets the current project ID.
     * If the session variable "currentProject" is set, it returns its integer value.
     * Otherwise, it returns 0.
     *
     * @return int The current project ID.
     */
    public function getCurrentProjectId(): int
    {
        // Make sure that we never return a value less than 0.
        return max(0, (int) (session('currentProject') ?? 0));
    }

    /**
     * Change the current session project to the specified projectId.
     *
     * @param  mixed  $projectId  The ID of the project to set as current.
     * @return bool Returns true if the current project is successfully changed, false otherwise.
     *
     * @api
     */
    public function changeCurrentSessionProject($projectId): bool
    {
        if (! is_numeric($projectId)) {
            return false;
        }

        $projectId = (int) $projectId;

        if (
            session()->exists('currentProject') &&
            session('currentProject') == $projectId
        ) {
            return true;
        }

        session(['currentProjectName' => '']);

        if ($this->isUserAssignedToProject(session('userdata.id'), $projectId) === true) {
            // Get user project role

            $project = $this->getProject($projectId);

            if ($project) {
                if (
                    session()->exists('currentProject') &&
                    session('currentProject') == $project['id']
                ) {
                    return true;
                }

                $projectRole = $this->getProjectRole(session('userdata.id'), $projectId);

                session(['currentProject' => $projectId]);

                if (mb_strlen($project['name']) > 25) {
                    session(['currentProjectName' => mb_substr($project['name'], 0, 25).' (...)']);
                } else {
                    session(['currentProjectName' => $project['name']]);
                }

                session(['currentProjectClient' => $project['clientName']]);

                session(['userdata.projectRole' => '']);
                if ($projectRole != '') {
                    session(['userdata.projectRole' => Roles::getRoleString($projectRole)]);
                }

                session(['currentSprint' => '']);
                session(['currentIdeaCanvas' => '']);
                session(['lastTicketView' => '']);
                session(['lastFilterdTicketTableView' => '']);
                session(['lastFilterdTicketKanbanView' => '']);
                session(['currentWiki' => '']);
                session(['lastArticle' => '']);

                session(['currentSWOTCanvas' => '']);
                session(['currentLEANCanvas' => '']);
                session(['currentEMCanvas' => '']);
                session(['currentINSIGHTSCanvas' => '']);
                session(['currentSBCanvas' => '']);
                session(['currentRISKSCanvas' => '']);
                session(['currentEACanvas' => '']);
                session(['currentLBMCanvas' => '']);
                session(['currentOBMCanvas' => '']);
                session(['currentDBMCanvas' => '']);
                session(['currentSQCanvas' => '']);
                session(['currentCPCanvas' => '']);
                session(['currentSMCanvas' => '']);
                session(['currentRETROSCanvas' => '']);
                $this->settingsRepo->saveSetting('usersettings.'.session('userdata.id').'.lastProject', session('currentProject'));

                $recentProjects = $this->settingsRepo->getSetting('usersettings.'.session('userdata.id').'.recentProjects');
                $recent = unserialize($recentProjects);

                if (is_array($recent) === false) {
                    $recent = [];
                }
                $key = array_search(session('currentProject'), $recent);
                if ($key !== false) {
                    unset($recent[$key]);
                }
                array_unshift($recent, session('currentProject'));

                $recent = array_slice($recent, 0, 20);

                $this->settingsRepo->saveSetting('usersettings.'.session('userdata.id').'.recentProjects', serialize($recent));

                session()->forget('projectsettings');

                self::dispatch_event('projects.setCurrentProject', $project);

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
     */
    public function resetCurrentProject(): void
    {

        session(['currentProject' => '']);
        session(['currentProjectClient' => '']);
        session(['currentProjectName' => '']);

        session(['currentSprint' => '']);
        session(['currentIdeaCanvas' => '']);

        session(['currentSWOTCanvas' => '']);
        session(['currentLEANCanvas' => '']);
        session(['currentEMCanvas' => '']);
        session(['currentINSIGHTSCanvas' => '']);
        session(['currentSBCanvas' => '']);
        session(['currentRISKSCanvas' => '']);
        session(['currentEACanvas' => '']);
        session(['currentLBMCanvas' => '']);
        session(['currentOBMCanvas' => '']);
        session(['currentDBMCanvas' => '']);
        session(['currentSQCanvas' => '']);
        session(['currentCPCanvas' => '']);
        session(['currentSMCanvas' => '']);
        session(['currentRETROSCanvas' => '']);
        session()->forget('projectsettings');

        $this->settingsRepo->saveSetting('usersettings.'.session('userdata.id').'.lastProject', session('currentProject'));

        $this->setCurrentProject();
    }

    /**
     * Gets all users that have access to a project.
     * For direct access only set the teamOnly flag to true
     *
     * @param  int  $projectId  The ID of the project.
     * @return array An array of users assigned to the project.
     *
     * @api
     */
    public function getUsersAssignedToProject($projectId, $teamOnly = false): array
    {
        $users = $this->projectRepository->getUsersAssignedToProject($projectId, $teamOnly);

        foreach ($users as $key => $user) {

            if (dtHelper()->isValidDateString($user['modified'])) {
                $users[$key]['modified'] = dtHelper()->parseDbDateTime($user['modified'])->toIso8601ZuluString();
            } else {
                $users[$key]['modified'] = null;
            }
        }

        if ($users) {
            return $users;
        }

        return [];
    }

    /**
     * Checks if a user is assigned to a particular project.
     *
     * @param  int  $userId  The ID of the user being checked.
     * @param  int  $projectId  The ID of the project being checked.
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
     * @param  int  $userId  - The ID of the user.
     * @param  int  $projectId  - The ID of the project.
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
     * @param  array  $values  The project data.
     *                         - name: string (required) The name of the project.
     *                         - details: string (optional) Additional details about the project.
     *                         - clientId: int (required) The ID of the client associated with the project.
     *                         - hourBudget: int (optional) The hour budget for the project (defaults to 0).
     *                         - assignedUsers: string (optional) The list of assigned users (defaults to an empty string).
     *                         - dollarBudget: int (optional) The dollar budget for the project (defaults to 0).
     *                         - psettings: string (optional) The project settings (defaults to 'restricted').
     *                         - type: string (fixed value 'project') The type of the project.
     *                         - start: string|null The start date of the project in user format or null.
     *                         - end: string|null The end date of the project in user format or null.
     * @return int|false The ID of the added project, or false if the project could not be added.
     *
     * @api
     */
    public function addProject(array $values): int|false
    {

        $values = [
            'name' => $values['name'],
            'details' => $values['details'] ?? '',
            'clientId' => $values['clientId'],
            'hourBudget' => $values['hourBudget'] ?? 0,
            'assignedUsers' => $values['assignedUsers'] ?? '',
            'dollarBudget' => $values['dollarBudget'] ?? 0,
            'psettings' => $values['psettings'] ?? 'restricted',
            'type' => 'project',
            'start' => $values['start'] ?? null,
            'end' => $values['end'] ?? null,
        ];
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
     * @param  int  $projectId  The ID of the project to duplicate.
     * @param  int  $clientId  The ID of the client for the duplicate project.
     * @param  string  $projectName  The name of the duplicate project.
     * @param  string  $userStartDate  The start date of the duplicate project in the format specified by the language setting.
     * @param  bool  $assignSameUsers  Whether to assign the same users as the original project.
     * @return bool|int Returns true if the project was successfully duplicated, or the ID of the new project if successful.
     *
     * @api
     */
    public function duplicateProject(int $projectId, int $clientId, string $projectName, string $userStartDate, bool $assignSameUsers): bool|int
    {

        if (! empty($userStartDate)) {

            try {
                $startDate = dtHelper()->parseUserDateTime($userStartDate)->startOfDay();
            } catch (\Exception $e) {
                $startDate = dtHelper()->userNow()->startOfDay();
            }

        }

        // Ignoring
        // Comments, files, timesheets, personalCalendar EventDispatcher
        $oldProjectId = $projectId;

        // Copy project Entry
        $projectValues = $this->getProject($projectId);

        $copyProject = [
            'name' => $projectName,
            'clientId' => $clientId,
            'details' => $projectValues['details'],
            'state' => $projectValues['state'],
            'hourBudget' => $projectValues['hourBudget'],
            'dollarBudget' => $projectValues['dollarBudget'],
            'menuType' => $projectValues['menuType'],
            'psettings' => $projectValues['psettings'],
            'assignedUsers' => [],
        ];

        if ($assignSameUsers) {
            $projectUsers = $this->projectRepository->getUsersAssignedToProject($projectId);

            foreach ($projectUsers as $user) {
                $copyProject['assignedUsers'][] = ['id' => $user['id'], 'projectRole' => $user['projectRole']];
            }
        }

        $projectSettingsKeys = ['retrolabels', 'ticketlabels', 'idealabels'];
        $newProjectId = $this->projectRepository->addProject($copyProject);

        // ProjectSettings
        foreach ($projectSettingsKeys as $key) {
            $setting = $this->settingsRepo->getSetting('projectsettings.'.$projectId.'.'.$key);

            if ($setting !== false) {
                $this->settingsRepo->saveSetting('projectsettings.'.$newProjectId.'.'.$key, $setting);
            }
        }

        // Duplicate all todos without dependent Ticket set
        $allTickets = $this->ticketRepository->getAllByProjectId($projectId);

        // Checks the oldest editFrom date and makes this the start date
        $oldestTicket = dtHelper()->now();

        foreach ($allTickets as $ticket) {

            if (dtHelper()->isValidDateString($ticket->editFrom)) {
                $ticketDateTimeObject = dtHelper()->parseDbDateTime($ticket->editFrom);
                if ($oldestTicket > $ticketDateTimeObject) {
                    $oldestTicket = $ticketDateTimeObject;
                }
            }

            if (dtHelper()->isValidDateString($ticket->dateToFinish)) {
                $ticketDateTimeObject = dtHelper()->parseDbDateTime($ticket->dateToFinish);
                if ($oldestTicket > $ticketDateTimeObject) {
                    $oldestTicket = $ticketDateTimeObject;
                }
            }
        }

        try {
            $projectStart = $startDate;
        } catch (\Exception $e) {
            $projectStart = dtHelper()->now()->startOfDay();
        }

        // Get interval from oldest ticket to project start date
        $interval = $oldestTicket->diff($projectStart);

        // oldId = > newId
        $ticketIdList = [];

        // Create all tickets first
        foreach ($allTickets as $ticket) {
            $dateToFinishValue = '';
            if (dtHelper()->isValidDateString($ticket->dateToFinish)) {
                $dateToFinish = dtHelper()->parseDbDateTime($ticket->dateToFinish);
                $dateToFinishValue = $dateToFinish->add($interval)->formatDateTimeForDb();
            }

            $editFromValue = '';
            if (dtHelper()->isValidDateString($ticket->editFrom)) {
                $editFrom = dtHelper()->parseDbDateTime($ticket->editFrom);
                $editFromValue = $editFrom->add($interval)->formatDateTimeForDb();
            }

            $editToValue = '';
            if (dtHelper()->isValidDateString($ticket->editTo)) {
                $editTo = dtHelper()->parseDbDateTime($ticket->editTo);
                $editToValue = $editTo->add($interval)->formatDateTimeForDb();
            }

            $ticketValues = [
                'headline' => $ticket->headline,
                'type' => $ticket->type,
                'description' => $ticket->description,
                'projectId' => $newProjectId,
                'editorId' => $ticket->editorId,
                'userId' => session('userdata.id'),
                'date' => date('Y-m-d H:i:s'),
                'dateToFinish' => $dateToFinishValue,
                'status' => $ticket->status,
                'storypoints' => $ticket->storypoints,
                'hourRemaining' => $ticket->hourRemaining,
                'planHours' => $ticket->planHours,
                'priority' => $ticket->priority,
                'sprint' => '',
                'acceptanceCriteria' => $ticket->acceptanceCriteria,
                'tags' => $ticket->tags,
                'editFrom' => $editFromValue,
                'editTo' => $editToValue,
                'dependingTicketId' => '',
                'milestoneid' => '',
            ];

            $newTicketId = $this->ticketRepository->addTicket($ticketValues);

            $ticketIdList[$ticket->id] = $newTicketId;
        }

        // Iterate through all and update relationships
        foreach ($allTickets as $ticket) {

            $values = [];

            if (! empty($ticket->milestoneid)) {
                $values['milestoneId'] = $ticketIdList[$ticket->milestoneid] ?? null;

                if ($values['milestoneId'] === null) {
                    Log::warning('Issue copying project. New Milestone was not found.');
                }
            }

            if (! empty($ticket->dependingTicketId)) {
                $values['dependingTicketId'] = $ticketIdList[$ticket->dependingTicketId] ?? null;

                if ($values['dependingTicketId'] === null) {
                    Log::warning('Issue copying project. New ticket dependency was not found.');
                }
            }

            $newTicketId = $ticketIdList[$ticket->id] ?? null;

            if ($newTicketId && ! empty($values)) {
                $this->ticketRepository->patchTicket($ticket->id, $values);
            }
        }

        // Ideas
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
            canvasTypeName: 'wiki'
        );

        $this->duplicateCanvas(
            repository: LeancanvaRepository::class,
            originalProjectId: $projectId,
            newProjectId: $newProjectId
        );

        self::dispatchEvent('projectDuplicated', ['projectId' => $projectId, 'newProjectId' => $newProjectId, 'startDate' => $projectStart, 'interval' => $interval]);

        return $newProjectId;
    }

    /**
     * Duplicate a canvas from one project to another.
     *
     * @param  string  $repository  The repository class to use for CRUD operations
     * @param  int  $originalProjectId  The ID of the original project
     * @param  int  $newProjectId  The ID of the new project
     * @param  string  $canvasTypeName  The canvas type name (optional)
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
            $canvasValues = [
                'title' => $canvas['title'],
                'author' => session('userdata.id'),
                'projectId' => $newProjectId,
                'description' => $canvas['description'] ?? '',
            ];

            $newCanvasId = $canvasRepo->addCanvas($canvasValues, $canvasTypeName);
            $canvasIdList[$canvas['id']] = $newCanvasId;

            $canvasItems = $canvasRepo->getCanvasItemsById($canvas['id']);

            if ($canvasItems && count($canvasItems) > 0) {
                // Build parent Array
                // oldId => newId
                $idMap = [];

                foreach ($canvasItems as $item) {

                    $milestoneId = '';
                    if (isset($idMap[$item['milestoneId']])) {
                        $milestoneId = $idMap[$item['milestoneId']];
                    }

                    $canvasItemValues = [
                        'description' => $item['description'] ?? '',
                        'assumptions' => $item['assumptions'] ?? '',
                        'data' => $item['data'] ?? '',
                        'conclusion' => $item['conclusion'] ?? '',
                        'box' => $item['box'] ?? '',
                        'author' => $item['author'] ?? '',

                        'canvasId' => $newCanvasId,
                        'sortindex' => $item['sortindex'] ?? '',
                        'status' => $item['status'] ?? '',
                        'relates' => $item['relates'] ?? '',
                        'milestoneId' => $milestoneId,
                        'title' => $item['title'] ?? '',
                        'parent' => $item['parent'] ?? '',
                        'featured' => $item['featured'] ?? '',
                        'tags' => $item['tags'] ?? '',
                        'kpi' => $item['kpi'] ?? '',
                        'data1' => $item['data1'] ?? '',
                        'data2' => $item['data2'] ?? '',
                        'data3' => $item['data3'] ?? '',
                        'data4' => $item['data4'] ?? '',
                        'data5' => $item['data5'] ?? '',
                        'startDate' => '',
                        'endDate' => '',
                        'setting' => $item['setting'] ?? '',
                        'metricType' => $item['metricType'] ?? '',
                        'startValue' => '',
                        'currentValue' => '',
                        'endValue' => $item['endValue'] ?? '',
                        'impact' => $item['impact'] ?? '',
                        'effort' => $item['effort'] ?? '',
                        'probability' => $item['probability'] ?? '',
                        'action' => $item['action'] ?? '',
                        'assignedTo' => $item['assignedTo'] ?? '',
                    ];

                    $newId = $canvasRepo->addCanvasItem($canvasItemValues);
                    $idMap[$item['id']] = $newId;
                }

                // Now fix relates to and parent relationships
                $newCanvasItems = $canvasRepo->getCanvasItemsById($newCanvasId);
                foreach ($canvasItems as $newItem) {
                    $newCanvasItemValues = [
                        'relates' => ($newItem['relates'] ?? false) ? ($idMap[$newItem['relates']] ?? '') : '',
                        'parent' => ($newItem['relates'] ?? false) ? ($idMap[$newItem['parent']] ?? '') : '',

                    ];

                    $canvasRepo->patchCanvasItem($newItem['id'], $newCanvasItemValues);
                }
            }
        }

        return true;
    }

    /**
     * Patches a project with partial updates.
     *
     * Unlike editProject(), this method only updates the fields provided in $params,
     * preserving all other existing values including the project type.
     *
     * Recommended for API usage to avoid accidentally overwriting fields.
     *
     * @param  int  $id  The ID of the project.
     * @param  array  $params  Fields to update (only these fields will be changed).
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
     * @param  mixed  $id  The ID of the project.
     * @return SVG|Response|string Returns either an SVG file, a file response or a path to a file
     *
     * @api
     */
    public function getProjectAvatar($id): SVG|Response|string
    {
        $project = $this->projectRepository->getProjectAvatar($id);

        if (empty($project)) {
            return $this->avatarcreator->getAvatar('🦄');
        }

        $this->avatarcreator->setFilePrefix('project');
        $this->avatarcreator->setBackground('#555555');

        // If user uploaded return uploaded file
        if (! empty($project['avatar'])) {

            $file = $this->fileService->getFileById($project['avatar']);
            if ($file) {
                return $file;
            }

        }

        $avatar = $this->avatarcreator->getAvatar($project['name']);

        return self::dispatch_filter('afterGettingAvatar', $avatar, ['projectId' => $id]);

        return $avatar;
    }

    /**
     * Sets the avatar for a project.
     *
     * @param  mixed  $file  The file containing the avatar.
     * @param  mixed  $project  The project object.
     * @return bool Indicates whether the avatar was successfully set.
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    public function setProjectAvatar($file, $projectId): bool
    {

        $project = $this->projectRepository->getProject($projectId);

        // Save the path to the old picture
        if (isset($project['avatar']) && $project['avatar'] > 0) {
            $oldPicture = $project['avatar'];
        }

        $leantimeFile = $this->fileService->upload($file, 'project', $projectId);

        if ($leantimeFile
            && $this->projectRepository->setPicture($leantimeFile['fileId'], $projectId)
            && $oldPicture) {

            try {
                $this->fileService->deleteFile($oldPicture);
            } catch (\Exception $e) {
                Log::warning('Could not delete old profile picture: '.$e->getMessage());
                Log::warning($e);
            }

        }

        return true;
    }

    /**
     * Retrieves all projects.
     *
     * @return array The projects.
     *
     * @api
     */
    public function getAllProjects()
    {
        return $this->projectRepository->getAll();
    }

    /**
     * Gets all strategy projects.
     *
     * @return array All strategies with their details
     *
     * @api
     */
    public function getAllStrategies(): array
    {
        return $this->projectRepository->getProjectsByType('strategy');
    }

    /**
     * Gets all program projects.
     *
     * @return array All programs with their details
     *
     * @api
     */
    public function getAllPrograms(): array
    {
        return $this->projectRepository->getProjectsByType('program');
    }

    /**
     * Retrieves the setup checklist for a project.
     *
     * @param  int  $projectId  The ID of the project.
     * @return array The setup checklist for the project
     */
    public function getProjectSetupChecklist($projectId): array
    {
        $progressSteps = [
            'define' => [
                'title' => 'label.define',
                'description' => 'checklist.define.description',
                'tasks' => [
                    'description' => [
                        'title' => 'label.projectDescription',
                        'status' => '',
                        'link' => BASE_URL.'/projects/showProject/'.session('currentProject').'',
                        'description' => 'checklist.define.tasks.description',
                    ],
                    'defineTeam' => [
                        'title' => 'label.defineTeam',
                        'status' => '',
                        'link' => BASE_URL.'/projects/showProject/'.session('currentProject').'#team',
                        'description' => 'checklist.define.tasks.defineTeam',
                    ],
                    'createBlueprint' => [
                        'title' => 'label.createBlueprint',
                        'status' => '',
                        'link' => BASE_URL.'/strategy/showBoards/',
                        'description' => 'checklist.define.tasks.createBlueprint',
                    ],
                ],
                'status' => '',
            ],
            'goals' => [
                'title' => 'label.setGoals',
                'description' => 'checklist.goals.description',
                'tasks' => [
                    'setGoals' => [
                        'title' => 'label.setGoals',
                        'status' => '',
                        'link' => BASE_URL.'/goalcanvas/dashboard',
                        'description' => 'checklist.goals.tasks.setGoals',
                    ],
                ],
                'status' => '',
            ],
            'timeline' => [
                'title' => 'label.setTimeline',
                'description' => 'checklist.timeline.description',
                'tasks' => [
                    'createMilestones' => [
                        'title' => 'label.createMilestones',
                        'status' => '',
                        'link' => BASE_URL.'/tickets/roadmap',
                        'description' => 'checklist.timeline.tasks.createMilestones',
                    ],

                ],
                'status' => '',
            ],
            'implementation' => [
                'title' => 'label.implement',
                'description' => 'checklist.implementation.description',
                'tasks' => [
                    'createTasks' => [
                        'title' => 'label.createTasks',
                        'status' => '', 'link' => BASE_URL.'/tickets/showAll',
                        'description' => 'checklist.implementation.tasks.createTasks ',
                    ],
                    'finish80percent' => [
                        'title' => 'label.finish80percent',
                        'status' => '',
                        'link' => BASE_URL.'/reports/show',
                        'description' => 'checklist.implementation.tasks.finish80percent',
                    ],
                ],
                'status' => '',
            ],
        ];

        // Todo determine tasks that are done.
        $project = $this->getProject($projectId);
        // Project Description
        if ($project['details'] != '') {
            $progressSteps['define']['tasks']['description']['status'] = 'done';
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
            $progressSteps['define']['goals']['setGoals']['status'] = 'done';
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
            $progressSteps['implementation']['tasks']['finish80percent']['status'] = 'done';
        }

        // Add overrides
        if (! $stepsCompleted = $this->settingsRepo->getSetting("projectsettings.$projectId.stepsComplete")) {
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
                ! in_array($previousValue['stepType'] ?? null, ['current', ''])
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
     * @param  string|array  $stepsComplete  The steps completed for the project.
     * @param  int  $projectId  The ID of the project.
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
     * @param  int  $id  The ID of the user.
     * @param  array  $projects  The projects to be edited.
     * @return bool True if the project relations were successfully edited, false otherwise.
     *
     * @api
     */
    public function editUserProjectRelations($id, $projects): bool
    {
        return $this->projectRepository->editUserProjectRelations($id, $projects);
    }

    /**
     * Retrieves the ID of a project by its name.
     *
     * @param  array  $allProjects  The array of all projects.
     * @param  string  $projectName  The name of the project to retrieve the ID for.
     * @return mixed The ID of the project if found, or false if not found.
     *
     *  @api
     */
    public function getProjectIdbyName(?array $allProjects, string $projectName)
    {
        if ($allProjects == null) {
            $allProjects = $this->getAll();
        }

        foreach ($allProjects as $project) {
            if (strtolower(trim($project['name'])) == strtolower(trim($projectName))) {
                return $project['id'];
            }
        }

        return false;
    }

    /**
     * Updates the sorting of multiple projects and tickets in Program Timeline.
     *
     * Handles mixed payload from Program Timeline Gantt chart which contains both:
     * - Project IDs prefixed with "pgm-" (e.g., "pgm-123")
     * - Ticket IDs prefixed with "ticket-" (e.g., "ticket-456")
     *
     * @param  array  $params  The array containing IDs as keys and sort positions as values.
     * @return bool Returns true if the sorting update was successful, false otherwise.
     */
    public function updateProjectSorting($params): bool
    {
        $projectUpdates = [];
        $ticketUpdates = [];

        // Separate projects from tickets based on ID prefix
        foreach ($params as $id => $sortPosition) {
            if (str_starts_with($id, 'pgm-')) {
                // Extract numeric project ID
                $projectId = (int) substr($id, 4);
                $projectUpdates[$projectId] = $sortPosition;
            } elseif (str_starts_with($id, 'ticket-')) {
                // Extract numeric ticket ID
                $ticketId = (int) substr($id, 7);
                $ticketUpdates[$ticketId] = $sortPosition;
            } else {
                // Legacy: plain numeric IDs are projects
                $projectUpdates[$id] = $sortPosition;
            }
        }

        // Update projects
        foreach ($projectUpdates as $projectId => $sortPosition) {
            if ($this->projectRepository->patch($projectId, ['sortIndex' => $sortPosition * 100]) === false) {
                return false;
            }
        }

        // Update tickets (milestones) using the Tickets service
        if (! empty($ticketUpdates)) {
            $ticketService = app()->make(\Leantime\Domain\Tickets\Services\Tickets::class);
            if (! $ticketService->updateTicketSorting($ticketUpdates)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Edits a project.
     *
     * IMPORTANT: If 'type' is not provided in $values, it will be preserved from the existing project.
     * To change the type, explicitly include it in $values.
     *
     * For partial updates that only modify specified fields, consider using patch() instead.
     *
     * @param  mixed  $values  The values to be updated in the project.
     * @param  int  $id  The ID of the project to be edited.
     * @return void
     *
     *  @api
     */
    public function editProject($values, $id)
    {
        // Preserve existing type if not provided
        if (! isset($values['type'])) {
            $currentProject = $this->getProject($id);
            if ($currentProject) {
                $values['type'] = $currentProject['type'] ?? 'project';
            } else {
                $values['type'] = 'project';
            }
        }

        $this->projectRepository->editProject($values, $id);
    }

    /**
     * Updates the status and sorting of projects.
     *
     * @param  array  $params  An associative array representing the project status and sorting.
     *                         The key is the status and the value is the serialized project list.
     * @param  null  $handler  Optional parameter for handling the project update process.
     * @return bool Returns true if the update process is successful, false otherwise.
     */
    public function updateProjectStatusAndSorting($params, $handler = null): bool
    {

        // Jquery sortable serializes the array for kanban in format
        // statusKey: item[]=X&item[]=X2...,
        // statusKey2: item[]=X&item[]=X2...,
        // This represents status & kanban sorting
        foreach ($params as $status => $projectList) {
            if (is_numeric($status) && ! empty($projectList)) {
                $projects = explode('&', $projectList);

                if (is_array($projects) === true) {
                    foreach ($projects as $key => $projectString) {
                        $id = substr($projectString, 7);

                        $this->projectRepository->patch($id, ['sortIndex' => $key * 100, 'state' => $status]);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Retrieves the projects for a client manager.
     *
     * @param  int  $userId  The ID of the user.
     * @param  int  $clientId  The ID of the client.
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

        return $allProjects;
    }

    /**
     * Gets all the projects for the current user.
     * By default, closed projects are not included.
     *
     * @param  bool  $showClosedProjects  (optional) Set to true to include closed projects.
     * @return array Returns an array of projects.
     *
     * @api
     */
    public function getAll(bool $showClosedProjects = false): array
    {
        return $this->projectRepository->getUserProjects(userId: session('userdata.id'),
            accessStatus: 'all',
            projectTypes: 'project');
    }

    /**
     * Finds projects based on a search term.
     *
     * @param  string  $term  The search term (optional)
     * @return array The filtered projects that match the search term
     *
     * @api
     */
    public function findProject(string $term = '')
    {
        $projects = $this->projectRepository->getUserProjects(
            userId: session('userdata.id'),
            accessStatus: 'all',
            projectTypes: 'project');

        $filteredProjects = [];
        foreach ($projects as $key => $project) {

            if (Str::contains($projects[$key]['name'], $term, ignoreCase: true) || $term == '') {
                $projects[$key] = $this->prepareDatesForApiResponse($project);
                $projects[$key]['id'] = $project['id'].'-'.$project['modified'];

                $filteredProjects[] = $projects[$key];
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
    public function pollForNewProjects()
    {

        $projects = $this->projectRepository->getUserProjects(userId: session('userdata.id'), accessStatus: 'all');

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
     *
     * @api
     */
    public function pollForUpdatedProjects(): array
    {
        $projects = $this->projectRepository->getUserProjects(userId: session('userdata.id'), accessStatus: 'all');

        foreach ($projects as $key => $project) {
            $projects[$key] = $this->prepareDatesForApiResponse($project);
            $projects[$key]['id'] = $project['id'].'-'.$project['modified'];

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
     * @param  array  $project  The project array to be modified.
     * @return array The modified project array with formatted date values.
     *
     * @internal
     */
    private function prepareDatesForApiResponse($project)
    {

        if (dtHelper()->isValidDateString($project['modified'])) {
            $project['modified'] = dtHelper()->parseDbDateTime($project['modified'])->toIso8601ZuluString();
        } else {
            $project['modified'] = null;
        }

        if (dtHelper()->isValidDateString($project['start'])) {
            $project['start'] = dtHelper()->parseDbDateTime($project['start'])->toIso8601ZuluString();
        } else {
            $project['start'] = null;
        }

        if (dtHelper()->isValidDateString($project['end'])) {
            $project['end'] = dtHelper()->parseDbDateTime($project['end'])->toIso8601ZuluString();
        } else {
            $project['end'] = null;
        }

        return $project;

    }
}

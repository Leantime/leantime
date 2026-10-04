<?php

namespace Leantime\Domain\Projects\Services;

use DateInterval;
use DateTime;
use GuzzleHttp\Client;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Leantime\Core\Auth\Contracts\ChecksProjectAccess;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Domains\BaseService;
use Leantime\Core\Events\EventDispatcher as EventCore;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\NotFoundException;
use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\Avatarcreator;
use Leantime\Core\Support\FromFormat;
use Leantime\Core\Support\OutboundUrlGuard;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Blueprints\Repositories\Blueprints as BlueprintsRepository;
use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Files\Services\Files;
use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas as GoalcanvaRepository;
use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;
use Leantime\Domain\Notifications\Models\Notification;
use Leantime\Domain\Notifications\Services\Messengers;
use Leantime\Domain\Notifications\Services\Notifications as NotificationService;
use Leantime\Domain\Notifications\Services\Webhooks;
use Leantime\Domain\Projects\Permissions\ProjectsPermissions;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Users\Permissions\UsersPermissions;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Leantime\Domain\Wiki\Repositories\Wiki;
use SVG\SVG;
use Symfony\Component\HttpFoundation\Response;

/**
 * Projects service (the company project god-service) + the engine's project-access provider.
 *
 * AUTHORIZATION MODEL (two scopes — see {@see ProjectsPermissions}):
 *  - by-id READS carry a dispatch #[RequiresPermission(projects.view, projectIdParam: ...)] gate
 *    (readonly+, AND-ins data access) — closes the cross-project read IDOR on the RPC surface.
 *  - MUTATIONS carry #[RequiresPermission(projects.create|edit|delete, global: true)] (manager+,
 *    company-wide — grant-equivalent to the legacy forceGlobal manager controllers).
 *  - $userId-param reads pin to the session user (admin may query others), mirroring
 *    getProjectsUserHasAccessTo().
 *
 * ⚠️ RECURSION GUARDRAIL: this class implements {@see ChecksProjectAccess}; the permission engine
 * calls getProjectRole() and isUserAssignedToProject() during EVERY project-scoped authorization.
 * Those two methods — and userCanManageProject()/getProjectsUserHasAccessTo() which the engine path
 * also touches — MUST NEVER call $this->authorize()/$this->can() in-body, or authorization recurses
 * infinitely. They stay ungated (pure repo / role reads).
 */
class Projects extends BaseService implements ChecksProjectAccess
{
    /**
     * Request-scoped memo for getProjectsAssignedToUser(), keyed by
     * "userId|status|clientId|projectTypes".
     *
     * @var array<string, array>
     */
    private array $assignedProjectsMemo = [];

    private Client $httpClient;

    public function __construct(
        private ProjectRepository $projectRepository,
        private TicketRepository $ticketRepository,
        private SettingRepository $settingsRepo,
        private LanguageCore $language,
        private Messengers $messengerService,
        private NotificationService $notificationService,
        protected Files $fileService,
        protected Avatarcreator $avatarcreator,
        private QueueRepository $queueRepo,
        private UserRepository $userRepo,
        private CommentRepository $commentRepo,
        private ClientRepository $clientRepo,
        Client $httpClient,
        private Webhooks $webhookService,
    ) {
        $this->httpClient = $httpClient;
    }

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
     * Self-authorizing (MCP tools and other in-process callers do not pass the attribute gate):
     * the caller must be able to view the project (role + membership), or hold the company-wide
     * projects.edit capability (managers manage any project). Otherwise false — the same result
     * as a missing project, so there is no existence oracle.
     *
     * @param  int  $id  The ID of the project to retrieve.
     * @return bool|array Returns the project data as an associative array if the project exists and is visible, otherwise false.
     *
     * @api
     */
    #[RequiresPermission(ProjectsPermissions::VIEW, projectIdParam: 'id')]
    public function getProject(int $id): bool|array
    {
        if ($id <= 0) {
            return false;
        }

        if (! $this->can(ProjectsPermissions::VIEW, $id) && ! $this->can(ProjectsPermissions::EDIT, null, true)) {
            return false;
        }

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
    #[RequiresPermission(ProjectsPermissions::VIEW, projectIdParam: 'projectId')]
    public function getProjectProgress($projectId): array
    {
        // Same shape as the computed return below — including
        // `estimatedCompletionState`. Without it the no-data early return
        // let templates fall back to the default 'ready' state and render
        // as though an estimate existed. Copy is i18n'd to match.
        $returnValue = [
            'percent' => 0,
            'estimatedCompletionDate' => $this->language->__('label.complete_more_todos'),
            'estimatedCompletionState' => 'needs_more_data',
            'plannedCompletionDate' => '',
        ];

        $averageStorySize = $this->ticketRepository->getAverageTodoSize($projectId);

        // We'll use this as the start date of the project
        $firstTicket = $this->ticketRepository->getFirstTicket($projectId);

        if (is_object($firstTicket) === false) {
            return $returnValue;
        }

        $dateOfFirstTicket = new DateTime($firstTicket->date);
        $today = new DateTime;
        $totalprojectDays = (int) $today->diff($dateOfFirstTicket)->format('%a');

        // Calculate percent

        // One query for all four counts/efforts instead of four separate table scans.
        $aggregates = $this->ticketRepository->getProjectProgressAggregates($projectId, $averageStorySize);

        $numberOfClosedTickets = $aggregates['closedCount'];
        $numberOfTotalTickets = $aggregates['allCount'];

        if ($numberOfTotalTickets == 0) {
            $percentNum = 0;
        } else {
            $percentNum = ($numberOfClosedTickets / $numberOfTotalTickets) * 100;
        }

        $effortOfClosedTickets = $aggregates['closedEffort'];
        $effortOfTotalTickets = $aggregates['allEffort'];

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

        // Return shape carries a plain-text status and a machine-readable
        // state — templates branch on the state to render the appropriate
        // CTA (e.g. a "showAll" link) instead of embedding presentation
        // HTML in the string. Non-template callers (MCP tools, JSON-RPC)
        // just read the plain text as-is.
        $returnValue = [
            'percent' => $finalPercent,
            'estimatedCompletionDate' => $completionDate,
            'estimatedCompletionState' => 'ready',
            'plannedCompletionDate' => '',
        ];
        if ($numberOfClosedTickets < 10) {
            $returnValue['estimatedCompletionState'] = 'needs_more_data';
            $returnValue['estimatedCompletionDate'] = $this->language->__('label.complete_more_todos');
        } elseif ($finalPercent == 100) {
            $returnValue['estimatedCompletionState'] = 'complete';
            $returnValue['estimatedCompletionDate'] = $this->language->__('label.project_complete_onto_next');
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
    #[RequiresPermission(ProjectsPermissions::VIEW, projectIdParam: 'projectId')]
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
    #[RequiresPermission(ProjectsPermissions::VIEW, projectIdParam: 'projectId')]
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
     * Channels: queued email, project messengers, mobile push, in-app
     * notifications and — last — queued rows for the personal webhooks of
     * recipients who opted in (delivered later by the webhook queue).
     *
     * @param  Notification  $notification  The notification object to send.
     *
     * @internal Local-only: not exposed over JSON-RPC. This performs no authorization and
     *           trusts every field of the caller-built Notification (project, author, subject,
     *           message, link), fanning it out to email, messengers, push, in-app and personal
     *           webhooks. It must only run as the last step after the calling service or
     *           controller has already authorized the change being announced.
     */
    public function notifyProjectUsers(Notification $notification): void
    {

        // Filter notifications (dispatch_filter returns mixed; the filter preserves the entity)
        /** @var Notification $notification */
        $notification = EventCore::dispatch_filter('notificationFilter', $notification);

        // The link goes out by email, messenger and push: point it at the trusted app URL
        // rather than the host of the request that triggered the notification.
        if (is_array($notification->url) && isset($notification->url['url']) && is_string($notification->url['url'])) {
            $notification->url['url'] = app()->make(TrustedAppUrl::class)->rebase($notification->url['url']);
        }

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

        // Mentions and collaborators both bypass the two filter layers above.
        $mentionedUserIds = $this->extractMentionedUserIds($notification);
        $collaboratorIds = $this->extractCollaboratorIds($notification);
        $users = $this->addBypassRecipients($users, $mentionedUserIds, $notification->authorId);
        $users = $this->addBypassRecipients($users, $collaboratorIds, $notification->authorId);

        $emailMessage = $notification->message;
        if ($notification->url !== false) {
            $emailMessage .= " <a href='".$notification->url['url']."'>".$notification->url['text'].'</a>';
        }

        // NEW Queuing messaging system
        $queue = app()->make(QueueRepository::class);
        $queue->queueMessageToUsers($users, $emailMessage, $notification->subject, $notification->projectId);

        // Send to messengers
        $this->messengerService->sendNotificationToMessengers($notification, $projectName);

        // Send mobile push notifications to recipients with a registered
        // device token. No-op for users with no mobile token; no-op for
        // FCM-provider rows when LEAN_PUSH_FCM_CREDENTIALS_PATH /
        // LEAN_PUSH_FCM_PROJECT_ID aren't configured. Wrapped in try
        // so a push outage never breaks the rest of the notification
        // dispatch path (queued emails + messengers still fire).
        try {
            $pushService = app()->make(\Leantime\Domain\Notifications\Services\Push::class);
            $pushService->sendFromNotification($notification, $users);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Push dispatch failed: '.$e->getMessage());
        }

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

        // Re-add mentions and collaborators for in-app notifications too
        $filteredIds = $this->addBypassRecipients($filteredIds, $mentionedUserIds, $notification->authorId);
        $filteredIds = $this->addBypassRecipients($filteredIds, $collaboratorIds, $notification->authorId);

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

        // Personal webhooks go last, to the same filtered recipients as email (relevance,
        // category, mentions, collaborators). This only queues one row per recipient: the
        // scheduler's WebhookQueue posts later, so no endpoint is contacted during this
        // request. Queueing reads only the recipients' opt-in; Webhooks checks each queued
        // recipient's opt-in, account and project access itself when the row is posted. The
        // catch is a last guard so this step can never break the dispatch path.
        try {
            $this->webhookService->queueToUsers($notification, $users);
        } catch (\Throwable $e) {
            Log::warning('Personal webhook dispatch failed', ['exception' => get_class($e)]);
        }
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
        if (! empty($newSetting)) {
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
        if (! empty($oldSetting)) {
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
            if (isset($entity['collaborators']) && is_array($entity['collaborators']) && in_array($userId, array_map('intval', $entity['collaborators']), true)) {
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
            if (isset($entity->collaborators) && is_array($entity->collaborators) && in_array($userId, array_map('intval', $entity->collaborators), true)) {
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
        if (! empty($companyDefault)) {
            $companyEnabledTypes = json_decode($companyDefault, true);
            if (! is_array($companyEnabledTypes)) {
                $companyEnabledTypes = null;
            }
        }

        return array_values(array_filter($userIds, function (int $userId) use ($category, $preloadedSettings, $companyEnabledTypes) {
            $key = 'usersettings.'.$userId.'.notificationEventTypes';
            $setting = $preloadedSettings[$key] ?? false;

            if (! empty($setting)) {
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
     * Extracts collaborator user IDs from a ticket notification entity.
     *
     * Collaborators bypass notification filters so they always receive
     * updates for tickets they are collaborating on.
     *
     * @param  Notification  $notification  The notification to extract collaborators from.
     * @return array<int> An array of unique user IDs who are collaborators.
     */
    private function extractCollaboratorIds(Notification $notification): array
    {
        if ($notification->module !== 'tickets') {
            return [];
        }

        $collaborators = [];

        if (isset($notification->entity) && is_array($notification->entity)) {
            $collaborators = $notification->entity['collaborators'] ?? [];
        } elseif (isset($notification->entity) && is_object($notification->entity)) {
            $collaborators = $notification->entity->collaborators ?? [];
        }

        if (empty($collaborators) || ! is_array($collaborators)) {
            return [];
        }

        // Only keep scalar, numeric values so non-scalars can't become bogus IDs (e.g. intval([]) === 0).
        $scalarNumeric = array_filter($collaborators, fn ($c) => is_scalar($c) && is_numeric($c));
        $ids = array_filter(array_map('intval', $scalarNumeric), fn ($id) => $id > 0);

        return array_values(array_unique($ids));
    }

    /**
     * Adds bypass recipients (e.g. mentions, collaborators) to a recipient list.
     *
     * Bypass recipients skip the two notification filter layers, so they are
     * appended after filtering. The notification author is never added, and
     * existing recipients are not duplicated.
     *
     * @param  array<int, mixed>  $recipients  The current recipient user IDs.
     * @param  array<int, int>  $bypassUserIds  User IDs that should always receive the notification.
     * @param  mixed  $authorId  The notification author, excluded from the result.
     * @return array<int, mixed> The recipient list with bypass users merged in.
     */
    private function addBypassRecipients(array $recipients, array $bypassUserIds, mixed $authorId): array
    {
        foreach ($bypassUserIds as $bypassId) {
            if ($bypassId != $authorId && ! in_array($bypassId, $recipients)) {
                $recipients[] = $bypassId;
            }
        }

        return $recipients;
    }

    /**
     * Gets the count of users who have muted notifications for a specific project.
     *
     * @param  int  $projectId  The project ID.
     * @return int Number of users who have muted this project.
     *
     * @api
     */
    #[RequiresPermission(ProjectsPermissions::VIEW, projectIdParam: 'projectId')]
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
    #[RequiresPermission(ProjectsPermissions::VIEW, projectIdParam: 'projectId')]
    public function getProjectName($projectId)
    {

        $project = $this->projectRepository->getProject($projectId);
        if ($project) {
            return $project['name'];
        }

        return null;
    }

    /**
     * Resolves the userId for an "assigned-to-user" read to the SESSION user unless the caller is an
     * admin/owner querying someone else — closing the cross-user param spoof on these @api reads (an
     * RPC caller could otherwise list another user's projects by passing a foreign id). Mirrors the
     * inline override in getProjectsUserHasAccessTo(). Recursion-safe: a global role check only,
     * never project membership.
     *
     * @param  int|string|null  $userId
     */
    private function resolveScopedUserId($userId): int
    {
        $sessionUser = (int) session('userdata.id');

        if ((int) $userId !== $sessionUser && ! Auth::userIsAtLeast(Roles::$admin)) {
            return $sessionUser;
        }

        return (int) $userId;
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
        $userId = $this->resolveScopedUserId($userId);

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
        $userId = $this->resolveScopedUserId($userId);

        // Request-scoped memo: this 11-join query is hit several times per page
        // load (status labels, multiple dashboard widgets). A user's project
        // assignments don't change within a request, so memoizing is safe.
        $memoKey = $userId.'|'.$projectStatus.'|'.($clientId ?? '').'|'.$projectTypes;
        if (isset($this->assignedProjectsMemo[$memoKey])) {
            return $this->assignedProjectsMemo[$memoKey];
        }

        $projects = $this->projectRepository->getUserProjects(userId: $userId, projectStatus: $projectStatus, clientId: $clientId, projectTypes: $projectTypes);

        return $this->assignedProjectsMemo[$memoKey] = $projects ?: [];
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
        $childrenByParent = [];
        foreach ($projects as $project) {
            $childrenByParent[$project['parent'] ?? 0][] = $project;
        }

        return $this->buildProjectBranch($currentParentId, $childrenByParent, []);
    }

    /**
     * Assembles one branch of the project tree from a parentId => children map.
     *

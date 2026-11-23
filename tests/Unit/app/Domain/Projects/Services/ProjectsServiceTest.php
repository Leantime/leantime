<?php

namespace Unit\app\Domain\Projects\Services;

use Carbon\CarbonImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Leantime\Core\Auth\Permissions\DefaultRolePermissions;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\Avatarcreator;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Files\Services\Files as FileService;
use Leantime\Domain\Notifications\Models\Notification;
use Leantime\Domain\Notifications\Services\Messengers;
use Leantime\Domain\Notifications\Services\Notifications as NotificationService;
use Leantime\Domain\Notifications\Services\Push;
use Leantime\Domain\Notifications\Services\PushPreferences;
use Leantime\Domain\Notifications\Services\WebhookQueue;
use Leantime\Domain\Notifications\Services\Webhooks;
use Leantime\Domain\Notifications\Services\WebhookTransport;
use Leantime\Domain\Projects\Permissions\ProjectsPermissions;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Leantime\Domain\Queue\Workers\Workers;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Users\Permissions\UsersPermissions;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\TestCase;

/**
 * Unit tests for the business logic extracted from the Projects domain
 * controllers into the Projects service during the thin-controller refactor:
 * getProjectHubData, notifyProjectCreated, saveZulipWebhook,
 * getProjectIntegrationSettings and getProjectCardData.
 */
class ProjectsServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    protected function setUp(): void
    {
        parent::setUp();

        // Session + macros needed because getUsersAssignedToProject() uses dtHelper().
        session(['usersettings.timezone' => 'UTC']);
        session(['usersettings.language' => 'en-US']);
        session(['usersettings.date_format' => 'Y-m-d']);
        session(['usersettings.time_format' => 'H:i']);
        session(['userdata.id' => 1]);

        $envMock = $this->make(EnvironmentCore::class, [
            'defaultTimezone' => 'UTC',
            'language' => 'en-US',
        ]);
        app()->instance(EnvironmentCore::class, $envMock);

        CarbonImmutable::mixin(new CarbonMacros('UTC', 'en-US', 'Y-m-d', 'H:i'));
    }

    /**
     * Builds a real Projects service, allowing each dependency to be overridden
     * with a stub so we can observe persistence/queue calls.
     */
    private function makeService(
        ?ProjectRepository $projectRepo = null,
        ?TicketRepository $ticketRepo = null,
        ?SettingRepository $settingsRepo = null,
        ?QueueRepository $queueRepo = null,
        ?UserRepository $userRepo = null,
        ?CommentRepository $commentRepo = null,
        ?ClientRepository $clientRepo = null,
        ?LanguageCore $language = null,
        ?Client $httpClient = null,
        ?Messengers $messengers = null,
        ?NotificationService $notificationService = null,
        ?Webhooks $webhooks = null,
        ?PermissionService $permissions = null,
    ): ProjectService {
        $language ??= $this->make(LanguageCore::class, [
            '__' => fn ($key) => $key,
        ]);

        $service = new ProjectService(
            $projectRepo ?? $this->make(ProjectRepository::class),
            $ticketRepo ?? $this->make(TicketRepository::class),
            $settingsRepo ?? $this->make(SettingRepository::class),
            $language,
            $messengers ?? $this->make(Messengers::class),
            $notificationService ?? $this->make(NotificationService::class),
            $this->make(FileService::class),
            $this->make(Avatarcreator::class),
            $queueRepo ?? $this->make(QueueRepository::class),
            $userRepo ?? $this->make(UserRepository::class),
            $commentRepo ?? $this->make(CommentRepository::class),
            $clientRepo ?? $this->make(ClientRepository::class),
            $httpClient ?? $this->make(Client::class),
            $webhooks ?? $this->make(Webhooks::class),
        );
        $service->setPermissionService($permissions ?? $this->permissionsGranting(null));

        return $service;
    }

    /**
     * A permission engine answering for the session role. With $grantedKeys null it uses the
     * built-in defaults of that role (so manager and above hold projects.edit), otherwise exactly
     * the given keys, like a custom role would.
     *
     * @param  array<int, string>|null  $grantedKeys
     */
    private function permissionsGranting(?array $grantedKeys): PermissionService
    {
        $decide = function (string $permissionKey) use ($grantedKeys): bool {
            $keys = $grantedKeys ?? DefaultRolePermissions::grantsFor(
                (string) session('userdata.role'),
                (new ProjectsPermissions)->permissions()
            );

            return in_array($permissionKey, $keys, true);
        };

        return $this->make(PermissionService::class, [
            'currentUserCan' => $decide,
            'authorize' => function (string $permissionKey) use ($decide): void {
                if (! $decide($permissionKey)) {
                    throw new AuthorizationException('denied');
                }
            },
        ]);
    }

    // ---------------------------------------------------------------------
    // notifyProjectUsers() → personal webhooks. Runs the real dispatch path
    // with every other channel stubbed and a real Webhooks service, so the
    // webhook recipients are what the relevance/category/mention filtering
    // produced, narrowed to the opted-in ones when queued and by Webhooks' own
    // per-recipient checks when posted. Delivery is queued; runWebhookQueue()
    // plays the scheduler's WebhookQueue run.
    // ---------------------------------------------------------------------

    /**
     * The fake zp_queue table the webhook rows are written to, keyed by msghash.
     *
     * @var array<string, array<string, mixed>>
     */
    private array $queueTable = [];

    /**
     * URLs the personal webhook transport posted to, in order.
     *
     * @var array<int, string>
     */
    private array $webhookPosts = [];

    private ?QueueRepository $fakeQueueRepo = null;

    /** @var array<int, array<int>> Stored creator/assignee of tasks and milestones, by id. */
    private array $storedTicketPeople = [];

    /** @var array<int, array<int>> Stored author/goal assignee of board items, by id. */
    private array $storedBoardPeople = [];

    /**
     * Wires notifyProjectUsers for restricted project 5 with team members 1 (author), 2, 3, 4, 5;
     * user 6 is not on the team but is @mentioned. The to-do is assigned to 2 and was created by
     * 5, so those two are the ones it involves. Returns what each channel saw during the call.
     *
     * @param  array<string, string>  $settings  Setting key => stored value (preferences + webhooks).
     * @param  array<int>  $usersWithProjectAccess  Who ProjectRepository::isUserAssignedToProject() lets into project 5.
     * @param  array<int, array<string, mixed>|false>  $userOverrides  zp_user column overrides by user id; false = user deleted.
     * @param  Webhooks|null  $webhooks  Replaces the real Webhooks service (built over a recording transport) when given.
     * @param  array<int>  $collaborators  Collaborators on the to-do the notification is about.
     * @return array{emailRecipients: array|null, emailEntity: array|null, messengerCalls: int, pushRecipients: array|null, inAppNotifications: array|null, order: array<int, string>}
     */
    private function runNotifyProjectUsers(array $settings, array $usersWithProjectAccess = [1, 2, 3, 4, 5], array $userOverrides = [], ?Webhooks $webhooks = null, array $collaborators = [], ?\Closure $adjustNotification = null): array
    {
        $seen = ['emailRecipients' => null, 'emailEntity' => null, 'messengerCalls' => 0, 'pushRecipients' => null, 'inAppNotifications' => null, 'order' => []];
        $this->queueTable = [];
        $this->webhookPosts = [];

        $users = [];
        foreach ([1, 2, 3, 4, 5, 6] as $userId) {
            $users[$userId] = ['id' => $userId, 'username' => "user{$userId}@example.com", 'notifications' => 1, 'status' => 'a', 'role' => 20, 'clientId' => 0];
        }
        foreach ($userOverrides as $userId => $columns) {
            $users[$userId] = $columns === false ? false : $columns + $users[$userId];
        }
        $team = array_map(fn (int $userId) => $users[$userId], [1, 2, 3, 4, 5]);

        $projectRepo = $this->make(ProjectRepository::class, [
            'getUsersAssignedToProject' => fn () => $team,
            'getProject' => fn ($projectId) => $projectId == 5 ? ['id' => 5, 'name' => 'Acme', 'psettings' => 'restricted', 'clientId' => 3] : false,
            'isUserAssignedToProject' => fn ($userId, $projectId) => $projectId == 5 && in_array((int) $userId, $usersWithProjectAccess, true),
            // The stored people of a task/milestone or a board item, by id (none unless a test says so).
            'getTicketPeopleIds' => fn (int $id) => $this->storedTicketPeople[$id] ?? [],
            'getBoardItemPeopleIds' => fn (int $id) => $this->storedBoardPeople[$id] ?? [],
        ]);
        $userRepo = $this->make(UserRepository::class, [
            'getUser' => fn ($userId) => $users[(int) $userId] ?? false,
        ]);
        $settingsRepo = $this->make(SettingRepository::class, [
            'getSettingsForKeys' => fn (array $keys) => array_intersect_key($settings, array_flip($keys)),
        ]);

        // Email rows and the webhook rows share the zp_queue table; the email queue is resolved from the container.
        $this->fakeQueueRepo = $this->make(QueueRepository::class, [
            'queueMessageToUsers' => function ($recipients, $message = '', $subject = '', $projectId = 0, $module = null, $moduleId = null) use (&$seen) {
                $seen['emailRecipients'] = array_values($recipients);
                $seen['emailEntity'] = ['module' => $module, 'moduleId' => $moduleId];
                $seen['order'][] = 'email';
            },
            'addMessageToQueue' => function (Workers $channel, string $subject, string $message, int $userId, int $projectId = 0) {
                $msghash = md5(count($this->queueTable).$subject.$message);
                $thedate = sprintf('2026-09-30 10:00:%02d', count($this->queueTable));
                $this->queueTable[$msghash] = ['msghash' => $msghash, 'channel' => $channel->value, 'subject' => $subject, 'message' => $message, 'userId' => $userId, 'projectId' => $projectId, 'thedate' => $thedate];
            },
            // Rows are written with rising timestamps, so table order is the repository's oldest-first order.
            'listMessageInQueue' => function (Workers $channel, mixed $recipients = null, int $projectId = 0, ?int $limit = null) {
                $rows = array_values(array_filter($this->queueTable, fn (array $row) => $row['channel'] === $channel->value));

                return $limit === null ? $rows : array_slice($rows, 0, $limit);
            },
            'deleteMessageInQueue' => function (string|array $msghashes) {
                foreach ((array) $msghashes as $msghash) {
                    unset($this->queueTable[$msghash]);
                }

                return true;
            },
        ]);
        app()->instance(QueueRepository::class, $this->fakeQueueRepo);
        app()->instance(Push::class, $this->make(Push::class, [
            'sendFromNotification' => function ($notification, $userIds) use (&$seen) {
                $seen['pushRecipients'] = array_values($userIds);
            },
        ]));
        // The in-app listener resolves the notifications service from the container too.
        $notificationService = $this->make(NotificationService::class, [
            'processMentions' => fn () => null,
            'addNotifications' => function ($notifications) use (&$seen) {
                $seen['inAppNotifications'] = $notifications;
                $seen['order'][] = 'inbox';
            },
        ]);
        app()->instance(NotificationService::class, $notificationService);
        $messengers = $this->make(Messengers::class, [
            'sendNotificationToMessengers' => function () use (&$seen) {
                $seen['messengerCalls']++;
            },
        ]);

        $webhooks ??= new Webhooks(
            $this->make(WebhookTransport::class, [
                'post' => function (string $url) {
                    $this->webhookPosts[] = $url;
                },
            ]),
            $settingsRepo,
            $userRepo,
            $projectRepo,
            $this->fakeQueueRepo,
        );
        // The queued rows' job resolves the webhook service from the container when WebhookQueue runs.
        app()->instance(Webhooks::class, $webhooks);

        $notification = new Notification;
        $notification->projectId = 5;
        $notification->authorId = 1;
        $notification->module = 'tickets';
        $notification->action = 'updated';
        $notification->subject = 'To-Do updated';
        $notification->message = 'Ada updated "Ship it"';
        $notification->url = ['url' => 'https://leantime.example.com/tickets/showTicket/42', 'text' => 'Open'];
        $notification->entity = ['id' => 42, 'headline' => 'Ship it', 'description' => '<a data-tagged-user-id="6">@Six</a> please review', 'editorId' => 2, 'userId' => 5, 'collaborators' => $collaborators];
        if ($adjustNotification) {
            $adjustNotification($notification);
        }

        $this->makeService(
            projectRepo: $projectRepo,
            settingsRepo: $settingsRepo,
            userRepo: $userRepo,
            messengers: $messengers,
            notificationService: $notificationService,
            webhooks: $webhooks,
        )->notifyProjectUsers($notification);

        return $seen;
    }

    /**
     * Runs the WEBHOOKS queue once the way the scheduler does: WebhookQueue built by the container.
     */
    private function runWebhookQueue(): void
    {
        app()->make(WebhookQueue::class)->processQueue();
    }

    /**
     * @param  array<int>  $userIds
     * @return array<string, string> The users' opted-in personal webhook settings.
     */
    private function optedInWebhooks(array $userIds): array
    {
        $settings = [];
        foreach ($userIds as $userId) {
            $settings["usersettings.{$userId}.webhook"] = json_encode(['url' => "https://1.1.1.1/hooks/user-{$userId}", 'enabled' => true]);
        }

        return $settings;
    }

    public function test_notify_project_users_queues_personal_webhooks_for_filtered_opted_in_recipients_and_sends_none_in_request(): void
    {
        // Laravel's queue driver plays no part — the job goes to Leantime's own zp_queue table,
        // drained by the scheduler — so even the sync driver must not deliver during the request.
        config(['queue.default' => 'sync']);
        $settings = [
            'usersettings.3.projectNotificationLevels' => json_encode([5 => 'muted']),
            'usersettings.4.notificationEventTypes' => json_encode(['comments']),
        ];
        // Everyone except user 5 opted in — including the author and the filtered-out users.
        $settings += $this->optedInWebhooks([1, 2, 3, 4, 6]);

        // User 6 is not on the team but reaches project 5 another way (e.g. through its client),
        // so the @mention bypass stands for the webhook too.
        $seen = $this->runNotifyProjectUsers($settings, usersWithProjectAccess: [1, 2, 3, 4, 5, 6]);

        // Author 1 excluded, 3 muted the project, 4 disabled the tasks category, 6 bypasses via @mention.
        $this->assertSame([2, 5, 6], $seen['emailRecipients']);
        $this->assertSame([2, 5, 6], $seen['pushRecipients']);
        $this->assertSame(1, $seen['messengerCalls'], 'Project messengers still fire independently of personal webhooks');
        $this->assertNotNull($seen['inAppNotifications'], 'In-app notifications are still created');
        $this->assertSame([], $this->webhookPosts, 'No webhook is sent while the request is running');
        $queuedRows = array_values($this->queueTable);
        $this->assertSame(array_fill(0, 3, Workers::WEBHOOKS->value), array_column($queuedRows, 'channel'), 'One webhooks-channel row per recipient, nothing on DEFAULT');
        $this->assertSame(
            [[1], [2], [6]],
            array_map(fn (array $row) => unserialize($row['message'], ['allowed_classes' => false])['recipientIds'], $queuedRows),
            'Only filtered recipients who opted in (5 did not) are queued, the author included'
        );

        $this->runWebhookQueue();

        $this->assertSame(
            ['https://1.1.1.1/hooks/user-1', 'https://1.1.1.1/hooks/user-2', 'https://1.1.1.1/hooks/user-6'],
            $this->webhookPosts,
            'Every queued recipient is delivered in one run'
        );
        $this->assertSame([], $this->queueTable, 'The run removes the handled rows');
    }

    /**
     * The digest tells an already-read email from a new one by matching the queued row to the
     * inbox row of the same event, so both must name the entity the same way.
     */
    public function test_notify_project_users_queues_the_email_with_the_same_entity_the_inbox_records(): void
    {
        $seen = $this->runNotifyProjectUsers([]);

        $this->assertSame(['module' => 'tickets', 'moduleId' => 42], $seen['emailEntity']);
        $this->assertNotEmpty($seen['inAppNotifications']);
        foreach ($seen['inAppNotifications'] as $row) {
            $this->assertSame(['tickets', 42], [$row['module'], $row['moduleId']]);
        }
        $this->assertSame(['inbox', 'email'], $seen['order'], 'the unread inbox row exists before the queued email, so a digest run in between can never take the email for an already-read one');
    }

    /**
     * Mentions and collaborators bypass the relevance/category filters, but a bypass is no
     * licence to post project content to someone's endpoint: the webhook boundary rechecks
     * the recipient itself, while email keeps its own policy.
     *
     * @dataProvider bypassRecipientsWhoMayNotReceiveWebhooksProvider
     */
    public function test_notify_project_users_never_webhooks_a_bypass_recipient_who_may_not_receive_it(array $usersWithProjectAccess, array $userOverrides): void
    {
        $seen = $this->runNotifyProjectUsers($this->optedInWebhooks([2, 6]), $usersWithProjectAccess, $userOverrides);
        $this->runWebhookQueue();

        $this->assertSame(['https://1.1.1.1/hooks/user-2'], $this->webhookPosts);
        $this->assertContains(6, $seen['emailRecipients'], 'The webhook checks narrow webhooks only; email keeps its own policy');
    }

    /**
     * @return array<string, array{array<int>, array<int, array<string, mixed>|false>}>
     */
    public static function bypassRecipientsWhoMayNotReceiveWebhooksProvider(): array
    {
        return [
            'mentioned user who lost access to the restricted project' => [[1, 2, 3, 4, 5], []],
            'mentioned user who was deactivated' => [[1, 2, 3, 4, 5, 6], [6 => ['status' => 'i']]],
            'mentioned user who was deleted' => [[1, 2, 3, 4, 5, 6], [6 => false]],
        ];
    }

    /**
     * "Receive email notifications" is about email. A user who moved their notifications to a
     * webhook and switched email off still gets the webhook, whether they are on the team or
     * reach the notification through a mention.
     */
    public function test_notify_project_users_sends_personal_webhooks_to_users_who_switched_email_off(): void
    {
        $seen = $this->runNotifyProjectUsers(
            $this->optedInWebhooks([2, 6]),
            usersWithProjectAccess: [1, 2, 3, 4, 5, 6],
            userOverrides: [2 => ['notifications' => 0], 6 => ['notifications' => 0]],
        );
        $this->runWebhookQueue();

        $this->assertNotContains(2, $seen['emailRecipients'], 'No email for a user who switched email off');
        $this->assertSame(['https://1.1.1.1/hooks/user-2', 'https://1.1.1.1/hooks/user-6'], $this->webhookPosts);
    }

    /**
     * The author's own changes reach their webhook (an integration keeping a full record), but
     * never their email, and their own relevance and category settings still apply.
     */
    public function test_notify_project_users_sends_the_authors_own_change_to_their_webhook_only(): void
    {
        $seen = $this->runNotifyProjectUsers($this->optedInWebhooks([1]));
        $this->runWebhookQueue();

        $this->assertNotContains(1, $seen['emailRecipients']);
        $this->assertSame(['https://1.1.1.1/hooks/user-1'], $this->webhookPosts);

        $this->runNotifyProjectUsers($this->optedInWebhooks([1]) + ['usersettings.1.projectNotificationLevels' => json_encode([5 => 'muted'])]);
        $this->runWebhookQueue();

        $this->assertSame([], $this->webhookPosts, 'An author who muted the project gets nothing');
    }

    /**
     * Collaborators bypass the filters, but an author who collaborates on the to-do is a candidate
     * already: being a collaborator must not bring their own change back past their mute or
     * category choice. Other collaborators keep the bypass.
     *
     * @dataProvider authorFilterSettingsProvider
     */
    public function test_an_author_who_collaborates_on_the_to_do_still_gets_nothing_past_their_own_filters(array $authorSettings): void
    {
        $settings = $this->optedInWebhooks([1, 2]) + $authorSettings + [
            'usersettings.2.projectNotificationLevels' => json_encode([5 => 'muted']),
        ];

        $this->runNotifyProjectUsers($settings, collaborators: [1, 2]);
        $this->runWebhookQueue();

        $this->assertSame(['https://1.1.1.1/hooks/user-2'], $this->webhookPosts, 'Collaborator 2 keeps the bypass; author 1 does not');
    }

    /**
     * @return array<string, array{array<string, string>}>
     */
    public static function authorFilterSettingsProvider(): array
    {
        return [
            'author muted the project' => [['usersettings.1.projectNotificationLevels' => json_encode([5 => 'muted'])]],
            'author switched the tasks category off' => [['usersettings.1.notificationEventTypes' => json_encode(['comments'])]],
        ];
    }

    public function test_notify_project_users_still_delivers_every_other_channel_when_webhooks_fail(): void
    {
        $webhooks = $this->make(Webhooks::class, [
            'queueToUsers' => function () {
                throw new \RuntimeException('webhook queue outage');
            },
        ]);

        $seen = $this->runNotifyProjectUsers([], usersWithProjectAccess: [1, 2, 3, 4, 5, 6], webhooks: $webhooks);

        $this->assertSame([2, 3, 4, 5, 6], $seen['emailRecipients']);
        $this->assertSame([2, 5, 6], $seen['pushRecipients']);
        $this->assertSame(1, $seen['messengerCalls']);
        $this->assertNotNull($seen['inAppNotifications'], 'In-app notifications are still created');
    }

    // ---------------------------------------------------------------------
    // notifyProjectUsers() → push. Push has its own switch (PushPreferences):
    // "Receive email notifications" no longer decides who gets a push.
    // ---------------------------------------------------------------------

    /**
     * Switching email off used to silence push as well. Now each channel follows its own switch.
     */
    public function test_notify_project_users_pushes_to_users_who_switched_email_off(): void
    {
        $seen = $this->runNotifyProjectUsers([], usersWithProjectAccess: [1, 2, 3, 4, 5, 6], userOverrides: [2 => ['notifications' => 0]]);

        $this->assertSame([3, 4, 5, 6], $seen['emailRecipients'], 'No email for a user who switched email off');
        $this->assertSame([2, 5, 6], $seen['pushRecipients'], 'Push still goes to them');
    }

    public function test_notify_project_users_never_pushes_to_users_who_switched_push_off(): void
    {
        $seen = $this->runNotifyProjectUsers([
            PushPreferences::settingKey(2) => '0',
            PushPreferences::settingKey(3) => '1',
        ], usersWithProjectAccess: [1, 2, 3, 4, 5, 6]);

        $this->assertSame([2, 3, 4, 5, 6], $seen['emailRecipients'], 'The push switch never touches email');
        $this->assertSame([5, 6], $seen['pushRecipients']);
    }

    /**
     * A mention left in an item must not push the text and link to someone who can no longer
     * see the project. Collaborators go through the same check. Email keeps its own policy.
     *
     * @return iterable<string, array{array<int>, array<int, array<string, mixed>|false>, array<int>}>
     */
    public static function bypassWithoutAccess(): iterable
    {
        yield 'mentioned user removed from the project' => [[1, 2, 3, 4, 5], [], []];
        yield 'mentioned user deactivated' => [[1, 2, 3, 4, 5, 6], [6 => ['status' => 'i']], []];
        yield 'mentioned user deleted' => [[1, 2, 3, 4, 5, 6], [6 => false], []];
    }

    /**
     * @dataProvider bypassWithoutAccess
     */
    public function test_a_mention_or_collaborator_without_project_access_gets_no_push(array $usersWithProjectAccess, array $userOverrides, array $collaborators): void
    {
        $seen = $this->runNotifyProjectUsers([], $usersWithProjectAccess, $userOverrides, collaborators: $collaborators);

        $this->assertNotContains(6, $seen['pushRecipients'], 'Mentioned user without access');
        $this->assertContains(2, $seen['pushRecipients'], 'Everyone else is unaffected');
    }

    /**
     * A mention gets past the relevance and category filters, not past the push switch.
     */
    public function test_notify_project_users_push_switch_is_final_even_for_a_mention(): void
    {
        $seen = $this->runNotifyProjectUsers([
            PushPreferences::settingKey(6) => '0',
            'usersettings.3.projectNotificationLevels' => json_encode([5 => 'muted']),
            'usersettings.4.notificationEventTypes' => json_encode(['comments']),
        ]);

        $this->assertSame([2, 5, 6], $seen['emailRecipients'], 'Email keeps the mention bypass');
        $this->assertSame([2, 5], $seen['pushRecipients'], 'Muted and category filters apply to push too; the mentioned user switched push off');
    }

    /**
     * "All activity" fills the inbox and the email digest; push only carries what involves you.
     */
    public function test_push_goes_only_to_the_people_the_notification_involves(): void
    {
        $seen = $this->runNotifyProjectUsers([], usersWithProjectAccess: [1, 2, 3, 4, 5, 6], collaborators: [3]);

        $this->assertSame([2, 3, 4, 5, 6], $seen['emailRecipients'], 'Email keeps All activity');
        $this->assertSame([2, 3, 5, 6], $seen['pushRecipients'], 'Assignee 2, collaborator 3, creator 5 and mentioned 6; not 4');
    }

    public function test_project_activity_that_involves_nobody_pushes_to_nobody(): void
    {
        // A project status update: the entity is the comment, and nobody on the team is named.
        $seen = $this->runNotifyProjectUsers([], adjustNotification: function (Notification $notification) {
            $notification->module = 'comments';
            $notification->action = 'commented';
            $notification->entity = ['id' => 9, 'text' => 'On track', 'userId' => 1, 'moduleId' => 5, 'module' => 'project'];
        });

        $this->assertSame([2, 3, 4, 5], $seen['emailRecipients']);
        $this->assertSame([], $seen['pushRecipients']);
    }

    public function test_a_comment_on_your_task_pushes_to_the_task_people(): void
    {
        $seen = $this->runNotifyProjectUsers([], adjustNotification: function (Notification $notification) {
            $notification->module = 'comments';
            $notification->action = 'commented';
            $notification->entity = ['id' => 9, 'text' => 'Looks good', 'userId' => 1, 'moduleId' => 42, 'module' => 'ticket'];
            $notification->involvedUserIds = [3, 4];
        });

        $this->assertSame([3, 4], $seen['pushRecipients']);
    }

    /**
     * Producers send what they have (form fields, the editor as a board item's "author", a
     * comment instead of its item), so involvement reads the stored item's people.
     *
     * @return array<string, array{0: \Closure, 1: array<int>}>
     */
    public static function storedPeopleCases(): array
    {
        $set = fn (string $module, string $action, array $entity) => function (Notification $notification) use ($module, $action, $entity) {
            $notification->module = $module;
            $notification->action = $action;
            $notification->entity = $entity;
        };

        return [
            // Task 42 was created by 4 and is assigned to 2; the form sends neither.
            'task form save' => [$set('tickets', 'updated', ['id' => 42, 'headline' => 'Ship it']), [2, 4]],
            // Milestone 50 was created by 3; the dialog's fields don't say so.
            'milestone dialog save' => [$set('tickets', 'updated', ['id' => 50, 'headline' => 'Beta']), [3]],
            // Board item 77 was written by 3; the edit carries the editor (1) as author.
            'board item edit' => [$set('leancanvas', 'updated', ['id' => 77, 'itemId' => 77, 'author' => 1]), [3]],
            'board item comment' => [$set('leancanvas', 'commented', ['text' => 'Nice', 'userId' => 1, 'moduleId' => 77]), [3]],
            'comment on a board item through the comments service' => [$set('comments', 'commented', ['id' => 9, 'text' => 'Nice', 'userId' => 1, 'module' => 'leancanvasitem', 'moduleId' => 77]), [3]],
            // Goal 78 was written by 3 and is assigned to 4.
            'goal edit' => [$set('goalcanvas', 'updated', ['id' => 78, 'itemId' => 78, 'author' => 1, 'assignedTo' => 4]), [3, 4]],
            'goal comment' => [$set('goalcanvas', 'commented', ['text' => 'On track', 'userId' => 1, 'moduleId' => 78]), [3, 4]],
            // A new goal has no id in the payload yet; its assignee is in it.
            'goal created for someone else' => [$set('goalcanvas', 'created', ['box' => 'goal', 'author' => 1, 'assignedTo' => 4]), [4]],
            // Docs article 79 was written by 3.
            'comment on a docs article' => [$set('comments', 'commented', ['id' => 9, 'text' => 'Typo', 'userId' => 1, 'module' => 'article', 'moduleId' => 79]), [3]],
            'comment on a milestone' => [$set('comments', 'commented', ['id' => 9, 'text' => 'Moved', 'userId' => 1, 'module' => 'milestone', 'moduleId' => 50]), [3]],
        ];
    }

    /**
     * @dataProvider storedPeopleCases
     *
     * @param  array<int>  $expectedPush
     */
    public function test_push_reaches_the_stored_people_of_the_item_whatever_the_producer_sent(\Closure $adjust, array $expectedPush): void
    {
        $this->storedTicketPeople = [42 => [4, 2], 50 => [3]];
        $this->storedBoardPeople = [77 => [3], 78 => [3, 4], 79 => [3]];

        $seen = $this->runNotifyProjectUsers([], adjustNotification: $adjust);

        $this->assertSame($expectedPush, $seen['pushRecipients']);
        $this->assertSame([2, 3, 4, 5], $seen['emailRecipients'], 'Email keeps All activity');
    }

    /**
     * Boards and ideas notify under their own module names. A mention there gets past the
     * involved-only push filter like a mention on a task.
     *
     * @return array<string, array{0: \Closure}>
     */
    public static function boardMentionCases(): array
    {
        $set = fn (string $module, string $action, array $entity) => function (Notification $notification) use ($module, $action, $entity) {
            $notification->module = $module;
            $notification->action = $action;
            $notification->entity = $entity;
        };
        $mention = '<a data-tagged-user-id="4">@Four</a> can you look?';

        return [
            'board item edit' => [$set('leancanvas', 'updated', ['id' => 77, 'itemId' => 77, 'author' => 1, 'description' => $mention])],
            'board item comment' => [$set('leancanvas', 'commented', ['text' => $mention, 'userId' => 1, 'moduleId' => 77])],
            'goal comment' => [$set('goalcanvas', 'commented', ['text' => $mention, 'userId' => 1, 'moduleId' => 77])],
            'idea update' => [$set('ideas', 'updated', ['id' => 77, 'itemId' => 77, 'author' => 1, 'data' => $mention])],
        ];
    }

    /**
     * @dataProvider boardMentionCases
     */
    public function test_a_mention_on_a_board_or_idea_reaches_push(\Closure $adjust): void
    {
        $this->storedBoardPeople = [77 => [3]];

        $seen = $this->runNotifyProjectUsers([], adjustNotification: $adjust);

        $this->assertSame([3, 4], $seen['pushRecipients'], 'Author 3, and 4 who was mentioned');
    }

    public function test_a_mention_on_a_board_gets_past_a_muted_project(): void
    {
        $this->storedBoardPeople = [77 => [3]];

        $seen = $this->runNotifyProjectUsers([
            'usersettings.4.projectNotificationLevels' => json_encode([5 => 'muted']),
        ], adjustNotification: function (Notification $notification) {
            $notification->module = 'goalcanvas';
            $notification->action = 'commented';
            $notification->entity = ['text' => '<a data-tagged-user-id="4">@Four</a> ok?', 'userId' => 1, 'moduleId' => 77];
        });

        $this->assertSame([2, 3, 5, 4], $seen['emailRecipients']);
        $this->assertSame([3, 4], $seen['pushRecipients']);
    }

    public function test_notify_project_users_never_pushes_to_the_author(): void
    {
        $seen = $this->runNotifyProjectUsers([PushPreferences::settingKey(1) => '1']);

        $this->assertNotContains(1, $seen['pushRecipients']);
    }

    /**
     * A comment's entity is the comment, so "My work" can only see the task's people through
     * involvedUserIds. Without them a comment on your task reached nobody on "My work".
     */
    public function test_my_work_reaches_the_people_a_comment_names_as_involved(): void
    {
        $myWork = [];
        foreach ([2, 3, 4, 5] as $userId) {
            $myWork["usersettings.{$userId}.projectNotificationLevels"] = json_encode([5 => 'my_work']);
        }
        $asTaskComment = function (array $involved) {
            return function (Notification $notification) use ($involved) {
                $notification->module = 'comments';
                $notification->action = 'commented';
                $notification->entity = ['id' => 9, 'text' => 'Looks good', 'userId' => 1, 'moduleId' => 42, 'module' => 'ticket'];
                $notification->involvedUserIds = $involved;
            };
        };

        $seen = $this->runNotifyProjectUsers($myWork, adjustNotification: $asTaskComment([]));
        $this->assertSame([], $seen['emailRecipients'], 'Without the task people, nobody on My work is involved');

        $seen = $this->runNotifyProjectUsers($myWork, adjustNotification: $asTaskComment([3, 5]));
        $this->assertSame([3, 5], $seen['emailRecipients']);
        $this->assertSame([3, 5], $seen['pushRecipients']);
    }

    public function test_involvement_never_gets_past_a_muted_project(): void
    {
        $seen = $this->runNotifyProjectUsers([
            'usersettings.3.projectNotificationLevels' => json_encode([5 => 'my_work']),
            'usersettings.4.projectNotificationLevels' => json_encode([5 => 'muted']),
        ], adjustNotification: function (Notification $notification) {
            $notification->module = 'comments';
            $notification->entity = ['id' => 9, 'text' => 'Looks good', 'userId' => 1, 'moduleId' => 42, 'module' => 'ticket'];
            $notification->involvedUserIds = [3, 4];
        });

        $this->assertSame([2, 3, 5], $seen['emailRecipients']);
    }

    public function test_get_project_hub_data_builds_unique_client_map_and_returns_all_projects_when_no_filter(): void
    {
        $projectRepo = $this->make(ProjectRepository::class, [
            'getUserProjects' => fn () => [
                ['id' => 1, 'clientId' => 10, 'clientName' => 'Acme'],
                ['id' => 2, 'clientId' => 10, 'clientName' => 'Acme'],
                ['id' => 3, 'clientId' => 20, 'clientName' => 'Globex'],
            ],
        ]);

        $result = $this->makeService(projectRepo: $projectRepo)->getProjectHubData(1, null);

        $this->assertCount(3, $result['allProjects']);
        $this->assertCount(2, $result['clients'], 'Duplicate clients must be collapsed into a unique map');
        $this->assertSame('Acme', $result['clients'][10]['name']);
        $this->assertSame('Globex', $result['clients'][20]['name']);
        $this->assertSame('', $result['currentClientName']);
        $this->assertSame('', $result['currentClient']);
    }

    public function test_get_project_hub_data_filters_projects_by_selected_client(): void
    {
        $projectRepo = $this->make(ProjectRepository::class, [
            'getUserProjects' => fn () => [
                ['id' => 1, 'clientId' => 10, 'clientName' => 'Acme'],
                ['id' => 2, 'clientId' => 20, 'clientName' => 'Globex'],
            ],
        ]);
        $clientRepo = $this->make(ClientRepository::class, [
            'getClient' => fn () => ['id' => 10, 'name' => 'Acme'],
        ]);

        $result = $this->makeService(projectRepo: $projectRepo, clientRepo: $clientRepo)->getProjectHubData(1, 10);

        $this->assertCount(1, $result['allProjects'], 'Only projects of the selected client are returned');
        $this->assertSame(1, $result['allProjects'][0]['id']);
        $this->assertCount(2, $result['clients'], 'The client map is still built from all projects');
        $this->assertSame('Acme', $result['currentClientName']);
        $this->assertSame(10, $result['currentClient']);
    }

    public function test_notify_project_created_queues_only_users_who_opted_in(): void
    {
        $projectRepo = $this->make(ProjectRepository::class, [
            'getUsersAssignedToProject' => fn () => [
                ['username' => 'wants@example.com', 'notifications' => 1, 'modified' => ''],
                ['username' => 'muted@example.com', 'notifications' => 0, 'modified' => ''],
            ],
        ]);

        $captured = null;
        $queueRepo = $this->make(QueueRepository::class, [
            'queueMessageToUsers' => function ($recipients, $message, $subject, $projectId) use (&$captured) {
                $captured = compact('recipients', 'message', 'subject', 'projectId');
            },
        ]);

        $this->makeService(projectRepo: $projectRepo, queueRepo: $queueRepo)
            ->notifyProjectCreated(42, 'My Project', 'Author');

        $this->assertNotNull($captured, 'A message must be queued');
        $this->assertSame(['wants@example.com'], $captured['recipients'], 'Users with notifications=0 are excluded');
        $this->assertSame(42, $captured['projectId']);
    }

    public function test_notify_project_created_escapes_the_author_and_project_names(): void
    {
        $projectRepo = $this->make(ProjectRepository::class, [
            'getUsersAssignedToProject' => fn () => [
                ['username' => 'wants@example.com', 'notifications' => 1, 'modified' => ''],
            ],
        ]);

        $captured = null;
        $queueRepo = $this->make(QueueRepository::class, [
            'queueMessageToUsers' => function ($recipients, $message) use (&$captured) {
                $captured = $message;
            },
        ]);
        $language = $this->make(LanguageCore::class, [
            '__' => fn ($key) => $key === 'email_notifications.project_created_message' ? '%s|%s|%s|%s' : $key,
        ]);

        $this->makeService(projectRepo: $projectRepo, queueRepo: $queueRepo, language: $language)
            ->notifyProjectCreated(42, 'P & Co', '<a href="https://evil.example">Click</a>"');

        $this->assertStringNotContainsString('<a href', $captured);
        $this->assertStringContainsString('P &amp; Co', $captured);
        $this->assertStringContainsString('Click&quot;', $captured);
    }

    // ---- in-body authorization (MCP tools call these services directly) ----

    /** Permission stub: VIEW granted only on the listed projects; global capabilities per flag. */
    private function projectPermissions(array $viewableProjects, bool $canManage): \Leantime\Core\Auth\Permissions\PermissionService
    {
        $decide = function (string $key, ?int $projectId = null, ?bool $forceGlobal = null) use ($viewableProjects, $canManage): bool {
            if ($key === 'projects.view' && $forceGlobal !== true) {
                return in_array($projectId, $viewableProjects, true);
            }

            return $canManage;
        };

        return $this->make(\Leantime\Core\Auth\Permissions\PermissionService::class, [
            'currentUserCan' => $decide,
            'authorize' => function (string $key, ?int $projectId = null, ?bool $forceGlobal = null) use ($decide): void {
                if (! $decide($key, $projectId, $forceGlobal)) {
                    throw new AuthorizationException;
                }
            },
        ]);
    }

    public function test_get_project_is_false_for_a_project_the_caller_cannot_view(): void
    {
        $projectRepo = $this->make(ProjectRepository::class, [
            'getProject' => fn ($id) => ['id' => $id, 'name' => 'Secret'],
        ]);
        $service = $this->makeService(projectRepo: $projectRepo);
        $service->setPermissionService($this->projectPermissions([5], false));

        $this->assertSame(5, $service->getProject(5)['id']);
        $this->assertFalse($service->getProject(6), 'A non-member without projects.edit must not read the project');
    }

    public function test_get_project_allows_company_wide_managers(): void
    {
        $projectRepo = $this->make(ProjectRepository::class, [
            'getProject' => fn ($id) => ['id' => $id],
        ]);
        $service = $this->makeService(projectRepo: $projectRepo);
        // Not a member of 6, but holds the company-wide projects.edit capability (manager+).
        $service->setPermissionService($this->projectPermissions([], true));

        $this->assertSame(6, $service->getProject(6)['id']);
    }

    public function test_project_mutations_self_authorize(): void
    {
        $projectRepo = $this->make(ProjectRepository::class, [
            'addProject' => function () {
                throw new \RuntimeException('must not create without projects.create');
            },
            'patch' => function () {
                throw new \RuntimeException('must not patch without projects.edit');
            },
            'editProject' => function () {
                throw new \RuntimeException('must not edit without projects.edit');
            },
        ]);
        $service = $this->makeService(projectRepo: $projectRepo);
        $service->setPermissionService($this->projectPermissions([5], false));

        $calls = [
            'addProject' => fn () => $service->addProject(['name' => 'X', 'clientId' => 1]),
            'patch' => fn () => $service->patch(5, ['name' => 'X']),
            'editProject' => fn () => $service->editProject(['name' => 'X', 'type' => 'project'], 5),
        ];

        foreach ($calls as $method => $call) {
            try {
                $call();
                $this->fail("$method must throw without the company-wide capability");
            } catch (AuthorizationException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    /**
     * Builds a service for the onboarding-patch tests: the session user (1) is assigned to the
     * listed projects and their recorded onboarding project is $onboardingProjectId.
     *
     * @param  array<int, int>  $assignedProjects
     * @param  array<int, array{0: int, 1: array}>  $patches
     */
    private function onboardingService(int|false $onboardingProjectId, array $assignedProjects, bool $canManage, array &$patches): ProjectService
    {
        session(['userdata' => ['id' => 1, 'role' => 'editor']]);

        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn ($userId, $projectId) => in_array($projectId, $assignedProjects, true),
            'patch' => function ($id, $params) use (&$patches) {
                $patches[] = [$id, $params];

                return true;
            },
        ]);
        $settingsRepo = $this->make(SettingRepository::class, [
            'getSetting' => fn ($key) => $key === 'user.1.onboardingProjectId' ? $onboardingProjectId : false,
        ]);

        $service = $this->makeService(projectRepo: $projectRepo, settingsRepo: $settingsRepo);
        $service->setPermissionService($this->projectPermissions([], $canManage));

        return $service;
    }

    public function test_onboarding_patch_allows_the_users_own_onboarding_project(): void
    {
        $patches = [];
        $service = $this->onboardingService(12, [12], false, $patches);

        $this->assertTrue($service->patchOnboardingProject(12, ['name' => 'Mine', 'parent' => 99, 'psettings' => 'all']));
        $this->assertSame([[12, ['name' => 'Mine']]], $patches, 'Only name/details may be written');
    }

    public function test_onboarding_patch_denies_any_other_project_for_non_managers(): void
    {
        $patches = [];
        // The user is assigned to team project 30, but it is not their onboarding project.
        $service = $this->onboardingService(12, [12, 30], false, $patches);

        $this->assertFalse($service->patchOnboardingProject(30, ['name' => 'Renamed']));

        // No recorded onboarding project at all (e.g. invited before this was tracked).
        $noRecord = [];
        $this->assertFalse($this->onboardingService(false, [30], false, $noRecord)->patchOnboardingProject(30, ['name' => 'Renamed']));

        // Recorded project the user was since removed from.
        $removed = [];
        $this->assertFalse($this->onboardingService(12, [], false, $removed)->patchOnboardingProject(12, ['name' => 'Renamed']));

        $this->assertSame([], $patches);
        $this->assertSame([], $noRecord);
        $this->assertSame([], $removed);
    }

    public function test_onboarding_patch_allows_company_wide_project_editors(): void
    {
        $patches = [];
        $service = $this->onboardingService(false, [], true, $patches);

        $this->assertTrue($service->patchOnboardingProject(30, ['details' => 'About']));
        $this->assertSame([[30, ['details' => 'About']]], $patches);
    }

    public function test_get_client_manager_projects_pins_non_admins_to_their_own_client(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'manager', 'clientId' => 3]]);

        $requestedClient = null;
        $projectRepo = $this->make(ProjectRepository::class, [
            'getClientProjects' => function ($clientId) use (&$requestedClient) {
                $requestedClient = $clientId;

                return [];
            },
            'getUserProjects' => fn () => [],
        ]);

        $this->makeService(projectRepo: $projectRepo)->getClientManagerProjects(1, 8);

        $this->assertSame(3, $requestedClient, 'A non-admin must only list their own client\'s projects');
    }

    /**
     * Regression for #4039: "Show Closed Projects" on Company → All Projects had no effect because
     * getAll() ignored its flag and always asked the repository for every project.
     */
    public function test_get_all_leaves_out_closed_projects_unless_asked(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'admin']]);

        $requestedStatuses = [];
        $projectRepo = $this->make(ProjectRepository::class, [
            'getUserProjects' => function (...$args) use (&$requestedStatuses) {
                $requestedStatuses[] = $args[1] ?? 'all';

                return [];
            },
        ]);
        $service = $this->makeService(projectRepo: $projectRepo);

        $service->getAll();
        $service->getAll(false);
        $service->getAll(true);

        $this->assertSame(['open', 'open', 'all'], $requestedStatuses);
    }

    /**
     * Regression for #4039 (client-manager branch of All Projects): closed projects are dropped when
     * the toggle is off and kept when it is on, from both the client list and the user's own list.
     */
    public function test_get_client_manager_projects_hides_closed_projects_when_asked(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'manager', 'clientId' => 3]]);

        $projectRepo = $this->make(ProjectRepository::class, [
            'getClientProjects' => fn () => [
                ['id' => 10, 'name' => 'Open client project', 'state' => 0],
                ['id' => 11, 'name' => 'Closed client project', 'state' => -1],
            ],
            'getUserProjects' => fn () => [
                ['id' => 10, 'name' => 'Open client project', 'state' => 0],
                ['id' => 20, 'name' => 'Own open project', 'state' => null],
                ['id' => 21, 'name' => 'Own closed project', 'state' => -1],
            ],
        ]);
        $service = $this->makeService(projectRepo: $projectRepo);

        $withoutClosed = $service->getClientManagerProjects(1, 3, showClosedProjects: false);
        $this->assertSame([10, 20], array_keys($withoutClosed));

        $withClosed = $service->getClientManagerProjects(1, 3, showClosedProjects: true);
        $this->assertSame([10, 11, 20, 21], array_keys($withClosed));
    }

    public function test_get_user_project_relation_pins_non_admins_to_themselves(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'editor']]);

        $requestedUser = null;
        $projectRepo = $this->make(ProjectRepository::class, [
            'getUserProjectRelation' => function ($userId) use (&$requestedUser) {
                $requestedUser = $userId;

                return [];
            },
        ]);

        $this->makeService(projectRepo: $projectRepo)->getUserProjectRelation(2);

        $this->assertSame(1, $requestedUser);
    }

    public function test_get_all_programs_only_returns_accessible_programs_for_non_admins(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'editor']]);

        $projectRepo = $this->make(ProjectRepository::class, [
            'getProjectsByType' => fn () => [['id' => 5, 'name' => 'Mine'], ['id' => 6, 'name' => 'Not mine']],
            'getUserProjects' => fn () => [['id' => 5]],
        ]);

        $programs = $this->makeService(projectRepo: $projectRepo)->getAllPrograms();

        $this->assertSame([5], array_column($programs, 'id'));
    }

    public function test_save_zulip_webhook_persists_when_all_fields_present(): void
    {
        $savedKey = null;
        $settingsRepo = $this->make(SettingRepository::class, [
            'saveSetting' => function ($key, $value) use (&$savedKey) {
                $savedKey = $key;

                return true;
            },
        ]);

        $result = $this->makeService(settingsRepo: $settingsRepo)->saveZulipWebhook(7, [
            'zulipURL' => 'https://zulip.example.com',
            'zulipEmail' => 'bot@example.com',
            'zulipBotKey' => 'key123',
            'zulipStream' => 'general',
            'zulipTopic' => 'updates',
        ]);

        $this->assertTrue($result['saved']);
        $this->assertSame('projectsettings.7.zulipHook', $savedKey);
        $this->assertSame('https://zulip.example.com', $result['hook']['zulipURL']);
    }

    public function test_save_zulip_webhook_does_not_persist_when_a_field_is_missing(): void
    {
        $saveCalls = 0;
        $settingsRepo = $this->make(SettingRepository::class, [
            'saveSetting' => function () use (&$saveCalls) {
                $saveCalls++;

                return true;
            },
        ]);

        $result = $this->makeService(settingsRepo: $settingsRepo)->saveZulipWebhook(7, [
            'zulipURL' => 'https://zulip.example.com',
            'zulipEmail' => '',
            'zulipBotKey' => 'key123',
            'zulipStream' => 'general',
            'zulipTopic' => 'updates',
        ]);

        $this->assertFalse($result['saved']);
        $this->assertSame(0, $saveCalls, 'Incomplete zulip config must not be persisted');
        $this->assertSame('', $result['hook']['zulipEmail']);
    }

    public function test_get_project_integration_settings_returns_empty_zulip_hook_when_unset(): void
    {
        $settingsRepo = $this->make(SettingRepository::class, [
            'getSetting' => fn () => '',
        ]);

        $settings = $this->makeService(settingsRepo: $settingsRepo)->getProjectIntegrationSettings(5);

        $this->assertSame('', $settings['mattermostWebhookURL']);
        $this->assertArrayHasKey('discordWebhookURL1', $settings);
        $this->assertArrayHasKey('discordWebhookURL3', $settings);
        $this->assertSame([
            'zulipURL' => '',
            'zulipEmail' => '',
            'zulipBotKey' => '',
            'zulipStream' => '',
            'zulipTopic' => '',
        ], $settings['zulipHook']);
    }

    public function test_get_project_integration_settings_unserializes_stored_zulip_hook(): void
    {
        $storedHook = serialize(['zulipURL' => 'https://z.example.com', 'zulipTopic' => 't']);
        $settingsRepo = $this->make(SettingRepository::class, [
            'getSetting' => fn ($key) => str_ends_with($key, 'zulipHook') ? $storedHook : '',
        ]);

        $settings = $this->makeService(settingsRepo: $settingsRepo)->getProjectIntegrationSettings(5);

        $this->assertSame('https://z.example.com', $settings['zulipHook']['zulipURL']);
        $this->assertSame('t', $settings['zulipHook']['zulipTopic']);
    }

    public function test_save_telegram_webhook_does_not_persist_without_token(): void
    {
        $saveCalls = 0;
        $settingsRepo = $this->make(SettingRepository::class, [
            'saveSetting' => function () use (&$saveCalls) {
                $saveCalls++;

                return true;
            },
        ]);

        $result = $this->makeService(settingsRepo: $settingsRepo)->saveTelegramWebhook(7, [
            'telegramBotToken' => '',
            'telegramChatId' => '12345',
            'telegramTopicId' => '',
        ]);

        $this->assertFalse($result['saved']);
        $this->assertSame('missing_token', $result['error']);
        $this->assertSame(0, $saveCalls);
    }

    public function test_save_telegram_webhook_auto_detects_chat_id_when_blank(): void
    {
        $savedValue = null;
        $settingsRepo = $this->make(SettingRepository::class, [
            'saveSetting' => function ($type, $value) use (&$savedValue) {
                $savedValue = $value;

                return true;
            },
        ]);

        $httpClient = $this->make(Client::class, [
            'get' => function ($url, $options) {
                return new Response(200, [], json_encode([
                    'ok' => true,
                    'result' => [
                        [
                            'message' => [
                                'chat' => [
                                    'id' => 987654321,
                                ],
                            ],
                        ],
                    ],
                ]));
            },
        ]);

        $result = $this->makeService(settingsRepo: $settingsRepo, httpClient: $httpClient)->saveTelegramWebhook(7, [
            'telegramBotToken' => '123456:ABC',
            'telegramChatId' => '',
            'telegramTopicId' => '',
        ]);

        $this->assertTrue($result['saved']);
        $this->assertNull($result['error']);
        $this->assertSame('987654321', $result['hook']['telegramChatId']);
        $this->assertNotNull($savedValue);
        $unserialized = safe_unserialize($savedValue, []);
        $this->assertSame('987654321', $unserialized['telegramChatId']);
    }

    public function test_save_telegram_webhook_uses_provided_chat_and_topic_id_without_calling_get_updates(): void
    {
        $getCalled = false;
        $httpClient = $this->make(Client::class, [
            'get' => function () use (&$getCalled) {
                $getCalled = true;

                return new Response(200);
            },
        ]);

        $savedValue = null;
        $settingsRepo = $this->make(SettingRepository::class, [
            'saveSetting' => function ($type, $value) use (&$savedValue) {
                $savedValue = $value;

                return true;
            },
        ]);

        $result = $this->makeService(settingsRepo: $settingsRepo, httpClient: $httpClient)->saveTelegramWebhook(7, [
            'telegramBotToken' => '123456:ABC',
            'telegramChatId' => '-1001234567890',
            'telegramTopicId' => '10',
        ]);

        $this->assertFalse($getCalled, 'getUpdates API must not be called when chat_id is provided directly');
        $this->assertTrue($result['saved']);
        $this->assertSame('-1001234567890', $result['hook']['telegramChatId']);
        $this->assertSame('10', $result['hook']['telegramTopicId']);
    }

    public function test_save_telegram_webhook_reports_chat_not_found_when_auto_detect_fails(): void
    {
        $saveCalls = 0;
        $settingsRepo = $this->make(SettingRepository::class, [
            'saveSetting' => function () use (&$saveCalls) {
                $saveCalls++;

                return true;
            },
        ]);

        $httpClient = $this->make(Client::class, [
            'get' => function () {
                return new Response(200, [], json_encode([
                    'ok' => true,
                    'result' => [],
                ]));
            },
        ]);

        $result = $this->makeService(settingsRepo: $settingsRepo, httpClient: $httpClient)->saveTelegramWebhook(7, [
            'telegramBotToken' => '123456:ABC',
            'telegramChatId' => '',
            'telegramTopicId' => '',
        ]);

        $this->assertFalse($result['saved']);
        $this->assertSame('chat_not_found', $result['error']);
        $this->assertSame(0, $saveCalls);
    }

    public function test_get_project_integration_settings_returns_empty_telegram_hook_when_unset(): void
    {
        $settingsRepo = $this->make(SettingRepository::class, [
            'getSetting' => fn () => '',
        ]);

        $settings = $this->makeService(settingsRepo: $settingsRepo)->getProjectIntegrationSettings(5);

        $this->assertSame([
            'telegramBotToken' => '',
            'telegramChatId' => '',
            'telegramTopicId' => '',
        ], $settings['telegramHook']);
    }

    public function test_get_project_integration_settings_unserializes_stored_telegram_hook(): void
    {
        $storedHook = serialize(['telegramBotToken' => 'tok', 'telegramChatId' => '123', 'telegramTopicId' => '1']);
        $settingsRepo = $this->make(SettingRepository::class, [
            'getSetting' => fn ($key) => str_ends_with($key, 'telegramHook') ? $storedHook : '',
        ]);

        $settings = $this->makeService(settingsRepo: $settingsRepo)->getProjectIntegrationSettings(5);

        $this->assertSame('tok', $settings['telegramHook']['telegramBotToken']);
        $this->assertSame('123', $settings['telegramHook']['telegramChatId']);
        $this->assertSame('1', $settings['telegramHook']['telegramTopicId']);
    }

    public function test_get_project_card_data_sets_last_update_and_status_from_first_comment(): void
    {
        $ticketRepo = $this->make(TicketRepository::class, [
            'getAverageTodoSize' => fn () => 0,
            'getFirstTicket' => fn () => null,
        ]);
        $projectRepo = $this->make(ProjectRepository::class, [
            'getUsersAssignedToProject' => fn () => [],
        ]);
        $commentRepo = $this->make(CommentRepository::class, [
            'getComments' => fn () => [
                ['id' => 99, 'status' => 'on_track', 'text' => 'Looking good'],
            ],
        ]);

        $card = $this->makeService(
            projectRepo: $projectRepo,
            ticketRepo: $ticketRepo,
            commentRepo: $commentRepo,
        )->getProjectCardData(3);

        $this->assertSame(3, $card['id']);
        $this->assertSame('on_track', $card['status']);
        $this->assertIsArray($card['lastUpdate']);
        $this->assertSame(99, $card['lastUpdate']['id']);
    }

    public function test_get_project_card_data_defaults_when_no_comments(): void
    {
        $ticketRepo = $this->make(TicketRepository::class, [
            'getAverageTodoSize' => fn () => 0,
            'getFirstTicket' => fn () => null,
        ]);
        $projectRepo = $this->make(ProjectRepository::class, [
            'getUsersAssignedToProject' => fn () => [],
        ]);
        $commentRepo = $this->make(CommentRepository::class, [
            'getComments' => fn () => [],
        ]);

        $card = $this->makeService(
            projectRepo: $projectRepo,
            ticketRepo: $ticketRepo,
            commentRepo: $commentRepo,
        )->getProjectCardData(3);

        $this->assertFalse($card['lastUpdate']);
        $this->assertSame('', $card['status']);
    }

    /**
     * A service wired for getProjectsCardData(): projects in $existingIds exist, each has one
     * status comment "status-<id>", and the caller may view exactly $viewableIds.
     *
     * @param  array<int, int>  $existingIds
     * @param  array<int, int>|null  $viewableIds  Null = every project is viewable.
     * @param  array<int, array<int, int>>  $existenceQueries  Receives the ids passed to each existence lookup.
     */
    private function makeCardBatchService(array $existingIds, ?array $viewableIds = null, array &$existenceQueries = []): ProjectService
    {
        $ticketRepo = $this->make(TicketRepository::class, [
            'getAverageTodoSize' => fn () => 0,
            'getFirstTicket' => fn () => null,
        ]);
        $projectRepo = $this->make(ProjectRepository::class, [
            'getUsersAssignedToProject' => fn () => [],
            'getExistingProjectIds' => function (array $ids) use ($existingIds, &$existenceQueries) {
                $existenceQueries[] = $ids;

                return array_values(array_intersect($existingIds, $ids));
            },
        ]);
        $commentRepo = $this->make(CommentRepository::class, [
            'getComments' => fn ($module, $id) => [
                ['id' => 1000 + (int) $id, 'status' => 'status-'.$id],
            ],
        ]);
        $permissions = $this->make(PermissionService::class, [
            'currentUserCan' => fn (string $key, ?int $projectId = null) => $key === ProjectsPermissions::VIEW
                && $projectId !== null
                && ($viewableIds === null || in_array($projectId, $viewableIds, true)),
        ]);

        return $this->makeService(
            projectRepo: $projectRepo,
            ticketRepo: $ticketRepo,
            commentRepo: $commentRepo,
            permissions: $permissions,
        );
    }

    public function test_get_projects_card_data_returns_same_shape_as_single_card(): void
    {
        $service = $this->makeCardBatchService([3, 7]);

        $batch = $service->getProjectsCardData([3, 7]);

        $this->assertSame([0, 1], array_keys($batch), 'result must be a JSON list');
        $this->assertSame($service->getProjectCardData(3), $batch[0]);
        $this->assertSame($service->getProjectCardData(7), $batch[1]);
        $this->assertSame(['id', 'progress', 'team', 'lastUpdate', 'status'], array_keys($batch[0]));
        $this->assertSame('status-7', $batch[1]['status']);
    }

    public function test_get_projects_card_data_keeps_requested_order(): void
    {
        $batch = $this->makeCardBatchService([1, 2, 3])->getProjectsCardData([3, 1, 2]);

        $this->assertSame([3, 1, 2], array_column($batch, 'id'));
    }

    public function test_get_projects_card_data_casts_dedupes_and_drops_invalid_ids(): void
    {
        $queries = [];
        $batch = $this->makeCardBatchService([4, 5], null, $queries)
            ->getProjectsCardData(['5', 4, 5, '4', 0, -2, 'abc', null, [9], true]);

        $this->assertSame([5, 4], array_column($batch, 'id'));
        $this->assertSame([[5, 4]], $queries, 'only the normalized distinct ids are looked up');
    }

    public function test_get_projects_card_data_caps_the_number_of_ids(): void
    {
        $limit = ProjectService::PROJECT_CARD_BATCH_LIMIT;
        $all = range(1, $limit + 25);

        $queries = [];
        $batch = $this->makeCardBatchService($all, null, $queries)->getProjectsCardData($all);

        $this->assertCount($limit, $batch);
        $this->assertSame(range(1, $limit), array_column($batch, 'id'));
        $this->assertCount($limit, $queries[0]);
    }

    public function test_get_projects_card_data_omits_denied_and_missing_projects(): void
    {
        // 8 exists but is not viewable; 99 is viewable-by-role but does not exist.
        $batch = $this->makeCardBatchService([2, 8, 6], [2, 6, 99])->getProjectsCardData([8, 2, 99, 6]);

        $this->assertSame([2, 6], array_column($batch, 'id'));
    }

    public function test_get_projects_card_data_returns_empty_list_without_valid_ids(): void
    {
        $queries = [];
        $service = $this->makeCardBatchService([1], null, $queries);

        $this->assertSame([], $service->getProjectsCardData([]));
        $this->assertSame([], $service->getProjectsCardData([0, -1, 'x']));
        $this->assertSame([], $queries, 'no query when nothing valid was asked for');
    }

    public function test_get_projects_card_data_is_entity_scoped_and_rpc_exposed(): void
    {
        $method = new \ReflectionMethod(ProjectService::class, 'getProjectsCardData');
        $attrs = $method->getAttributes(\Leantime\Core\Auth\Permissions\RequiresPermission::class);

        $this->assertCount(1, $attrs);
        $gate = $attrs[0]->newInstance();
        $this->assertSame(ProjectsPermissions::VIEW, $gate->permission);
        $this->assertTrue($gate->entityScoped, 'per-project authorization happens in the method body');
        $this->assertMatchesRegularExpression('/^\s*\*\s*@api\b/m', (string) $method->getDocComment());
    }

    /**
     * duplicateProject() copies the To-Dos first, then re-links each copy to the copies of its
     * milestone and parent. It used to patch the ORIGINAL ticket, which moved the source
     * project's To-Dos onto the new project's milestones and left the copies unlinked.
     */
    public function test_duplicate_project_links_the_copies_and_leaves_the_originals_alone(): void
    {
        session(['userdata.role' => 'admin']);

        $sourceTickets = [
            (object) ['id' => 10, 'type' => 'milestone', 'milestoneid' => '', 'dependingTicketId' => ''],
            (object) ['id' => 11, 'type' => 'task', 'milestoneid' => 10, 'dependingTicketId' => ''],
            (object) ['id' => 12, 'type' => 'subtask', 'milestoneid' => '', 'dependingTicketId' => 11],
            (object) ['id' => 13, 'type' => 'task', 'milestoneid' => 99, 'dependingTicketId' => ''],
        ];
        foreach ($sourceTickets as $ticket) {
            foreach (['headline', 'description', 'editorId', 'status', 'storypoints', 'hourRemaining', 'planHours', 'priority', 'acceptanceCriteria', 'tags', 'editFrom', 'editTo', 'dateToFinish'] as $field) {
                $ticket->$field = '';
            }
        }

        $nextCopyId = 100;
        $patches = [];
        $ticketRepo = $this->make(TicketRepository::class, [
            'getAllByProjectId' => fn () => $sourceTickets,
            'addTicket' => function () use (&$nextCopyId) {
                return $nextCopyId++;
            },
            'patchTicket' => function ($id, array $values) use (&$patches) {
                $patches[$id] = $values;

                return true;
            },
        ]);
        $projectRepo = $this->make(ProjectRepository::class, [
            'getProject' => fn ($id) => ['id' => $id, 'details' => '', 'state' => 0, 'hourBudget' => 0, 'dollarBudget' => 0, 'psettings' => 'restricted'],
            'addProject' => fn () => 50,
            'getModuleOverrides' => fn () => [],
            'updateModuleOverrides' => fn () => true,
        ]);
        $settingsRepo = $this->make(SettingRepository::class, ['getSetting' => fn () => false]);

        // No boards to copy; only the To-Do links are under test.
        foreach ([\Leantime\Domain\Ideas\Repositories\Ideas::class, \Leantime\Domain\Goalcanvas\Repositories\Goalcanvas::class, \Leantime\Domain\Wiki\Repositories\Wiki::class, \Leantime\Domain\Blueprints\Repositories\Blueprints::class] as $canvasRepository) {
            app()->instance($canvasRepository, $this->make($canvasRepository, ['getAllCanvas' => fn () => []]));
        }

        $service = $this->makeService(projectRepo: $projectRepo, ticketRepo: $ticketRepo, settingsRepo: $settingsRepo);

        $this->assertSame(50, $service->duplicateProject(5, 3, 'Copy', '', false));

        // Copies: 10 → 100, 11 → 101, 12 → 102, 13 → 103.
        $this->assertSame([101 => ['milestoneid' => 100], 102 => ['dependingTicketId' => 101]], $patches);
        $this->assertEmpty(array_intersect(array_keys($patches), [10, 11, 12, 13]), 'source To-Dos must not be modified');
    }

    // ---------------------------------------------------------------------
    // Authorized JSON-RPC entry points for project sort/status/patch.
    // The /api/projects controller (which had a route-level gate) was retired,
    // so these wrappers must self-authorize: manager+ AND access to each project.
    // ---------------------------------------------------------------------

    public function test_user_can_manage_project_allows_admin_without_explicit_assignment(): void
    {
        session(['userdata.role' => 'admin']);

        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => false,
        ]);

        // Admins/owners manage every project regardless of assignment.
        $this->assertTrue($this->makeService(projectRepo: $projectRepo)->userCanManageProject(99));
    }

    public function test_user_can_manage_project_requires_assignment_for_managers(): void
    {
        session(['userdata.role' => 'manager']);

        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => false,
        ]);

        $this->assertFalse($this->makeService(projectRepo: $projectRepo)->userCanManageProject(99));
    }

    public function test_user_can_manage_project_follows_the_grant_not_the_role_rank(): void
    {
        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => true,
        ]);

        // An editor-ranked role that was given projects.edit manages its projects...
        session(['userdata.role' => 'editor']);
        $grantedEditor = $this->makeService(projectRepo: $projectRepo, permissions: $this->permissionsGranting([ProjectsPermissions::EDIT]));
        $this->assertTrue($grantedEditor->userCanManageProject(99));

        // ...and a manager whose projects.edit was taken away does not.
        session(['userdata.role' => 'manager']);
        $revokedManager = $this->makeService(projectRepo: $projectRepo, permissions: $this->permissionsGranting([]));
        $this->assertFalse($revokedManager->userCanManageProject(99));
    }

    public function test_delete_project_follows_the_grant_not_the_role_rank(): void
    {
        $deletedIds = [];
        $projectRepo = $this->make(ProjectRepository::class, [
            'deleteProject' => function ($id) use (&$deletedIds) {
                $deletedIds[] = $id;
            },
            'deleteAllUserRelations' => null,
        ]);

        session(['userdata.role' => 'manager']);
        $revokedManager = $this->makeService(projectRepo: $projectRepo, permissions: $this->permissionsGranting([]));
        $this->assertFalse($revokedManager->deleteProject(5));

        session(['userdata.role' => 'editor']);
        $grantedEditor = $this->makeService(projectRepo: $projectRepo, permissions: $this->permissionsGranting([ProjectsPermissions::DELETE]));
        $this->assertTrue($grantedEditor->deleteProject(5));

        $this->assertSame([5], $deletedIds);
    }

    public function test_patch_project_status_and_sorting_rejects_non_manager(): void
    {
        session(['userdata.role' => 'editor']);

        $patchCalls = 0;
        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => true,
            'patch' => function () use (&$patchCalls) {
                $patchCalls++;

                return true;
            },
        ]);

        $thrown = null;
        try {
            $this->makeService(projectRepo: $projectRepo)
                ->patchProjectStatusAndSorting(['3' => 'item[]=5']);
        } catch (AuthorizationException $e) {
            $thrown = $e;
        }

        $this->assertInstanceOf(AuthorizationException::class, $thrown, 'Editors must not be able to re-status projects');
        $this->assertSame(0, $patchCalls, 'Unauthorized request must not persist any sorting');
    }

    public function test_patch_project_status_and_sorting_rejects_manager_without_project_access(): void
    {
        session(['userdata.role' => 'manager']);

        $patchCalls = 0;
        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => false,
            'patch' => function () use (&$patchCalls) {
                $patchCalls++;

                return true;
            },
        ]);

        $thrown = null;
        try {
            $this->makeService(projectRepo: $projectRepo)
                ->patchProjectStatusAndSorting(['3' => 'item[]=5']);
        } catch (AuthorizationException $e) {
            $thrown = $e;
        }

        $this->assertInstanceOf(AuthorizationException::class, $thrown, 'A manager smuggling a project they cannot access must be blocked');
        $this->assertSame(0, $patchCalls);
    }

    public function test_patch_project_status_and_sorting_allows_manager_with_access(): void
    {
        session(['userdata.role' => 'manager']);

        $patched = [];
        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => true,
            'patch' => function ($id, $values) use (&$patched) {
                $patched[] = ['id' => $id, 'values' => $values];

                return true;
            },
        ]);

        $result = $this->makeService(projectRepo: $projectRepo)
            ->patchProjectStatusAndSorting(['3' => 'item[]=5&item[]=6']);

        $this->assertTrue($result);
        $this->assertCount(2, $patched, 'Both serialized projects must be re-sorted');
        $this->assertSame('5', $patched[0]['id']);
        $this->assertSame(3, (int) $patched[0]['values']['state']);
    }

    public function test_sort_projects_rejects_when_user_cannot_manage_target_project(): void
    {
        session(['userdata.role' => 'manager']);

        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => false,
        ]);

        $thrown = null;
        try {
            $this->makeService(projectRepo: $projectRepo)->sortProjects(['pgm-5' => 1]);
        } catch (AuthorizationException $e) {
            $thrown = $e;
        }

        $this->assertInstanceOf(AuthorizationException::class, $thrown);
    }

    public function test_sort_projects_resolves_ticket_to_its_project_for_authorization(): void
    {
        session(['userdata.role' => 'manager']);

        $checkedProjectId = null;
        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => function ($userId, $projectId) use (&$checkedProjectId) {
                $checkedProjectId = $projectId;

                return false; // deny so we stop before delegating to the Tickets service
            },
        ]);
        $ticket = new \Leantime\Domain\Tickets\Models\Tickets;
        $ticket->projectId = 9;
        $ticketRepo = $this->make(TicketRepository::class, [
            'getTicket' => fn () => $ticket,
        ]);

        $thrown = null;
        try {
            $this->makeService(projectRepo: $projectRepo, ticketRepo: $ticketRepo)
                ->sortProjects(['ticket-7' => 1]);
        } catch (AuthorizationException $e) {
            $thrown = $e;
        }

        $this->assertInstanceOf(AuthorizationException::class, $thrown);
        $this->assertSame(9, $checkedProjectId, 'Authorization must check the ticket\'s project, not the ticket id');
    }

    public function test_patch_project_rejects_non_manager(): void
    {
        session(['userdata.role' => 'editor']);

        $patchCalls = 0;
        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => true,
            'patch' => function () use (&$patchCalls) {
                $patchCalls++;

                return true;
            },
        ]);

        $thrown = null;
        try {
            $this->makeService(projectRepo: $projectRepo)->patchProject(5, ['sortIndex' => 2]);
        } catch (AuthorizationException $e) {
            $thrown = $e;
        }

        $this->assertInstanceOf(AuthorizationException::class, $thrown);
        $this->assertSame(0, $patchCalls);
    }

    public function test_patch_project_allows_manager_and_strips_control_fields(): void
    {
        session(['userdata.role' => 'manager']);

        $patchedValues = null;
        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => true,
            'patch' => function ($id, $values) use (&$patchedValues) {
                $patchedValues = $values;

                return true;
            },
        ]);

        $service = $this->makeService(projectRepo: $projectRepo);
        $service->setPermissionService($this->make(\Leantime\Core\Auth\Permissions\PermissionService::class, [
            'authorize' => fn () => null,
            'currentUserCan' => fn () => true,
        ]));

        $result = $service->patchProject(5, ['act' => 'projects.x', 'id' => 5, 'sortIndex' => 2, 'start' => '2026-01-01']);

        $this->assertTrue($result);
        $this->assertArrayNotHasKey('act', $patchedValues, 'Control fields must be stripped before persisting');
        $this->assertArrayNotHasKey('id', $patchedValues);
        $this->assertSame(2, $patchedValues['sortIndex']);
    }

    // ---- permission-engine: recursion guardrail ---------------------------

    /**
     * THE recursion guardrail. The permission engine calls isUserAssignedToProject() and
     * getProjectRole() during every project-scoped authorization, so those two methods must never
     * invoke the engine in-body — otherwise authorize() → currentUserCan() → isUserAssignedToProject()
     * → authorize() → ∞. A PermissionService stub that fails the test if touched proves it.
     */
    public function test_access_resolution_methods_never_invoke_the_permission_engine(): void
    {
        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => true,
            'getUserProjectRelation' => fn () => [['projectRole' => 'editor']],
        ]);

        $tripwire = $this->make(\Leantime\Core\Auth\Permissions\PermissionService::class, [
            'currentUserCan' => fn () => $this->fail('isUserAssignedToProject/getProjectRole must NOT call the permission engine (infinite-recursion guard).'),
            'authorize' => fn () => $this->fail('access-resolution methods must NOT authorize in-body (infinite-recursion guard).'),
        ]);

        $service = $this->makeService(projectRepo: $projectRepo);
        $service->setPermissionService($tripwire);

        // Neither call may touch the engine.
        $this->assertTrue($service->isUserAssignedToProject(1, 5));
        $this->assertSame('editor', $service->getProjectRole(1, 5));
    }

    /**
     * getProjectRole() must resolve "no explicit role" to '' so callers fall back to the global
     * role. This locks in the fix for the "Inherit" lockout: the legacy 0 role (written when
     * "inherit" was cast to int), a missing relation, unknown/junk keys, and admin/owner keys all
     * map to '', while a real assignable key is returned unchanged.
     *
     * @dataProvider projectRoleResolutionProvider
     */
    public function test_get_project_role_resolves_inherit_and_junk_to_empty(mixed $stored, string $expected): void
    {
        $relation = $stored === '__none__' ? [] : [['projectRole' => $stored]];
        $projectRepo = $this->make(ProjectRepository::class, [
            'getUserProjectRelation' => fn () => $relation,
        ]);

        $service = $this->makeService(projectRepo: $projectRepo);

        $this->assertSame($expected, $service->getProjectRole(1, 5));
    }

    public static function projectRoleResolutionProvider(): array
    {
        return [
            'legacy int 0 -> inherit' => [0, ''],
            'legacy string 0 -> inherit' => ['0', ''],
            'empty string -> inherit' => ['', ''],
            'no relation row -> inherit' => ['__none__', ''],
            'unknown numeric key -> inherit' => ['999', ''],
            'admin key not assignable -> inherit' => ['40', ''],
            'owner key not assignable -> inherit' => ['50', ''],
            'valid editor key preserved' => ['20', '20'],
            'valid readonly key preserved' => ['5', '5'],
            'legacy inherit sentinel -> inherit' => ['inherit', ''],
            'legacy inherited sentinel -> inherit' => ['inherited', ''],
            'legacy uppercase Inherit sentinel -> inherit' => ['Inherit', ''],
            'legacy role name preserved' => ['editor', 'editor'],
        ];
    }

    /**
     * Reflection lock: the engine-reachable access methods must carry NO #[RequiresPermission]
     * dispatch attribute (a dispatch gate on them would re-enter the engine), and the mutations/reads
     * must carry the expected gate. Locks the recursion-safe contract in CI.
     */
    public function test_rpc_surface_contract(): void
    {
        $gate = function (string $method): ?array {
            $attrs = (new \ReflectionMethod(ProjectService::class, $method))
                ->getAttributes(\Leantime\Core\Auth\Permissions\RequiresPermission::class);
            if ($attrs === []) {
                return null;
            }
            $a = $attrs[0]->newInstance();

            return ['permission' => $a->permission, 'global' => $a->global, 'projectIdParam' => $a->projectIdParam];
        };

        // Engine-reachable / access-resolution: MUST be ungated (the recursion guard). Note
        // getUsersAssignedToProject is NOT in this set — the engine never calls it, so it is safely
        // view-gated below to close its member-list IDOR.
        foreach (['getProjectRole', 'isUserAssignedToProject', 'getUserProjectRelation', 'userCanManageProject', 'getProjectsUserHasAccessTo'] as $m) {
            $this->assertNull($gate($m), "$m must carry NO #[RequiresPermission] (recursion guard)");
        }

        // Mutations: global manager+.
        foreach (['addProject' => 'projects.create', 'duplicateProject' => 'projects.create', 'editProject' => 'projects.edit', 'patch' => 'projects.edit', 'patchProject' => 'projects.edit', 'updateProjectUsers' => 'projects.edit', 'saveSlackWebhook' => 'projects.edit', 'saveZulipWebhook' => 'projects.edit', 'saveTelegramWebhook' => 'projects.edit', 'deleteProject' => 'projects.delete', 'editUserProjectRelations' => 'projects.edit', 'addUserToProject' => 'projects.edit'] as $m => $perm) {
            $g = $gate($m);
            $this->assertNotNull($g, "$m must be gated");
            $this->assertSame($perm, $g['permission'], $m);
            $this->assertTrue($g['global'], "$m must be global-scoped (manager+ company-wide)");
        }

        // By-id reads: project-scoped view.
        foreach (['getProject', 'getProjectProgress', 'getProjectName', 'getProjectIntegrationSettings', 'getProjectCardData', 'getUsersAssignedToProject'] as $m) {
            $g = $gate($m);
            $this->assertNotNull($g, "$m must be gated");
            $this->assertSame('projects.view', $g['permission'], $m);
            $this->assertNotNull($g['projectIdParam'], "$m must bind to the requested project id");
        }
    }

    /**
     * The $userId-param reads pin to the SESSION user for non-admins, closing the cross-user spoof
     * (an RPC caller could otherwise list another user's projects by passing a foreign id).
     */
    public function test_assigned_to_user_reads_pin_to_session_user_for_non_admins(): void
    {
        session(['userdata.id' => 1]);  // non-admin session user

        $capturedUserId = null;
        $projectRepo = $this->make(ProjectRepository::class, [
            'getUserProjectRelation' => function ($userId) use (&$capturedUserId) {
                $capturedUserId = $userId;

                return [];
            },
        ]);

        // Caller passes a FOREIGN userId (99); the read must be scoped to the session user (1).
        $this->makeService(projectRepo: $projectRepo)->getProjectIdAssignedToUser(99);

        $this->assertSame(1, $capturedUserId, 'a non-admin must not be able to read another user\'s project assignments');
    }

    public function test_user_managers_read_the_edited_users_project_assignments(): void
    {
        // The user editor runs as a role that may not see every project but manages users.
        session(['userdata.id' => 1, 'userdata.role' => 'editor']);

        $capturedUserId = null;
        $projectRepo = $this->make(ProjectRepository::class, [
            'getUserProjectRelation' => function ($userId) use (&$capturedUserId) {
                $capturedUserId = $userId;

                return [];
            },
        ]);

        foreach ([UsersPermissions::VIEW, UsersPermissions::EDIT] as $userManagementGrant) {
            $capturedUserId = null;
            $this->makeService(projectRepo: $projectRepo, permissions: $this->permissionsGranting([$userManagementGrant]))
                ->getProjectIdAssignedToUser(99);

            $this->assertSame(99, $capturedUserId, "with {$userManagementGrant}, saving the user editor must not replace the edited user's memberships with the caller's");
        }
    }

    public function test_user_managers_cannot_read_full_projects_through_another_account(): void
    {
        session(['userdata.id' => 1, 'userdata.role' => 'editor']);

        $capturedUserIds = [];
        $projectRepo = $this->make(ProjectRepository::class, [
            'getUserProjects' => function (...$arguments) use (&$capturedUserIds) {
                $capturedUserIds[] = $arguments['userId'] ?? $arguments[0];

                return [];
            },
        ]);
        $service = $this->makeService(
            projectRepo: $projectRepo,
            permissions: $this->permissionsGranting([UsersPermissions::VIEW, UsersPermissions::EDIT])
        );

        // Project records and budgets stay scoped to the caller's own access.
        $service->getProjectsUserHasAccessTo(99);
        $service->getProjectsAssignedToUser(99);

        $this->assertSame([1, 1], $capturedUserIds);
    }

    // ---- Project hierarchy safety (#3540: cyclic parents hung every page via the project selector) ----

    public function test_find_my_children_builds_nested_hierarchy(): void
    {
        $projects = [
            ['id' => 1, 'parent' => 0, 'name' => 'Program'],
            ['id' => 2, 'parent' => 1, 'name' => 'Project'],
            ['id' => 3, 'parent' => 2, 'name' => 'Subproject'],
            ['id' => 4, 'parent' => 0, 'name' => 'Standalone'],
        ];

        $hierarchy = $this->makeService()->findMyChildren(0, $projects);

        $this->assertCount(2, $hierarchy);
        $this->assertSame(2, $hierarchy[0]['children'][0]['id']);
        $this->assertSame(3, $hierarchy[0]['children'][0]['children'][0]['id']);
        $this->assertArrayNotHasKey('children', $hierarchy[1]);
    }

    public function test_find_my_children_does_not_recurse_on_self_referential_parent(): void
    {
        $projects = [
            ['id' => 1, 'parent' => 0, 'name' => 'Root'],
            ['id' => 2, 'parent' => 2, 'name' => 'Self-parented'],
        ];

        $hierarchy = $this->makeService()->findMyChildren(0, $projects);

        $this->assertCount(1, $hierarchy, 'must terminate instead of recursing on a self-parented project');
        $this->assertSame(1, $hierarchy[0]['id']);
    }

    public function test_clean_parent_relationship_reroots_self_parent_and_cycles(): void
    {
        $projects = [
            ['id' => 1, 'parent' => 1, 'name' => 'Self-parented'],
            ['id' => 2, 'parent' => 3, 'name' => 'Cycle A'],
            ['id' => 3, 'parent' => 2, 'name' => 'Cycle B'],
            ['id' => 4, 'parent' => 99, 'name' => 'Orphan'],
            ['id' => 5, 'parent' => 1, 'name' => 'Valid child'],
        ];

        $service = $this->makeService();
        $clean = $service->cleanParentRelationship($projects);
        $byId = array_column($clean, null, 'id');

        $this->assertSame(0, $byId[1]['parent'], 'self-parent must be re-rooted');
        $this->assertSame(0, $byId[2]['parent'], 'cycle members must be re-rooted');
        $this->assertSame(0, $byId[3]['parent'], 'cycle members must be re-rooted');
        $this->assertSame(0, $byId[4]['parent'], 'orphans must be re-rooted');
        $this->assertSame(1, $byId[5]['parent'], 'valid parent links must be preserved');

        // The full pipeline must terminate and surface every project.
        $hierarchy = $service->findMyChildren(0, $clean);
        $this->assertCount(4, $hierarchy);
    }

    /**
     * Regression for #3617: a child whose parent is a top-level strategy/program (parent = NULL)
     * must stay nested. isset() reports false for a NULL parent value, which previously re-rooted
     * every such child to 0 and dropped it out of its strategy group in the Projects dropdown.
     */
    public function test_clean_parent_relationship_keeps_children_of_top_level_parents(): void
    {
        $projects = [
            ['id' => 1, 'parent' => null, 'name' => 'Strategy'],   // top-level container
            ['id' => 2, 'parent' => 1, 'name' => 'Project under strategy'],
            ['id' => 3, 'parent' => 1, 'name' => 'Plan under strategy'],
        ];

        $service = $this->makeService();
        $byId = array_column($service->cleanParentRelationship($projects), null, 'id');

        $this->assertSame(1, $byId[2]['parent'], 'child of a NULL-parent strategy must stay nested');
        $this->assertSame(1, $byId[3]['parent'], 'plan of a NULL-parent strategy must stay nested');

        // And the child must actually appear under the strategy in the assembled hierarchy.
        $hierarchy = $service->findMyChildren(0, array_values($byId));
        $this->assertCount(1, $hierarchy, 'only the top-level strategy sits at the root');
        $this->assertSame(1, $hierarchy[0]['id']);
        $this->assertCount(2, $hierarchy[0]['children'], 'project and plan nest under the strategy');
    }

    /**
     * addUserToProject() must be ADDITIVE. The sibling
     * editUserProjectRelations() is a full replace that deletes any
     * relation not in the array it is handed, so the whole point of this
     * method is that it never touches a user's other memberships.
     */
    public function test_add_user_to_project_inserts_when_not_a_member(): void
    {
        $added = [];
        $repo = $this->makeEmpty(ProjectRepository::class, [
            'getProject' => fn () => ['id' => 42],
            'isUserMemberOfProject' => fn () => false,
            'addProjectRelation' => function ($userId, $projectId, $role) use (&$added) {
                $added[] = [$userId, $projectId, $role];
            },
            'editUserProjectRelations' => fn () => throw new \LogicException(
                'addUserToProject must never call the destructive full-replace method'
            ),
        ]);

        $result = $this->makeService($repo, userRepo: $this->validUserRepo())->addUserToProject(7, 42, 'contributor');

        $this->assertTrue($result, 'a new membership reports true');
        $this->assertSame([[7, 42, 'contributor']], $added);
    }

    /**
     * Membership is not access. isUserAssignedToProject() returns true for
     * every admin and owner whether or not a relation row exists, so using
     * it as the idempotence check would make this method a permanent
     * no-op for exactly those users: an admin could never be put on a
     * project team, and the caller would be told "already a member" about
     * someone who is not on the team at all.
     */
    public function test_add_user_to_project_adds_admin_who_has_access_but_no_membership(): void
    {
        $added = [];
        $repo = $this->makeEmpty(ProjectRepository::class, [
            'getProject' => fn () => ['id' => 42],
            // An admin: reaches every project...
            'isUserAssignedToProject' => fn () => true,
            // ...but holds no relation row for this one.
            'isUserMemberOfProject' => fn () => false,
            'addProjectRelation' => function ($userId, $projectId, $role) use (&$added) {
                $added[] = [$userId, $projectId, $role];
            },
        ]);

        $this->assertTrue($this->makeService($repo, userRepo: $this->validUserRepo())->addUserToProject(7, 42));
        $this->assertSame([[7, 42, '']], $added, 'access must not be mistaken for membership');
    }

    /**
     * Idempotence guard. zp_relationuserproject has no unique index on
     * (userId, projectId), so a blind insert would duplicate the row and
     * show the person twice on the project team.
     */
    public function test_add_user_to_project_is_idempotent_for_existing_member(): void
    {
        $addCalls = 0;
        $repo = $this->makeEmpty(ProjectRepository::class, [
            'getProject' => fn () => ['id' => 42],
            'isUserMemberOfProject' => fn () => true,
            'addProjectRelation' => function () use (&$addCalls) {
                $addCalls++;
            },
        ]);

        $result = $this->makeService($repo, userRepo: $this->validUserRepo())->addUserToProject(7, 42);

        $this->assertFalse($result, 'an existing membership reports false');
        $this->assertSame(0, $addCalls, 'must not insert a duplicate relation row');
    }

    /**
     * Invalid ids must fail before touching persistence — a 0 userId
     * reaching addProjectRelation would create an orphan relation row.
     */
    public function test_add_user_to_project_rejects_invalid_ids(): void
    {
        $touched = 0;
        $repo = $this->makeEmpty(ProjectRepository::class, [
            'isUserMemberOfProject' => function () use (&$touched) {
                $touched++;

                return false;
            },
            'addProjectRelation' => function () use (&$touched) {
                $touched++;
            },
        ]);
        $service = $this->makeService($repo);

        $this->assertFalse($service->addUserToProject(0, 42));
        $this->assertFalse($service->addUserToProject(7, 0));
        $this->assertSame(0, $touched, 'invalid ids must not reach the repository');
    }

    /**
     * Non-zero ids that don't resolve to real rows must also fail closed —
     * isUserMemberOfProject() returns false for a missing user/project, so
     * without the existence guard addProjectRelation() would write an orphan
     * relation row for a user or project that isn't there.
     */
    public function test_add_user_to_project_rejects_nonexistent_user_or_project(): void
    {
        $added = 0;
        $mkRepo = fn (bool $projectExists) => $this->makeEmpty(ProjectRepository::class, [
            'getProject' => fn () => $projectExists ? ['id' => 42] : false,
            'isUserMemberOfProject' => fn () => false,
            'addProjectRelation' => function () use (&$added) {
                $added++;
            },
        ]);
        $missingUser = $this->makeEmpty(UserRepository::class, ['getUser' => fn () => false]);

        // User missing (project resolves fine).
        $this->assertFalse(
            $this->makeService($mkRepo(true), userRepo: $missingUser)->addUserToProject(999, 42)
        );
        // Project missing (user resolves fine).
        $this->assertFalse(
            $this->makeService($mkRepo(false), userRepo: $this->validUserRepo())->addUserToProject(7, 999)
        );

        $this->assertSame(0, $added, 'a non-existent user or project must never reach addProjectRelation');
    }

    /**
     * Switches the test user to $timezone with US date formats.
     */
    private function useUserTimezone(string $timezone): void
    {
        session([
            'usersettings.timezone' => $timezone,
            'usersettings.date_format' => 'm/d/Y',
            'usersettings.time_format' => 'h:i A',
        ]);

        CarbonImmutable::mixin(new CarbonMacros($timezone, 'en-US', 'm/d/Y', 'h:i A'));
    }

    /**
     * Creates a project with the given user-entered dates and returns the start/end
     * values the service handed to the repository.
     *
     * @return array{start: mixed, end: mixed}
     */
    private function createProjectWithDates(string $start, string $end): array
    {
        $stored = [];
        $projectRepo = $this->makeEmpty(ProjectRepository::class, [
            'addProject' => function (array $values) use (&$stored) {
                $stored = $values;

                return 12;
            },
        ]);

        $this->makeService($projectRepo)->createProject([
            'name' => 'Launch',
            'clientId' => 0,
            'start' => $start,
            'end' => $end,
        ]);

        return ['start' => $stored['start'] ?? null, 'end' => $stored['end'] ?? null];
    }

    /**
     * New project dates are entered in the user's timezone and must be stored in UTC, because
     * everything reads zp_projects.start/end back as UTC. They used to be stored as the user's
     * local time, so a user behind UTC saw the start date a day early.
     */
    public function test_create_project_stores_dates_in_utc_for_a_user_behind_utc(): void
    {
        $this->useUserTimezone('America/New_York');

        $stored = $this->createProjectWithDates('11/02/2026', '03/31/2027');

        // Start / end of day in New York (EST, UTC-5), expressed in UTC.
        $this->assertSame('2026-11-02 05:00:00', $stored['start']);
        $this->assertSame('2027-04-01 03:59:59', $stored['end']);
        $this->assertSame('11/02/2026', format($stored['start'])->date());
        $this->assertSame('03/31/2027', format($stored['end'])->date());
    }

    /**
     * Mirror case: ahead of UTC the local end of day used to be read back as the next day.
     */
    public function test_create_project_keeps_its_calendar_dates_for_a_user_ahead_of_utc(): void
    {
        $this->useUserTimezone('Asia/Tokyo');

        $stored = $this->createProjectWithDates('11/02/2026', '03/31/2027');

        $this->assertSame('2026-11-01 15:00:00', $stored['start']);
        $this->assertSame('11/02/2026', format($stored['start'])->date());
        $this->assertSame('03/31/2027', format($stored['end'])->date());
    }

    /**
     * A UserRepository stub whose getUser() resolves to a real row, for the
     * addUserToProject() tests that need the existence guard to pass.
     */
    private function validUserRepo(): UserRepository
    {
        return $this->makeEmpty(UserRepository::class, ['getUser' => fn () => ['id' => 7]]);
    }

    /**
     * Runs $act with a listener on ProjectMemberAdded and returns the user ids it was told
     * about. The dispatcher's registry is restored afterwards.
     *
     * @return array<int, int>
     */
    private function membersAddedDuring(\Closure $act): array
    {
        $reflection = new \ReflectionClass(\Leantime\Core\Events\EventDispatcher::class);
        $snapshot = [];
        foreach (['eventRegistry', 'available_hooks', 'patternMatchCache', 'compiledPatternCache', 'eventRegistryVersion'] as $prop) {
            $snapshot[$prop] = $reflection->getProperty($prop)->getValue();
        }

        $added = [];
        try {
            \Leantime\Core\Events\EventDispatcher::add_event_listener(
                \Leantime\Domain\Projects\Events\ProjectMemberAdded::class,
                function (...$args) use (&$added) {
                    foreach ($args as $arg) {
                        if ($arg instanceof \Leantime\Domain\Projects\Events\ProjectMemberAdded) {
                            $added[] = $arg->userId;
                        }
                    }
                }
            );
            $act();
        } finally {
            foreach ($snapshot as $prop => $value) {
                $reflection->getProperty($prop)->setValue(null, $value);
            }
        }

        return $added;
    }

    /**
     * The repository only saves team members that come with a project role; nobody else may
     * be told "added you to <project>".
     */
    public function test_a_new_project_tells_only_the_members_it_actually_saved(): void
    {
        $projectRepo = $this->makeEmpty(ProjectRepository::class, ['addProject' => fn () => 12]);
        $service = $this->makeService($projectRepo);

        $added = $this->membersAddedDuring(fn () => $service->createProject([
            'name' => 'Launch',
            'clientId' => 0,
            'type' => 'project',
            'start' => null,
            'end' => null,
            'assignedUsers' => [['id' => 2, 'projectRole' => 'editor'], ['id' => 3], ['id' => 4, 'projectRole' => '']],
        ]));

        $this->assertSame([2, 4], $added);
    }

    /**
     * A rename is told with the project's new name: the notification carries the project as
     * saved, not as it was before the edit.
     */
    public function test_a_project_edit_notifies_with_the_saved_values(): void
    {
        session(['currentProject' => 9]);
        $captured = null;
        $service = $this->construct(ProjectService::class, [
            $this->make(ProjectRepository::class),
            $this->make(TicketRepository::class),
            $this->make(SettingRepository::class),
            $this->make(LanguageCore::class, ['__' => fn ($key) => '%s %s']),
            $this->make(Messengers::class),
            $this->make(NotificationService::class),
            $this->make(FileService::class),
            $this->make(Avatarcreator::class),
            $this->make(QueueRepository::class),
            $this->make(UserRepository::class),
            $this->make(CommentRepository::class),
            $this->make(ClientRepository::class),
            $this->make(Client::class),
            $this->make(Webhooks::class),
        ], [
            'editProject' => fn () => true,
            'notifyProjectUsers' => function (Notification $notification) use (&$captured) {
                $captured = $notification;
            },
        ]);

        $service->editProjectAndNotify(['name' => 'Pricing launch'], 9, ['id' => 9, 'name' => 'Draft launch', 'clientId' => 3], 'https://x', 1, 'Gloria');

        $this->assertSame('Pricing launch', $captured->entity['name']);
        $this->assertSame(9, $captured->entity['id']);
        $this->assertSame(3, $captured->entity['clientId'], 'fields the edit left alone stay');
    }
}

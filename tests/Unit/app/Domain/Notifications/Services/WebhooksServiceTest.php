<?php

namespace Unit\app\Domain\Notifications\Services;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Log;
use Leantime\Domain\Notifications\Jobs\DeliverPersonalWebhooks;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
use Leantime\Domain\Notifications\Services\WebhookQueue;
use Leantime\Domain\Notifications\Services\Webhooks;
use Leantime\Domain\Notifications\Services\WebhookTransport;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Leantime\Domain\Queue\Workers\Workers;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Mockery;
use Unit\TestCase;

/**
 * Personal notification webhooks, end to end across the queue.
 *
 * queueToUsers() runs inside the request that raised the notification: it may
 * only read settings/users/projects and write one WEBHOOKS-channel zp_queue row
 * per recipient — no HTTP, no DNS. The scheduler's WebhookQueue later runs those
 * rows through DeliverPersonalWebhooks, and sendToUsers() re-reads opt-in,
 * endpoint, user status and project access before posting anything.
 *
 * Everything the service reads lives in one in-memory "world" the tests change
 * between enqueue and delivery. Project access is decided by the real
 * ProjectRepository::isUserAssignedToProject() over that world (only its
 * zp_relationuserproject lookup is faked), so the public / client / admin /
 * team rules are the production rules. The transport is a recording stub;
 * endpoints are public IP literals so nothing depends on live DNS.
 */
class WebhooksServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private const SECRET_ENDPOINT = 'https://1.1.1.1/hooks/secret-token?sig=secret-sig';

    private const PROJECT_ID = 5;

    private const CLIENT_ID = 3;

    private const AUTHOR_ID = 1;

    /**
     * zp_user rows, zp_projects rows, team memberships and zp_settings values.
     */
    private object $world;

    /**
     * Every transport post, in order.
     *
     * @var array<int, array{url: string, payload: array<string, mixed>}>
     */
    private array $posts = [];

    /**
     * The fake zp_queue table: rows written by addMessageToQueue, keyed by msghash.
     *
     * @var array<string, array<string, mixed>>
     */
    private array $queueTable = [];

    /**
     * Channels listMessageInQueue was asked for, in order.
     *
     * @var array<int, string>
     */
    private array $listedChannels = [];

    /**
     * Endpoints whose transport post throws, with the exception to throw.
     *
     * @var array<string, \Throwable>
     */
    private array $failingEndpoints = [];

    private QueueRepository $queueRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->world = new class
        {
            /** @var array<int, array<string, mixed>> zp_user rows by id */
            public array $users = [];

            /** @var array<int, array<string, mixed>> zp_projects rows by id */
            public array $projects = [];

            /** @var array<string, true> "userId:projectId" rows of zp_relationuserproject */
            public array $teams = [];

            /** @var array<string, string> zp_settings key => value */
            public array $settings = [];
        };

        // Project 5 is visible to its team only and belongs to client 3.
        $this->world->projects[self::PROJECT_ID] = ['id' => self::PROJECT_ID, 'psettings' => 'restricted', 'clientId' => self::CLIENT_ID];
        $this->posts = [];
        $this->queueTable = [];
        $this->listedChannels = [];
        $this->failingEndpoints = [];

        $rowsWritten = 0;
        $this->queueRepo = $this->make(QueueRepository::class, [
            'addMessageToQueue' => function (Workers $channel, string $subject, string $message, int $userId, int $projectId = 0) use (&$rowsWritten) {
                $rowsWritten++;
                $msghash = md5($rowsWritten.$subject.$message);
                $this->queueTable[$msghash] = [
                    'msghash' => $msghash,
                    'channel' => $channel->value,
                    'subject' => $subject,
                    'message' => $message,
                    'userId' => $userId,
                    'projectId' => $projectId,
                    'thedate' => sprintf('2026-09-30 10:%02d:%02d', intdiv($rowsWritten, 60), $rowsWritten % 60),
                ];
            },
            'listMessageInQueue' => function (Workers $channel, mixed $recipients = null, int $projectId = 0, ?int $limit = null) {
                $this->listedChannels[] = $channel->value;
                $rows = array_values(array_filter($this->queueTable, fn (array $row) => $row['channel'] === $channel->value));

                // Rows are written with rising timestamps, so table order is the repository's oldest-first order.
                return $limit === null ? $rows : array_slice($rows, 0, $limit);
            },
            'deleteMessageInQueue' => function (string|array $msghashes) {
                foreach ((array) $msghashes as $msghash) {
                    unset($this->queueTable[$msghash]);
                }

                return true;
            },
        ]);
    }

    /**
     * Builds the service over the world. Every fake reads the world at call
     * time, so changes made after queueing are what the worker sees.
     */
    private function makeService(): Webhooks
    {
        $world = $this->world;

        $userRepo = $this->make(UserRepository::class, [
            'getUser' => fn ($id) => $world->users[(int) $id] ?? false,
        ]);
        // The real isUserAssignedToProject() resolves the user repository from the container.
        app()->instance(UserRepository::class, $userRepo);

        // Stands in for the zp_relationuserproject "is on the team" lookup at the end of isUserAssignedToProject().
        $connection = Mockery::mock(ConnectionInterface::class);
        $connection->shouldReceive('table')->with('zp_relationuserproject')->andReturnUsing(fn () => new class($world)
        {
            private ?int $userId = null;

            private ?int $projectId = null;

            public function __construct(private object $world) {}

            public function join(mixed ...$arguments): static
            {
                return $this;
            }

            public function where(string $column, mixed $value): static
            {
                if (str_ends_with($column, 'userId')) {
                    $this->userId = (int) $value;
                }
                if (str_ends_with($column, 'projectId')) {
                    $this->projectId = (int) $value;
                }

                return $this;
            }

            public function exists(): bool
            {
                return isset($this->world->teams[$this->userId.':'.$this->projectId]);
            }
        });

        $projectRepo = $this->make(ProjectRepository::class, [
            'getProject' => fn ($id) => $world->projects[(int) $id] ?? false,
            'connection' => $connection,
        ]);

        $settingsRepo = $this->make(SettingRepository::class, [
            'getSettingsForKeys' => fn (array $keys) => array_intersect_key($world->settings, array_flip($keys)),
        ]);

        $transport = $this->make(WebhookTransport::class, [
            'post' => function (string $url, array $payload) {
                $this->posts[] = ['url' => $url, 'payload' => $payload];
                if (isset($this->failingEndpoints[$url])) {
                    throw $this->failingEndpoints[$url];
                }
            },
        ]);

        return new Webhooks($transport, $settingsRepo, $userRepo, $projectRepo, $this->queueRepo);
    }

    /**
     * Runs the WEBHOOKS queue once the way the scheduler does — WebhookQueue
     * and its job built by the container — with a freshly built service, as a
     * later worker process would have.
     */
    private function runWebhookQueue(): void
    {
        app()->instance(Webhooks::class, $this->makeService());
        app()->instance(QueueRepository::class, $this->queueRepo);

        app()->make(WebhookQueue::class)->processQueue();
    }

    /**
     * An active user with notifications on (editor role, no client) unless overridden.
     *
     * @param  array<string, mixed>  $overrides  Column overrides for the zp_user row.
     */
    private function addUser(int $userId, array $overrides = []): void
    {
        $this->world->users[$userId] = $overrides + ['id' => $userId, 'status' => 'a', 'notifications' => 1, 'role' => 20, 'clientId' => 0];
    }

    /**
     * An active user who is on project 5's team.
     */
    private function addTeamMember(int $userId): void
    {
        $this->addUser($userId);
        $this->world->teams[$userId.':'.self::PROJECT_ID] = true;
    }

    /**
     * Stores the user's personal webhook setting: one JSON value holding URL and opt-in.
     */
    private function storeWebhook(int $userId, string $url, bool $enabled = true): void
    {
        $this->world->settings['usersettings.'.$userId.'.webhook'] = json_encode(['url' => $url, 'enabled' => $enabled]);
    }

    private function makeNotification(): NotificationModel
    {
        $notification = new NotificationModel;
        $notification->projectId = self::PROJECT_ID;
        $notification->authorId = self::AUTHOR_ID;
        $notification->module = 'tickets';
        $notification->action = 'updated';
        $notification->subject = 'To-Do updated';
        $notification->message = 'Ada updated "Ship it"';
        $notification->url = ['url' => 'https://leantime.example.com/#/tickets/showTicket/42', 'text' => 'Open'];
        $notification->entity = ['id' => 42, 'headline' => 'Ship it', 'description' => 'internal notes', 'password' => 'never-send'];

        return $notification;
    }

    /**
     * The JSON body a recipient's endpoint receives for makeNotification().
     *
     * @return array<string, mixed>
     */
    private function expectedPostBody(int $recipientId): array
    {
        return [
            'event' => 'notification',
            'module' => 'tickets',
            'action' => 'updated',
            'subject' => 'To-Do updated',
            'message' => 'Ada updated "Ship it"',
            'projectId' => self::PROJECT_ID,
            'url' => 'https://leantime.example.com/#/tickets/showTicket/42',
            'recipientId' => $recipientId,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function queuedRows(): array
    {
        return array_values($this->queueTable);
    }

    /**
     * The recipient ids of each queued row, in queue order.
     *
     * @return array<int, array<int>>
     */
    private function queuedRecipientIds(): array
    {
        return array_map(
            fn (array $row) => unserialize($row['message'], ['allowed_classes' => false])['recipientIds'],
            $this->queuedRows()
        );
    }

    /**
     * @return array<int, string>
     */
    private function postedUrls(): array
    {
        return array_column($this->posts, 'url');
    }

    // ---------------------------------------------------------------------
    // queueToUsers(): the part that runs inside the request.
    // ---------------------------------------------------------------------

    public function test_queue_to_users_writes_one_webhooks_queue_row_per_recipient_and_sends_nothing(): void
    {
        $this->addTeamMember(7);
        $this->addTeamMember(8);
        $this->storeWebhook(7, self::SECRET_ENDPOINT);
        $this->storeWebhook(8, 'https://8.8.8.8/hooks/eight');

        $this->makeService()->queueToUsers($this->makeNotification(), [7, 8]);

        $this->assertSame([], $this->posts, 'Queueing must never contact an endpoint');
        $rows = $this->queuedRows();
        $this->assertCount(2, $rows, 'One row per recipient, on the webhooks channel');
        $this->assertSame([[7], [8]], $this->queuedRecipientIds());
        foreach ($rows as $index => $row) {
            $this->assertSame(Workers::WEBHOOKS->value, $row['channel'], 'Never the DEFAULT channel');
            $this->assertSame(DeliverPersonalWebhooks::class, $row['subject']);
            $this->assertSame([7, 8][$index], $row['userId'], 'The row belongs to its recipient');
            $this->assertSame(self::PROJECT_ID, $row['projectId']);
        }
    }

    public function test_queued_payload_is_scalar_only_and_carries_no_endpoint_or_raw_entity(): void
    {
        $this->addTeamMember(7);
        $this->addTeamMember(8);
        $this->storeWebhook(7, self::SECRET_ENDPOINT);
        $this->storeWebhook(8, 'https://8.8.8.8/hooks/eight-secret');

        $this->makeService()->queueToUsers($this->makeNotification(), [7, 8]);

        $this->assertCount(2, $this->queuedRows());
        foreach ($this->queuedRows() as $index => $row) {
            $storedMessage = $row['message'];
            $this->assertSame([
                'projectId' => self::PROJECT_ID,
                'module' => 'tickets',
                'action' => 'updated',
                'subject' => 'To-Do updated',
                'message' => 'Ada updated "Ship it"',
                'url' => 'https://leantime.example.com/#/tickets/showTicket/42',
                'recipientIds' => [[7, 8][$index]],
            ], unserialize($storedMessage, ['allowed_classes' => false]));
            $this->assertStringNotContainsString('O:', $storedMessage, 'No serialized objects in the queue');
            foreach (['secret', '1.1.1.1', '8.8.8.8', 'internal notes', 'never-send'] as $mustNotBeStored) {
                $this->assertStringNotContainsString($mustNotBeStored, $storedMessage, 'Endpoints are re-read at send time and the raw entity is never queued');
            }
        }
    }

    public function test_queue_to_users_only_includes_opted_in_active_notifiable_users_with_project_access(): void
    {
        $this->addTeamMember(7);                              // eligible
        $this->addTeamMember(8);                              // webhook switched off
        $this->addTeamMember(9);
        $this->world->users[9]['status'] = 'i';               // deactivated
        $this->addTeamMember(10);
        $this->world->users[10]['notifications'] = 0;         // master notification switch off
        $this->world->teams['11:'.self::PROJECT_ID] = true;   // deleted user, relation row left behind
        $this->addUser(12);                                   // @mentioned, but not on this restricted project
        foreach ([7, 9, 10, 11, 12] as $userId) {
            $this->storeWebhook($userId, "https://1.1.1.1/hooks/user-{$userId}");
        }
        $this->storeWebhook(8, 'https://1.1.1.1/hooks/user-8', false);

        $this->makeService()->queueToUsers($this->makeNotification(), [7, 8, 9, 10, 11, 12]);

        $this->assertSame([[7]], $this->queuedRecipientIds());
    }

    public function test_nothing_is_queued_when_no_recipient_is_eligible(): void
    {
        $this->addTeamMember(7);
        $this->addUser(12);
        $this->storeWebhook(12, 'https://1.1.1.1/hooks/outsider');

        $this->makeService()->queueToUsers($this->makeNotification(), [7, 12]);

        $this->assertSame([], $this->queuedRows(), 'Only opted-in recipients with access produce a job');
    }

    public function test_nothing_is_queued_for_a_project_that_no_longer_exists(): void
    {
        $this->addUser(7, ['role' => 50]); // owners can reach every project that exists
        $this->storeWebhook(7, 'https://1.1.1.1/hooks/owner');
        unset($this->world->projects[self::PROJECT_ID]);

        $this->makeService()->queueToUsers($this->makeNotification(), [7]);

        $this->assertSame([], $this->queuedRows());
    }

    public function test_no_recipients_is_a_no_op(): void
    {
        $settingLookups = 0;
        $webhooks = new Webhooks(
            $this->make(WebhookTransport::class, ['post' => fn () => $this->fail('No recipients means no post')]),
            $this->make(SettingRepository::class, [
                'getSettingsForKeys' => function () use (&$settingLookups) {
                    $settingLookups++;

                    return [];
                },
            ]),
            $this->make(UserRepository::class),
            $this->make(ProjectRepository::class),
            $this->queueRepo,
        );

        $webhooks->queueToUsers($this->makeNotification(), []);
        $webhooks->sendToUsers($this->makeNotification(), []);

        $this->assertSame(0, $settingLookups, 'No recipients means no settings lookup');
        $this->assertSame([], $this->queuedRows());
    }

    // ---------------------------------------------------------------------
    // Delivery: the queued rows run by the scheduler's WebhookQueue.
    // ---------------------------------------------------------------------

    public function test_worker_posts_the_queued_notification_to_each_recipients_endpoint(): void
    {
        $this->addTeamMember(7);
        $this->addTeamMember(8);
        $this->storeWebhook(7, self::SECRET_ENDPOINT);
        $this->storeWebhook(8, 'https://8.8.8.8/hooks/eight');
        $this->makeService()->queueToUsers($this->makeNotification(), [7, 8]);

        $this->runWebhookQueue();

        $this->assertSame([
            ['url' => self::SECRET_ENDPOINT, 'payload' => $this->expectedPostBody(7)],
            ['url' => 'https://8.8.8.8/hooks/eight', 'payload' => $this->expectedPostBody(8)],
        ], $this->posts, 'The payload carries presentational fields only — never the raw entity');
        $this->assertSame([], $this->queuedRows(), 'Handled rows leave the queue');
    }

    public function test_one_run_delivers_several_queued_notifications_without_the_default_queue(): void
    {
        foreach ([7, 8, 9] as $userId) {
            $this->addTeamMember($userId);
            $this->storeWebhook($userId, "https://1.1.1.1/hooks/user-{$userId}");
        }
        // Another feature's DEFAULT job that keeps failing sits at the head of the DEFAULT channel.
        $this->queueRepo->addMessageToQueue(Workers::DEFAULT, 'Some\\Other\\FailingJob', serialize(['stuck' => true]), self::AUTHOR_ID, self::PROJECT_ID);
        $service = $this->makeService();
        $service->queueToUsers($this->makeNotification(), [7, 8, 9]);
        $second = $this->makeNotification();
        $second->subject = 'To-Do moved';
        $service->queueToUsers($second, [7, 8, 9]);

        $this->runWebhookQueue();

        $this->assertCount(6, $this->posts, 'Two notifications to three recipients, all delivered in one run');
        $this->assertSame(
            ['To-Do updated', 'To-Do updated', 'To-Do updated', 'To-Do moved', 'To-Do moved', 'To-Do moved'],
            array_map(fn (array $post) => $post['payload']['subject'], $this->posts)
        );
        $this->assertSame(['webhooks'], $this->listedChannels, 'Delivery never reads the DEFAULT channel');
        $remaining = $this->queuedRows();
        $this->assertCount(1, $remaining);
        $this->assertSame(Workers::DEFAULT->value, $remaining[0]['channel'], 'The DEFAULT row is left alone');
    }

    public function test_worker_reads_the_endpoint_at_send_time_so_a_changed_url_gets_the_post(): void
    {
        $this->addTeamMember(7);
        $this->storeWebhook(7, 'https://1.1.1.1/hooks/old');
        $this->makeService()->queueToUsers($this->makeNotification(), [7]);

        $this->storeWebhook(7, 'https://8.8.8.8/hooks/new');
        $this->runWebhookQueue();

        $this->assertSame(['https://8.8.8.8/hooks/new'], $this->postedUrls());
    }

    /**
     * @dataProvider changesAfterQueueingProvider
     */
    public function test_worker_skips_a_recipient_who_may_no_longer_receive_the_webhook(\Closure $changeAfterQueueing): void
    {
        $this->addTeamMember(7);
        $this->storeWebhook(7, 'https://1.1.1.1/hooks/seven');
        $this->makeService()->queueToUsers($this->makeNotification(), [7]);
        $this->assertCount(1, $this->queuedRows(), 'Eligible when the notification was raised');

        $changeAfterQueueing($this->world);
        $this->runWebhookQueue();

        $this->assertSame([], $this->posts);
        $this->assertSame([], $this->queuedRows(), 'A row with nothing left to send is still handled, never left in the queue');
    }

    /**
     * @return array<string, array{\Closure}>
     */
    public static function changesAfterQueueingProvider(): array
    {
        return [
            'webhook switched off' => [fn (object $world) => $world->settings['usersettings.7.webhook'] = json_encode(['url' => 'https://1.1.1.1/hooks/seven', 'enabled' => false])],
            'webhook url cleared' => [fn (object $world) => $world->settings['usersettings.7.webhook'] = json_encode(['url' => '', 'enabled' => true])],
            'user deactivated' => [fn (object $world) => $world->users[7]['status'] = 'i'],
            'user deleted' => [function (object $world) {
                unset($world->users[7]);
            }],
            'notifications switched off' => [fn (object $world) => $world->users[7]['notifications'] = 0],
            'removed from the restricted project' => [function (object $world) {
                unset($world->teams['7:5']);
            }],
            'project deleted' => [function (object $world) {
                unset($world->projects[5]);
            }],
        ];
    }

    /**
     * @dataProvider projectAccessProvider
     */
    public function test_project_access_rules_decide_who_outside_the_team_gets_webhooks(string $projectVisibility, array $userColumns, bool $expectedToReceive): void
    {
        $this->world->projects[self::PROJECT_ID]['psettings'] = $projectVisibility;
        $this->addUser(12, $userColumns); // not on the team — e.g. @mentioned
        $this->storeWebhook(12, 'https://1.1.1.1/hooks/twelve');

        $this->makeService()->queueToUsers($this->makeNotification(), [12]);
        $this->runWebhookQueue();

        $this->assertSame($expectedToReceive ? ['https://1.1.1.1/hooks/twelve'] : [], $this->postedUrls());
    }

    /**
     * @return array<string, array{string, array<string, mixed>, bool}>
     */
    public static function projectAccessProvider(): array
    {
        return [
            'project open to the whole organisation' => ['all', [], true],
            'client project, user in that client' => ['clients', ['clientId' => self::CLIENT_ID], true],
            'client project, user in another client' => ['clients', ['clientId' => 4], false],
            'restricted project, admin' => ['restricted', ['role' => 40], true],
            'restricted project, not on the team' => ['restricted', [], false],
        ];
    }

    public function test_only_users_who_opted_in_and_stored_a_url_are_posted(): void
    {
        foreach ([1, 2, 3, 4, 5, 6] as $userId) {
            $this->addTeamMember($userId);
        }
        $this->storeWebhook(1, 'https://1.1.1.1/one');
        $this->storeWebhook(2, 'https://1.1.1.1/two', false);
        $this->storeWebhook(3, '');
        $this->world->settings['usersettings.5.webhook'] = 'not json';
        $this->world->settings['usersettings.6.webhook'] = json_encode(['url' => 'https://1.1.1.1/six', 'enabled' => '1']);
        // User 4 has no webhook setting at all.

        $this->makeService()->sendToUsers($this->makeNotification(), [1, 2, 3, 4, 5, 6]);

        $this->assertSame([['url' => 'https://1.1.1.1/one', 'payload' => $this->expectedPostBody(1)]], $this->posts);
    }

    public function test_each_recipient_gets_their_own_payload_once(): void
    {
        $this->addTeamMember(1);
        $this->addTeamMember(2);
        $this->storeWebhook(1, 'https://1.1.1.1/one');
        $this->storeWebhook(2, 'https://8.8.8.8/two');

        $this->makeService()->sendToUsers($this->makeNotification(), [1, '2', 1]);

        $this->assertCount(2, $this->posts, 'Duplicate recipient ids must not produce duplicate posts');
        $this->assertSame([1, 2], array_map(fn (array $post) => $post['payload']['recipientId'], $this->posts));
    }

    public function test_posts_to_a_public_ipv6_endpoint(): void
    {
        $this->addTeamMember(7);
        $this->storeWebhook(7, 'https://[2606:4700:4700::1111]/hooks/v6');

        $this->makeService()->sendToUsers($this->makeNotification(), [7]);

        $this->assertSame(['https://[2606:4700:4700::1111]/hooks/v6'], $this->postedUrls(), 'A public bracketed IPv6 literal is a valid endpoint');
    }

    public function test_endpoints_that_fail_the_url_checks_are_never_contacted(): void
    {
        // Values that could only be stored by bypassing the save-time validation.
        $storedEndpoints = [
            1 => 'https://127.0.0.1/hook',
            2 => 'https://169.254.169.254/latest/meta-data',
            3 => 'https://10.0.0.5/hook',
            4 => 'http://1.1.1.1/plain-http',
            5 => 'https://user:pass@1.1.1.1/hook',
            6 => 'https://[::1]/hook',
            7 => 'https://[fd12:3456::1]/hook',
            8 => 'https://[fe80::1]/hook',
            9 => 'https://[::ffff:10.0.0.5]/hook',
        ];
        foreach ($storedEndpoints as $userId => $url) {
            $this->addTeamMember($userId);
            $this->storeWebhook($userId, $url);
        }

        $this->makeService()->sendToUsers($this->makeNotification(), array_keys($storedEndpoints));

        $this->assertSame([], $this->posts);
    }

    public function test_delivery_failures_are_swallowed_logged_without_the_url_secret_and_do_not_stop_other_recipients(): void
    {
        // Built before Log is mocked: building the repository mocks can emit PHP deprecations, which go through Log.
        $service = $this->makeService();
        $logged = [];
        Log::shouldReceive('warning')->andReturnUsing(function ($message, $context = []) use (&$logged) {
            $logged[] = $message.' '.json_encode($context);
        });

        foreach ([1, 2, 3] as $userId) {
            $this->addTeamMember($userId);
        }
        $this->storeWebhook(1, self::SECRET_ENDPOINT);
        $this->storeWebhook(2, 'https://8.8.8.8/hooks/other-secret');
        $this->storeWebhook(3, 'https://1.1.1.1/hooks/third');
        // What WebhookTransport throws; both carry the request, and with it the secret URL.
        $this->failingEndpoints = [
            self::SECRET_ENDPOINT => new BadResponseException('Webhook endpoint answered with a non-2xx status', new Request('POST', self::SECRET_ENDPOINT), new Response(500)),
            'https://8.8.8.8/hooks/other-secret' => new ConnectException('cURL error 28: timed out for https://8.8.8.8/hooks/other-secret', new Request('POST', 'https://8.8.8.8/hooks/other-secret')),
        ];

        $service->sendToUsers($this->makeNotification(), [1, 2, 3]);

        $this->assertCount(3, $this->posts, 'A failing endpoint must not stop delivery to the next recipient');
        $this->assertCount(2, $logged, 'Each failed delivery is logged once');
        $this->assertStringContainsString('1.1.1.1', $logged[0]);
        $this->assertStringContainsString('500', $logged[0]);
        $this->assertStringContainsString('ConnectException', $logged[1]);
        foreach ($logged as $line) {
            $this->assertStringNotContainsString('secret', $line, 'Webhook path/query and exception messages must never reach the logs');
        }
    }

    /**
     * @dataProvider endpointUrlProvider
     */
    public function test_is_valid_endpoint_url(string $url, bool $expected): void
    {
        $this->assertSame($expected, Webhooks::isValidEndpointUrl($url));
    }

    /**
     * Every host spelling WebhookTransport refuses to contact must be refused when the URL is
     * saved too — otherwise the profile reports a webhook as set up that can never deliver.
     *
     * @dataProvider transportRefusedHostProvider
     */
    public function test_endpoints_the_transport_refuses_cannot_be_saved(string $url): void
    {
        $this->assertFalse(Webhooks::isValidEndpointUrl($url));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function transportRefusedHostProvider(): array
    {
        return [
            'shorthand ipv4' => ['https://127.1/hook'],
            'shorthand public ipv4' => ['https://1.1/hook'],
            'hex ipv4' => ['https://0x7f000001/hook'],
            'decimal ipv4' => ['https://2130706433/hook'],
            'dotted hex ipv4' => ['https://0x7f.0.0.1/hook'],
            'octal ipv4' => ['https://0177.0.0.1/hook'],
            'octal public ipv4' => ['https://010.010.010.010/hook'],
            'trailing dot' => ['https://hooks.example.com./hook'],
            'percent-encoded host' => ['https://hooks%2eexample.com/hook'],
        ];
    }

    public static function endpointUrlProvider(): array
    {
        return [
            'https hostname with path' => ['https://hooks.example.com/services/T000/B000/XXXX', true],
            'https with port and query' => ['https://example.com:8443/hook?token=abc', true],
            'public ipv4 literal' => ['https://1.1.1.1/hook', true],
            'public ipv6 literal' => ['https://[2606:4700:4700::1111]/hook', true],
            'exactly the length limit' => ['https://example.com/'.str_repeat('a', 2028), true],
            'over the length limit' => ['https://example.com/'.str_repeat('a', 2029), false],
            'empty' => ['', false],
            'plain http' => ['http://hooks.example.com/hook', false],
            'other scheme' => ['ftp://example.com/hook', false],
            'javascript' => ['javascript:alert(1)', false],
            'user and password' => ['https://user:pass@example.com/hook', false],
            'user only' => ['https://token@example.com/hook', false],
            'single-label host' => ['https://localhost/hook', false],
            'upper-case hostname' => ['https://Hooks.Example.COM/hook', true],
            'punycode hostname' => ['https://xn--bcher-kva.example/hook', true],
            'non-ascii hostname' => ['https://bücher.example/hook', false],
            'numeric last label' => ['https://hooks.example.123/hook', false],
            'loopback literal' => ['https://127.0.0.1/hook', false],
            'private literal' => ['https://10.0.0.1/hook', false],
            'metadata literal' => ['https://169.254.169.254/latest', false],
            'loopback ipv6 literal' => ['https://[::1]/hook', false],
            'no host' => ['https:///hook', false],
            'space in host' => ['https://exa mple.com/hook', false],
            'not a url' => ['not a url', false],
        ];
    }
}

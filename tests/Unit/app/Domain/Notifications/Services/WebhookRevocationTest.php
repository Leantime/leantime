<?php

namespace Unit\app\Domain\Notifications\Services;

use Carbon\CarbonImmutable;
use Illuminate\Database\SQLiteConnection;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Support\Avatarcreator;
use Leantime\Domain\Files\Repositories\Files as FileRepository;
use Leantime\Domain\Notifications\Jobs\DeliverPersonalWebhooks;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
use Leantime\Domain\Notifications\Services\WebhookQueue;
use Leantime\Domain\Notifications\Services\Webhooks;
use Leantime\Domain\Notifications\Services\WebhookTransport;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Setting\Services\SettingCache;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\TestCase;

/**
 * A queued personal webhook is posted only if the recipient may receive it at the moment
 * that row is posted — not at the moment the scheduler run started.
 *
 * One scheduler run builds WebhookQueue, its job and Webhooks once and reuses their
 * repositories for every row of the batch, and those repositories cache: the user repository
 * memoizes getUser(), and each SettingCache keeps an in-memory copy of every key it read. So
 * two rows for the same recipient run in one batch, and right after the first post another
 * request changes something: the user switches the webhook off, rotates its URL, turns
 * notifications off, is deactivated, deleted, demoted or taken off the project team. The
 * second row must follow that change.
 *
 * Nor does the queue fold two rows into one: the same notification raised twice in one second
 * is posted twice.
 *
 * Only the database (in-memory SQLite) and the transport (a recorder) are stand-ins. The
 * WebhookQueue, DeliverPersonalWebhooks, Webhooks, the Setting/User/Project/Queue repositories
 * and SettingCache (its shared tier on the array cache store) are the real classes. The other
 * request writes through its own repository instances, as a separate PHP process would.
 */
class WebhookRevocationTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private const PROJECT_ID = 5;

    private const RECIPIENT_ID = 7;

    private const OLD_ENDPOINT = 'https://1.1.1.1/hooks/old';

    private const NEW_ENDPOINT = 'https://8.8.8.8/hooks/new';

    private SQLiteConnection $connection;

    private DbCore $db;

    /**
     * Every transport post, in order.
     *
     * @var array<int, array{url: string, subject: string}>
     */
    private array $posts = [];

    /**
     * What another request changes right after the first post, if anything.
     */
    private ?\Closure $afterFirstPost = null;

    /**
     * The other request's repositories: each its own instance, with its own cache.
     */
    private SettingRepository $webSettings;

    private UserRepository $webUsers;

    private ProjectRepository $webProjects;

    protected function setUp(): void
    {
        parent::setUp();

        $this->posts = [];
        $this->afterFirstPost = null;

        $this->connection = new SQLiteConnection(new \PDO('sqlite::memory:'));
        // The columns the repositories under test read and write.
        $this->connection->statement('CREATE TABLE zp_user (id INTEGER PRIMARY KEY, status VARCHAR(1), notifications INTEGER, role INTEGER, clientId INTEGER, modified DATETIME NULL)');
        $this->connection->statement('CREATE TABLE zp_settings ("key" VARCHAR(175) PRIMARY KEY, value TEXT NULL)');
        $this->connection->statement('CREATE TABLE zp_projects (id INTEGER PRIMARY KEY, name VARCHAR(255), clientId INTEGER, details TEXT NULL, state INTEGER NULL, hourBudget VARCHAR(255) NULL, dollarBudget INTEGER NULL, psettings VARCHAR(255) NULL, menuType VARCHAR(255) NULL, avatar TEXT NULL, cover TEXT NULL, type VARCHAR(255) NULL, parent INTEGER NULL, modified DATETIME NULL, start DATETIME NULL, "end" DATETIME NULL)');
        $this->connection->statement('CREATE TABLE zp_clients (id INTEGER PRIMARY KEY, name VARCHAR(255))');
        $this->connection->statement('CREATE TABLE zp_reactions (id INTEGER PRIMARY KEY, module VARCHAR(50), moduleId INTEGER, reaction VARCHAR(50), userId INTEGER)');
        $this->connection->statement('CREATE TABLE zp_relationuserproject (userId INTEGER, projectId INTEGER, projectRole VARCHAR(255) NULL)');
        $this->connection->statement('CREATE TABLE zp_queue (msghash VARCHAR(50) NOT NULL PRIMARY KEY, channel VARCHAR(255) NULL, userId INTEGER NOT NULL, subject VARCHAR(255) NULL, message TEXT NOT NULL, thedate DATETIME NOT NULL, projectId INTEGER NOT NULL)');

        $this->db = $this->make(DbCore::class, ['getConnection' => fn () => $this->connection]);

        $this->webSettings = $this->settingRepository();
        $this->webUsers = $this->userRepository();
        $this->webProjects = $this->projectRepository();

        // Project 5 is visible to its team only. User 7 is an active editor on that team,
        // with notifications on and a personal webhook switched on.
        $this->connection->table('zp_projects')->insert(['id' => self::PROJECT_ID, 'name' => 'Launch', 'clientId' => 3, 'psettings' => 'restricted']);
        $this->connection->table('zp_user')->insert(['id' => self::RECIPIENT_ID, 'status' => 'a', 'notifications' => 1, 'role' => 20, 'clientId' => 0]);
        $this->connection->table('zp_relationuserproject')->insert(['userId' => self::RECIPIENT_ID, 'projectId' => self::PROJECT_ID, 'projectRole' => '']);
        $this->webSettings->saveSetting(Webhooks::settingKey(self::RECIPIENT_ID), Webhooks::encodeSetting(self::OLD_ENDPOINT, true));
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    private function settingRepository(): SettingRepository
    {
        return new SettingRepository($this->db, new SettingCache);
    }

    private function userRepository(): UserRepository
    {
        return new UserRepository(app(Environment::class), $this->db, new DatabaseHelper($this->connection), $this->make(FileRepository::class));
    }

    private function projectRepository(): ProjectRepository
    {
        return new ProjectRepository(app(Environment::class), $this->db, $this->make(Avatarcreator::class), new DatabaseHelper($this->connection));
    }

    private function queueRepository(): QueueRepository
    {
        return new QueueRepository($this->db, $this->userRepository());
    }

    /**
     * Records every post; right after the first one, lets the other request make its change.
     */
    private function recordingTransport(): WebhookTransport
    {
        return $this->make(WebhookTransport::class, [
            'post' => function (string $url, array $payload) {
                $this->posts[] = ['url' => $url, 'subject' => $payload['subject']];

                if (count($this->posts) === 1 && $this->afterFirstPost !== null) {
                    ($this->afterFirstPost)();
                }
            },
        ]);
    }

    private function makeNotification(string $subject): NotificationModel
    {
        $notification = new NotificationModel;
        $notification->projectId = self::PROJECT_ID;
        $notification->module = 'tickets';
        $notification->action = 'updated';
        $notification->subject = $subject;
        $notification->message = 'Ada updated "Ship it"';
        $notification->url = ['url' => 'https://leantime.example.com/#/tickets/showTicket/42', 'text' => 'Open'];

        return $notification;
    }

    /**
     * Two notifications raised in one request queue two rows for user 7; one scheduler run then
     * delivers both, and $changeAfterFirstPost runs between the two posts.
     */
    private function deliverTwoQueuedNotifications(\Closure $changeAfterFirstPost, string $firstSubject = 'first', string $secondSubject = 'second'): void
    {
        // The request that raised the notifications: its own repositories, cached reads.
        app()->instance(UserRepository::class, $this->webUsers);
        $request = new Webhooks($this->recordingTransport(), $this->webSettings, $this->webUsers, $this->webProjects, $this->queueRepository());
        $request->queueToUsers($this->makeNotification($firstSubject), [self::RECIPIENT_ID]);
        $request->queueToUsers($this->makeNotification($secondSubject), [self::RECIPIENT_ID]);
        $this->assertSame(2, $this->connection->table('zp_queue')->count(), 'Both notifications are queued for user 7');
        $this->assertSame([], $this->posts, 'Queueing posts nothing');

        // One scheduler run: one Webhooks and one set of repositories for the whole batch. The
        // container hands isUserAssignedToProject() the same user repository Webhooks holds —
        // the case in which a stale getUser() memo would also decide project access.
        $this->afterFirstPost = $changeAfterFirstPost;
        $workerUsers = $this->userRepository();
        app()->instance(UserRepository::class, $workerUsers);
        $worker = new Webhooks($this->recordingTransport(), $this->settingRepository(), $workerUsers, $this->projectRepository(), $this->queueRepository());
        (new WebhookQueue($this->queueRepository(), new DeliverPersonalWebhooks($worker)))->processQueue();

        $this->assertSame(0, $this->connection->table('zp_queue')->count(), 'Both rows are taken in the one run');
    }

    /**
     * @return array<int, string>
     */
    private function postedUrls(): array
    {
        return array_column($this->posts, 'url');
    }

    public function test_both_rows_post_when_nothing_changes_between_them(): void
    {
        $this->deliverTwoQueuedNotifications(fn () => null);

        $this->assertSame([self::OLD_ENDPOINT, self::OLD_ENDPOINT], $this->postedUrls());
        $this->assertEqualsCanonicalizing(['first', 'second'], array_column($this->posts, 'subject'));
    }

    public function test_the_same_notification_raised_twice_in_one_second_is_posted_twice(): void
    {
        // Same second, recipient, project and payload: nothing but each row's own id tells them apart.
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-01 08:00:00', 'UTC'));

        $this->deliverTwoQueuedNotifications(fn () => null, firstSubject: 'same', secondSubject: 'same');

        $this->assertSame(array_fill(0, 2, ['url' => self::OLD_ENDPOINT, 'subject' => 'same']), $this->posts, 'Each row is claimed and posted on its own');
    }

    public function test_switching_the_webhook_off_stops_the_next_row(): void
    {
        $this->deliverTwoQueuedNotifications(fn () => $this->webSettings->saveSetting(
            Webhooks::settingKey(self::RECIPIENT_ID),
            Webhooks::encodeSetting(self::OLD_ENDPOINT, false)
        ));

        $this->assertSame([self::OLD_ENDPOINT], $this->postedUrls());
    }

    public function test_a_rotated_url_receives_the_next_row_and_the_old_one_does_not(): void
    {
        $this->deliverTwoQueuedNotifications(fn () => $this->webSettings->saveSetting(
            Webhooks::settingKey(self::RECIPIENT_ID),
            Webhooks::encodeSetting(self::NEW_ENDPOINT, true)
        ));

        $this->assertSame([self::OLD_ENDPOINT, self::NEW_ENDPOINT], $this->postedUrls());
    }

    public function test_turning_notifications_off_stops_the_next_row(): void
    {
        $this->deliverTwoQueuedNotifications(fn () => $this->webUsers->patchUser(self::RECIPIENT_ID, ['notifications' => 0]));

        $this->assertSame([self::OLD_ENDPOINT], $this->postedUrls());
    }

    public function test_deactivating_the_account_stops_the_next_row(): void
    {
        $this->deliverTwoQueuedNotifications(fn () => $this->webUsers->patchUser(self::RECIPIENT_ID, ['status' => 'i']));

        $this->assertSame([self::OLD_ENDPOINT], $this->postedUrls());
    }

    public function test_deleting_the_user_stops_the_next_row(): void
    {
        $this->deliverTwoQueuedNotifications(fn () => $this->webUsers->deleteUser(self::RECIPIENT_ID));

        $this->assertSame([self::OLD_ENDPOINT], $this->postedUrls());
    }

    public function test_taking_the_user_off_the_project_team_stops_the_next_row(): void
    {
        $this->deliverTwoQueuedNotifications(fn () => $this->webProjects->deleteProjectRelation(self::RECIPIENT_ID, self::PROJECT_ID));

        $this->assertSame([self::OLD_ENDPOINT], $this->postedUrls());
    }

    public function test_demoting_an_admin_who_is_not_on_the_team_stops_the_next_row(): void
    {
        // An admin sees every project without being on its team.
        $this->connection->table('zp_relationuserproject')->delete();
        $this->connection->table('zp_user')->where('id', self::RECIPIENT_ID)->update(['role' => 50]);

        $this->deliverTwoQueuedNotifications(fn () => $this->webUsers->patchUser(self::RECIPIENT_ID, ['role' => 20]));

        $this->assertSame([self::OLD_ENDPOINT], $this->postedUrls());
    }
}

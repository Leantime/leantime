<?php

namespace Unit\app\Domain\Notifications\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Domain\Notifications\Listeners\NotifyAssignee;
use Leantime\Domain\Notifications\Services\AssignmentNotifier;
use Leantime\Domain\Notifications\Services\Notifications as NotificationService;
use Leantime\Domain\Notifications\Services\Push;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Tickets\Events\TicketAssigned;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\TestCase;

/**
 * "Assigned to you" goes to the one person a to-do was assigned to: inbox always, email when
 * they kept email on, push when they kept push on. Self-assignments, deactivated assignees and
 * unreadable to-dos send nothing.
 */
class AssignmentNotifierTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private const STRINGS = [
        'text.x_assigned_you' => "%1\$s assigned you '%2\$s'",
        'text.click_here' => 'Click here to get to it.',
        'email_notifications.assigned_subject' => 'Assigned to you: %s',
        'notification.push_assigned_body' => '%s assigned this to you',
        'notification.push_due' => 'Due %s',
        'label.someone' => 'Someone',
        'language.dateformat' => 'M j',
    ];

    /** @var array<int, array<string, mixed>|false> zp_user rows by id */
    private array $users = [];

    /** @var array<int, array{userIds: array, title: string, body: string, data: array}> */
    private array $pushes = [];

    /** @var array<int, array{recipients: array, message: string, subject: string, projectId: int}> */
    private array $emails = [];

    /** @var array<int, array<string, mixed>> */
    private array $inbox = [];

    private ?\Closure $pushBehaviour = null;

    private array $dispatcherSnapshot = [];

    protected function setUp(): void
    {
        parent::setUp();

        session(['userdata' => ['id' => 1, 'name' => 'Sam']]);
        session(['usersettings.timezone' => 'UTC']);
        session(['usersettings.language' => 'en-US']);
        session(['usersettings.date_format' => 'Y-m-d']);
        session(['usersettings.time_format' => 'H:i']);
        CarbonImmutable::mixin(new CarbonMacros('UTC', 'en-US', 'M j', 'H:i'));

        $this->users = [
            1 => ['id' => 1, 'firstname' => 'Sam', 'status' => 'a', 'notifications' => 1],
            2 => ['id' => 2, 'firstname' => 'Ada', 'status' => 'a', 'notifications' => 1],
            3 => ['id' => 3, 'firstname' => 'Gone', 'status' => 'i', 'notifications' => 1],
            4 => ['id' => 4, 'firstname' => 'Quiet', 'status' => 'A', 'notifications' => 0],
        ];
        $this->pushes = [];
        $this->emails = [];
        $this->inbox = [];
        $this->pushBehaviour = null;

        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach (['eventRegistry', 'available_hooks', 'patternMatchCache', 'compiledPatternCache', 'eventRegistryVersion'] as $prop) {
            $this->dispatcherSnapshot[$prop] = $reflection->getProperty($prop)->getValue();
        }
    }

    protected function tearDown(): void
    {
        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach ($this->dispatcherSnapshot as $prop => $value) {
            $reflection->getProperty($prop)->setValue(null, $value);
        }

        parent::tearDown();
    }

    /**
     * @param  array<string, string>  $settings  Stored zp_settings by key.
     * @param  array<int>|null  $usersWithAccess  Who may see project 5; null = everyone.
     */
    private function notifier(TicketModel|false $ticket, array $settings = [], ?array $usersWithAccess = null): AssignmentNotifier
    {
        return new AssignmentNotifier(
            $this->make(TicketRepository::class, ['getTicket' => fn () => $ticket]),
            $this->make(UserRepository::class, ['getUser' => fn ($id) => $this->users[(int) $id] ?? false]),
            $this->make(ProjectRepository::class, [
                'isUserAssignedToProject' => fn ($userId, $projectId) => (int) $projectId === 5 && ($usersWithAccess === null || in_array((int) $userId, $usersWithAccess, true)),
            ]),
            $this->make(NotificationService::class, [
                'addNotifications' => function (array $rows) {
                    array_push($this->inbox, ...$rows);

                    return true;
                },
            ]),
            $this->make(QueueRepository::class, [
                'queueMessageToUsers' => function (array $recipients, string $message, string $subject = '', int $projectId = 0) {
                    $this->emails[] = compact('recipients', 'message', 'subject', 'projectId');
                },
            ]),
            $this->make(SettingRepository::class, [
                'getSetting' => fn ($key, $default = false) => $settings[$key] ?? $default,
                'getSettingsForKeys' => fn (array $keys) => array_intersect_key($settings, array_flip($keys)),
            ]),
            $this->make(LanguageCore::class, ['__' => fn ($key) => self::STRINGS[$key] ?? $key]),
            $this->make(Push::class, [
                'send' => function (array $userIds, string $title, string $body, array $data = []) {
                    if ($this->pushBehaviour !== null) {
                        ($this->pushBehaviour)();
                    }
                    $this->pushes[] = compact('userIds', 'title', 'body', 'data');
                },
            ]),
            // BASE_URL differs per environment (localhost here, leantime-dev on CI): swap whatever host it has.
            $this->make(TrustedAppUrl::class, ['rebase' => fn (string $url) => preg_replace('#^https?://[^/]+#', 'https://app.example.com', $url)]),
            $this->make(Environment::class, ['defaultTimezone' => 'UTC', 'language' => 'en-US']),
        );
    }

    private function ticket(array $overrides = []): TicketModel
    {
        return new TicketModel($overrides + ['id' => 42, 'headline' => 'Fix <b>login</b>', 'projectId' => 5, 'dateToFinish' => '2026-10-31 00:00:00']);
    }

    private function assigned(int $assigneeId = 2, bool $toSelf = false, ?int $previous = null): TicketAssigned
    {
        return new TicketAssigned(ticketId: 42, projectId: 5, assigneeId: $assigneeId, previousAssigneeId: $previous, assignedToSelf: $toSelf);
    }

    public function test_the_assignee_gets_an_inbox_row_a_queued_email_and_a_push(): void
    {
        $this->notifier($this->ticket())->notify($this->assigned());

        $this->assertCount(1, $this->inbox);
        $row = $this->inbox[0];
        $this->assertSame(2, $row['userId']);
        $this->assertSame('assigned', $row['type']);
        $this->assertSame('tickets', $row['module']);
        $this->assertSame(42, $row['moduleId']);
        $this->assertSame("Sam assigned you 'Fix login'", $row['message'], 'Markup in the headline is stripped');
        $this->assertSame(1, $row['authorId']);
        $this->assertStringStartsWith('https://app.example.com/', $row['url'], 'The link is rebased onto the trusted app URL');
        $this->assertStringEndsWith('/tickets/showTicket/42', $row['url']);

        $this->assertCount(1, $this->emails);
        $this->assertSame([2], $this->emails[0]['recipients']);
        $this->assertSame('Assigned to you: Fix login', $this->emails[0]['subject']);
        $this->assertStringContainsString("Sam assigned you 'Fix login' <a href=\"https://app.example.com/", $this->emails[0]['message']);
        $this->assertSame(5, $this->emails[0]['projectId']);

        $this->assertCount(1, $this->pushes);
        $push = $this->pushes[0];
        $this->assertSame([2], $push['userIds']);
        $this->assertSame('Fix login', $push['title'], 'The title names the to-do');
        $this->assertSame('Sam assigned this to you · Due Oct 31', $push['body']);
        $this->assertSame([
            'module' => 'tickets',
            'moduleId' => '42',
            'projectId' => '5',
            'type' => 'assigned',
            'threadId' => 'ticket-42',
            'url' => $row['url'],
        ], $push['data']);
    }

    /**
     * @dataProvider noDueDateProvider
     */
    public function test_the_push_body_has_no_due_part_without_a_due_date(mixed $dateToFinish): void
    {
        $this->notifier($this->ticket(['dateToFinish' => $dateToFinish]))->notify($this->assigned());

        $this->assertSame('Sam assigned this to you', $this->pushes[0]['body']);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function noDueDateProvider(): array
    {
        return [
            'empty' => [''],
            'null' => [null],
            'zero date' => ['0000-00-00 00:00:00'],
            'junk' => ['not a date'],
        ];
    }

    /**
     * Skipping the notification preference filters never skips authorization: an assignment
     * can name anyone, but someone outside the project must not learn the to-do's headline
     * or deadline through any channel.
     */
    public function test_an_assignee_without_access_to_the_project_gets_nothing(): void
    {
        $this->notifier($this->ticket(), [], usersWithAccess: [1])->notify($this->assigned());

        $this->assertSame([], $this->inbox);
        $this->assertSame([], $this->emails);
        $this->assertSame([], $this->pushes);
    }

    public function test_a_todo_without_a_project_sends_nothing(): void
    {
        $this->notifier($this->ticket(['projectId' => 0]))->notify(new TicketAssigned(ticketId: 42, projectId: null, assigneeId: 2, previousAssigneeId: null, assignedToSelf: false));

        $this->assertSame([], $this->inbox);
        $this->assertSame([], $this->pushes);
    }

    /**
     * A due date saved without a time is stored as midnight and means that calendar day for
     * everyone: neither the actor's nor the assignee's timezone may shift it.
     */
    public function test_a_calendar_only_due_date_is_never_shifted_by_a_timezone(): void
    {
        session(['usersettings.timezone' => 'America/Los_Angeles']);
        CarbonImmutable::mixin(new CarbonMacros('America/Los_Angeles', 'en-US', 'M j', 'H:i'));

        $this->notifier($this->ticket(['dateToFinish' => '2026-10-31 00:00:00']), ['usersettings.2.timezone' => 'Pacific/Auckland'])->notify($this->assigned());

        $this->assertSame('Sam assigned this to you · Due Oct 31', $this->pushes[0]['body']);
    }

    /**
     * @dataProvider timedDeadlineProvider
     */
    public function test_a_timed_deadline_is_shown_in_the_assignees_timezone(string $assigneeTimezone, string $expected): void
    {
        session(['usersettings.timezone' => 'UTC']);

        $this->notifier($this->ticket(['dateToFinish' => '2026-10-31 02:00:00']), ['usersettings.2.timezone' => $assigneeTimezone])->notify($this->assigned());

        $this->assertSame('Sam assigned this to you · Due '.$expected, $this->pushes[0]['body']);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function timedDeadlineProvider(): array
    {
        return [
            'west of UTC, still the day before' => ['America/Los_Angeles', 'Oct 30'],
            'east of UTC, same day' => ['Europe/Berlin', 'Oct 31'],
        ];
    }

    public function test_the_due_date_uses_the_assignees_own_date_format_and_language(): void
    {
        $this->notifier($this->ticket(), [
            'usersettings.2.date_format' => 'd.m.Y',
            'usersettings.2.language' => 'de-DE',
        ])->notify($this->assigned());
        $this->notifier($this->ticket(), ['usersettings.2.language' => 'de-DE'])->notify($this->assigned());

        $this->assertSame('Sam assigned this to you · Due 31.10.2026', $this->pushes[0]['body']);
        $this->assertSame('Sam assigned this to you · Due Okt 31', $this->pushes[1]['body'], 'Month names follow the assignee\'s language');
    }

    public function test_assigning_to_yourself_sends_nothing(): void
    {
        $this->notifier($this->ticket())->notify($this->assigned(assigneeId: 1, toSelf: true));

        $this->assertSame([], $this->inbox);
        $this->assertSame([], $this->emails);
        $this->assertSame([], $this->pushes);
    }

    public function test_a_deactivated_assignee_gets_nothing(): void
    {
        $this->notifier($this->ticket())->notify($this->assigned(assigneeId: 3));

        $this->assertSame([], $this->inbox);
        $this->assertSame([], $this->emails);
        $this->assertSame([], $this->pushes);
    }

    public function test_an_unknown_assignee_or_unreadable_todo_sends_nothing(): void
    {
        $this->notifier($this->ticket())->notify($this->assigned(assigneeId: 99));
        $this->notifier(false)->notify($this->assigned());

        $this->assertSame([], $this->inbox);
        $this->assertSame([], $this->pushes);
    }

    public function test_email_off_still_gets_the_inbox_row_and_the_push(): void
    {
        $this->notifier($this->ticket())->notify($this->assigned(assigneeId: 4));

        $this->assertCount(1, $this->inbox);
        $this->assertSame([], $this->emails, '"Receive email notifications" is off');
        $this->assertCount(1, $this->pushes, 'The email switch has no say over push');
    }

    public function test_push_off_still_gets_the_inbox_row_and_the_email(): void
    {
        $this->notifier($this->ticket(), ['usersettings.2.pushNotifications' => '0'])->notify($this->assigned());

        $this->assertCount(1, $this->inbox);
        $this->assertCount(1, $this->emails);
        $this->assertSame([], $this->pushes);
    }

    public function test_push_is_on_until_switched_off(): void
    {
        $this->notifier($this->ticket(), ['usersettings.2.pushNotifications' => '1'])->notify($this->assigned());
        $this->notifier($this->ticket(), [])->notify($this->assigned());

        $this->assertCount(2, $this->pushes);
    }

    public function test_a_push_outage_never_loses_the_inbox_row_or_the_email(): void
    {
        $this->pushBehaviour = function () {
            throw new \RuntimeException('FCM down');
        };

        $this->notifier($this->ticket())->notify($this->assigned());

        $this->assertCount(1, $this->inbox);
        $this->assertCount(1, $this->emails);
        $this->assertSame([], $this->pushes);
    }

    public function test_an_unknown_actor_reads_as_someone(): void
    {
        session(['userdata' => ['id' => 0]]);

        $this->notifier($this->ticket())->notify($this->assigned());

        $this->assertSame("Someone assigned you 'Fix login'", $this->inbox[0]['message']);
        $this->assertSame('Someone assigned this to you · Due Oct 31', $this->pushes[0]['body']);
    }

    /**
     * The domain's register.php subscribes the listener to the class-based event, so an
     * assignment made anywhere (web form, kanban, the app's patch) reaches the notifier, and a
     * notifier failure never reaches the caller that saved the to-do.
     */
    public function test_the_listener_is_registered_for_ticket_assigned_and_swallows_failures(): void
    {
        $received = [];
        app()->instance(AssignmentNotifier::class, $this->make(AssignmentNotifier::class, [
            'notify' => function (TicketAssigned $event) use (&$received) {
                $received[] = $event;
                if ($event->assigneeId === 99) {
                    throw new \RuntimeException('boom');
                }
            },
        ]));

        // The domain register.php files are loaded when the application boots, so the subscription is already in place.
        $listeners = array_column(
            (new \ReflectionClass(EventDispatcher::class))->getProperty('eventRegistry')->getValue()[TicketAssigned::class] ?? [],
            'listener'
        );
        $this->assertContains(NotifyAssignee::class, $listeners);

        TicketAssigned::dispatch(ticketId: 42, projectId: 5, assigneeId: 2, previousAssigneeId: null, assignedToSelf: false);
        TicketAssigned::dispatch(ticketId: 43, projectId: 5, assigneeId: 99, previousAssigneeId: null, assignedToSelf: false);

        $this->assertSame([42, 43], array_map(fn (TicketAssigned $e) => $e->ticketId, $received));
    }
}

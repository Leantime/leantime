<?php

namespace Unit\app\Domain\Notifications\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Auth\Permissions\PermissionService;
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
        // Deliberately NOT a real format: the assignee's fallback must come from the language files, never the translator (which reads the assigner's session).
        'language.dateformat' => 'STUB-FORMAT',
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

    /** @var array<int, string> Role names holding tickets.view in this test's grant map. */
    private array $rolesWithView = ['readonly', 'commenter', 'editor', 'manager', 'admin', 'owner'];

    /** @var array<int, string> Explicit project role (zp_relationuserproject.projectRole) by user id. */
    private array $projectRoles = [];

    private array $dispatcherSnapshot = [];

    protected function setUp(): void
    {
        parent::setUp();

        session(['userdata' => ['id' => 1, 'name' => 'Sam']]);
        session(['usersettings.timezone' => 'UTC']);
        session(['usersettings.language' => 'en-US']);
        session(['usersettings.date_format' => 'd/m/Y']); // the ASSIGNER's format; it must never reach the assignee's push
        session(['usersettings.time_format' => 'H:i']);
        CarbonImmutable::mixin(new CarbonMacros('UTC', 'en-US', 'M j', 'H:i'));

        // role 20 = editor (zp_user.role holds the numeric key)
        $this->users = [
            1 => ['id' => 1, 'firstname' => 'Sam', 'status' => 'a', 'notifications' => 1, 'role' => '20'],
            2 => ['id' => 2, 'firstname' => 'Ada', 'status' => 'a', 'notifications' => 1, 'role' => '20'],
            3 => ['id' => 3, 'firstname' => 'Gone', 'status' => 'i', 'notifications' => 1, 'role' => '20'],
            4 => ['id' => 4, 'firstname' => 'Quiet', 'status' => 'A', 'notifications' => 0, 'role' => '20'],
        ];
        $this->rolesWithView = ['readonly', 'commenter', 'editor', 'manager', 'admin', 'owner'];
        $this->projectRoles = [];
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
                'getUserProjectRelation' => fn ($userId, $projectId = null) => isset($this->projectRoles[(int) $userId]) ? [['projectRole' => $this->projectRoles[(int) $userId]]] : [],
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
            $this->make(PermissionService::class, [
                'roleHasPermission' => fn (string $role, string $permission) => $permission === 'tickets.view' && in_array($role, $this->rolesWithView, true),
            ]),
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
        $this->assertSame('Sam assigned this to you · Due 10/31/2026', $push['body']);
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

    /**
     * Project membership is not enough: the assignee's effective role in the project must hold
     * tickets.view, resolved for the assignee (an explicit assignable project role, else their
     * global role; manager and above always keep the global role).
     *
     * @dataProvider viewCapabilityProvider
     */
    public function test_a_project_member_whose_role_cannot_view_todos_gets_nothing(string $globalRoleKey, ?string $projectRole, bool $expectsNotification): void
    {
        $this->rolesWithView = ['editor', 'manager', 'admin', 'owner']; // commenter and readonly can't see to-dos here
        $this->users[2]['role'] = $globalRoleKey;
        if ($projectRole !== null) {
            $this->projectRoles[2] = $projectRole;
        }

        $this->notifier($this->ticket())->notify($this->assigned());

        $this->assertCount($expectsNotification ? 1 : 0, $this->inbox);
        $this->assertCount($expectsNotification ? 1 : 0, $this->emails);
        $this->assertCount($expectsNotification ? 1 : 0, $this->pushes);
    }

    /**
     * @return array<string, array{string, ?string, bool}>
     */
    public static function viewCapabilityProvider(): array
    {
        return [
            'global editor, no project role: inherits and sees' => ['20', null, true],
            'global editor demoted to commenter in this project' => ['20', '10', false],
            'global commenter promoted to editor in this project' => ['10', '20', true],
            'global commenter, legacy inherit sentinel' => ['10', 'inherit', false],
            'manager keeps the global role whatever the project says' => ['30', '10', true],
            'unknown global role key' => ['0', null, false],
        ];
    }

    public function test_a_todo_without_a_project_sends_nothing(): void
    {
        $this->notifier($this->ticket(['projectId' => 0]))->notify(new TicketAssigned(ticketId: 42, projectId: null, assigneeId: 2, previousAssigneeId: null, assignedToSelf: false));

        $this->assertSame([], $this->inbox);
        $this->assertSame([], $this->pushes);
    }

    /**
     * A stored UTC midnight is shown in the assignee's timezone, exactly as the web shows it
     * (Format::date converts every due date the same way): a deadline of 17:00 on October 30 in
     * Los Angeles is stored as 2026-10-31 00:00:00 and must read October 30 there. The actor's
     * timezone plays no part.
     *
     * @dataProvider midnightDeadlineProvider
     */
    public function test_a_midnight_utc_deadline_reads_as_the_web_shows_it_to_the_assignee(string $assigneeTimezone, string $expected): void
    {
        session(['usersettings.timezone' => 'Pacific/Auckland']);
        CarbonImmutable::mixin(new CarbonMacros('Pacific/Auckland', 'en-US', 'M j', 'H:i'));

        $this->notifier($this->ticket(['dateToFinish' => '2026-10-31 00:00:00']), ['usersettings.2.timezone' => $assigneeTimezone])->notify($this->assigned());

        $this->assertSame('Sam assigned this to you · Due '.$expected, $this->pushes[0]['body']);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function midnightDeadlineProvider(): array
    {
        return [
            '17:00 the day before in Los Angeles' => ['America/Los_Angeles', '10/30/2026'],
            'same day in Berlin' => ['Europe/Berlin', '10/31/2026'],
            'same day in UTC' => ['UTC', '10/31/2026'],
        ];
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
            'west of UTC, still the day before' => ['America/Los_Angeles', '10/30/2026'],
            'east of UTC, same day' => ['Europe/Berlin', '10/31/2026'],
        ];
    }

    public function test_the_due_date_uses_the_assignees_own_date_format_and_language(): void
    {
        $this->notifier($this->ticket(), [
            'usersettings.2.date_format' => 'D j. M',
            'usersettings.2.language' => 'de-DE',
        ])->notify($this->assigned());

        $this->assertSame('Sam assigned this to you · Due Sa. 31. Okt', $this->pushes[0]['body'], 'The assignee\'s own format, in the assignee\'s language');
    }

    /**
     * With no date format of their own, the assignee gets their language's default from the
     * language files, never the assigner's session format (which Language::__() would return).
     *
     * @dataProvider languageDefaultFormatProvider
     */
    public function test_without_a_saved_format_the_assignees_language_decides_not_the_assigners_session(array $settings, string $expected): void
    {
        session(['usersettings.date_format' => 'd/m/Y']);

        $this->notifier($this->ticket(), $settings)->notify($this->assigned());

        $this->assertSame('Sam assigned this to you · Due '.$expected, $this->pushes[0]['body']);
    }

    /**
     * @return array<string, array{array<string, string>, string}>
     */
    public static function languageDefaultFormatProvider(): array
    {
        return [
            'de-DE (d.m.Y in de-DE.ini)' => [['usersettings.2.language' => 'de-DE'], '31.10.2026'],
            'de-DE-inf (two subtags; d.m.Y in de-DE-inf.ini)' => [['usersettings.2.language' => 'de-DE-inf'], '31.10.2026'],
            'en-US (m/d/Y in en-US.ini)' => [['usersettings.2.language' => 'en-US'], '10/31/2026'],
            'no language either: instance default en-US' => [[], '10/31/2026'],
            'unknown language code falls back to en-US' => [['usersettings.2.language' => '../../etc/passwd'], '10/31/2026'],
        ];
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
        $this->assertSame('Someone assigned this to you · Due 10/31/2026', $this->pushes[0]['body']);
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

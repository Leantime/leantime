<?php

namespace Unit\app\Domain\Reminders;

use Carbon\CarbonImmutable;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Schedule;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Reminders\Repositories\Reminders as ReminderRepository;
use Leantime\Domain\Reminders\Services\ReminderDispatcher;
use Leantime\Domain\Reminders\Services\ReminderNotifier;
use Leantime\Domain\Reminders\Services\ReminderSettings;
use Leantime\Domain\Reminders\Services\SummaryBuilder;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Unit\TestCase;

/**
 * The reminder job: a summary goes out at most once per local day, inside the window after the
 * person's time. The inbox row it writes first is the record that it went out. Empty days write
 * nothing, the workspace switch turns it all off. Runs the real inbox check on SQLite.
 */
class ReminderDispatcherTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;
    use ReminderDatabase;

    /** @var array<int, array{user: int, items: array<int, int>}> */
    private array $summariesSent = [];

    private int $builds = 0;

    /** @var array<int, bool> user id → whether their summary is empty */
    private array $empty = [];

    /** @var array<int, array{summary: bool, summaryTime: string, taskPreset: string, timezone: string}> */
    private array $userSettings = [];

    private bool $workspaceEnabled = true;

    private ?string $heartbeat = null;

    /** The next summary's inbox write fails (the notifier throws before sending anything). */
    private bool $inboxFails = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createReminderDatabase();
        $this->addUser(7);
        $this->addUser(8);
        $this->addUser(9, status: 'i');                 // deactivated
        $this->addUser(10, source: 'api');               // API service user
        $this->addProject(1);
        $this->summariesSent = [];
        $this->builds = 0;
        $this->empty = [];
        $this->workspaceEnabled = true;
        $this->heartbeat = null;
        $this->inboxFails = false;
        \Illuminate\Support\Facades\Cache::forget(ReminderDispatcher::CURSOR_KEY);
        $settings = ['summary' => true, 'summaryTime' => '08:00', 'taskPreset' => 'default', 'timezone' => 'UTC'];
        $this->userSettings = [7 => $settings, 8 => ['summaryTime' => '09:30'] + $settings, 9 => $settings, 10 => $settings];
    }

    /**
     * @param  SummaryBuilder|null  $builder  A real builder; by default a stand-in that returns one item, or nothing for users in $this->empty.
     */
    /**
     * @param  \Closure|null  $seconds  Replaces the run clock (seconds), to test the run limit.
     */
    private function dispatcher(?SummaryBuilder $builder = null, ?ReminderRepository $repository = null, ?\Closure $seconds = null): ReminderDispatcher
    {
        $settings = $this->make(ReminderSettings::class, [
            'forUsers' => fn (array $ids) => array_intersect_key($this->userSettings, array_flip($ids)),
            'workspaceEnabled' => fn () => $this->workspaceEnabled,
            'recordHeartbeat' => function (string $now) {
                $this->heartbeat = $now;
            },
        ]);

        $builder ??= $this->make(SummaryBuilder::class, [
            'build' => function (int $userId) {
                $this->builds++;
                $empty = $this->empty[$userId] ?? false;

                return ['isEmpty' => $empty, 'items' => $empty ? [] : [['id' => 1]]];
            },
        ]);

        // The real notifier writes its inbox row (recordSummary), then emails and pushes
        // (deliverSummary); this stand-in writes the same row and notes the delivery.
        $notifier = $this->make(ReminderNotifier::class, [
            'recordSummary' => function (array $user, array $summary, CarbonImmutable $now) {
                if ($this->inboxFails) {
                    $this->inboxFails = false;
                    throw new \RuntimeException('inbox row not written');
                }
                $this->addSummaryRow($user['id'], $now->format('Y-m-d H:i:s'));
            },
            'deliverSummary' => function (array $user, array $summary) {
                $this->summariesSent[] = ['user' => $user['id'], 'items' => array_column($summary['items'], 'id')];
            },
        ]);

        $repository ??= $this->repository();
        if ($seconds === null) {
            return new ReminderDispatcher($repository, $settings, $builder, $notifier);
        }

        return new class($repository, $settings, $builder, $notifier, $seconds) extends ReminderDispatcher
        {
            public function __construct($repository, $settings, $builder, $notifier, private \Closure $clock)
            {
                parent::__construct($repository, $settings, $builder, $notifier);
            }

            protected function seconds(): float
            {
                return ($this->clock)();
            }
        };
    }

    public function test_any_minute_works_and_times_after_the_last_run_go_out_with_it(): void
    {
        $this->userSettings[7]['summaryTime'] = '08:02';
        $this->userSettings[8]['summaryTime'] = '23:58';
        $dispatcher = $this->dispatcher();

        $dispatcher->run($this->utc('2026-11-22 08:00:00'));
        $this->assertSame([], $this->sentTo(), '08:02 isn\'t due at 08:00');
        $dispatcher->run($this->utc('2026-11-22 08:05:00'));
        $this->assertSame([7], $this->sentTo(), '08:02 goes out with the 08:05 run');

        $dispatcher->run($this->utc('2026-11-22 23:50:00'));
        $this->assertSame([7], $this->sentTo());
        $dispatcher->run($this->utc('2026-11-22 23:55:00'));
        $this->assertSame([7, 8], $this->sentTo(), '23:58 is due at the day\'s last run, 23:55');

        $dispatcher->run($this->utc('2026-11-23 00:00:00'));
        $this->assertSame([7, 8], $this->sentTo(), 'the midnight run starts the next day: nothing early');
        $this->assertSame('23:55', ReminderDispatcher::dueTime('23:59'));
        $this->assertSame('08:02', ReminderDispatcher::dueTime('08:02'));
    }

    public function test_a_failed_inbox_write_leaves_the_person_due_for_the_next_run(): void
    {
        $this->inboxFails = true;
        $this->dispatcher()->run($this->utc('2026-11-22 08:00:00'));
        $this->assertSame([], $this->sentTo());

        $this->dispatcher()->run($this->utc('2026-11-22 08:05:00'));
        $this->assertSame([7], $this->sentTo(), 'no inbox row, so still due');
    }

    public function test_a_person_another_run_is_sending_to_is_skipped(): void
    {
        $lock = \Illuminate\Support\Facades\Cache::lock('reminders:summary:7', 120);
        $this->assertTrue($lock->get(), 'the other run holds the person\'s lock');

        $this->dispatcher()->run($this->utc('2026-11-22 08:00:00'));
        $this->assertSame([], $this->sentTo());

        $lock->release();
        $this->dispatcher()->run($this->utc('2026-11-22 08:05:00'));
        $this->assertSame([7], $this->sentTo());
    }

    public function test_the_inbox_is_checked_again_right_before_sending(): void
    {
        // Another run (whose overlap lock had expired) sends to user 7 after this run's batch
        // check but before its own send.
        $repository = new class extends ReminderRepository
        {
            public int $checks = 0;

            public ?\Closure $beforeRecheck = null;

            public function __construct() {}

            public function summariesSentSince(array $sinceByUser): array
            {
                if (++$this->checks === 2 && $this->beforeRecheck !== null) {
                    ($this->beforeRecheck)();
                }

                return parent::summariesSentSince($sinceByUser);
            }
        };
        (new \ReflectionProperty(ReminderRepository::class, 'db'))->setValue($repository, $this->db);
        $repository->beforeRecheck = fn () => $this->addSummaryRow(7, '2026-11-22 08:00:30');

        $this->dispatcher(repository: $repository)->run($this->utc('2026-11-22 08:01:00'));

        $this->assertSame([], $this->sentTo(), 'the recheck finds the other run\'s row');
        $this->assertSame(1, $this->db->table('zp_notifications')->where('userId', 7)->count(), 'only the other run\'s row');
    }

    public function test_a_run_starts_no_new_person_after_its_time_limit(): void
    {
        $this->userSettings[8] = $this->userSettings[7];
        $ticks = [0.0, 10.0, ReminderDispatcher::MAX_RUN_SECONDS + 1.0];
        $clock = function () use (&$ticks) {
            return count($ticks) > 1 ? array_shift($ticks) : $ticks[0];
        };

        $sent = $this->dispatcher(seconds: $clock)->run($this->utc('2026-11-22 08:00:00'));

        $this->assertSame(1, $sent);
        $this->assertSame([7], $this->sentTo(), 'user 8 waits for the next run');

        $this->dispatcher()->run($this->utc('2026-11-22 08:05:00'));
        $this->assertSame([7, 8], $this->sentTo());
    }

    public function test_the_next_run_continues_where_a_busy_run_stopped(): void
    {
        // User 7's summary is empty every time, so it is rebuilt on every run and never written.
        $this->empty[7] = true;
        $this->userSettings[8] = $this->userSettings[7];
        $oneTick = fn (array $ticks) => function () use (&$ticks) {
            return count($ticks) > 1 ? array_shift($ticks) : $ticks[0];
        };

        // Run 1 has time for user 7 only.
        $this->dispatcher(seconds: $oneTick([0.0, 10.0, ReminderDispatcher::MAX_RUN_SECONDS + 1.0]))->run($this->utc('2026-11-22 08:00:00'));
        $this->assertSame([], $this->sentTo());

        // Run 2 again has time for one person: it starts after user 7, so user 8 is reached.
        $this->dispatcher(seconds: $oneTick([0.0, 10.0, ReminderDispatcher::MAX_RUN_SECONDS + 1.0]))->run($this->utc('2026-11-22 08:05:00'));
        $this->assertSame([8], $this->sentTo());

        // A run that gets through everyone wraps around and clears the place it resumes from.
        $this->dispatcher()->run($this->utc('2026-11-22 08:10:00'));
        $this->assertSame(0, (int) \Illuminate\Support\Facades\Cache::get(ReminderDispatcher::CURSOR_KEY, 0));
    }

    /**
     * @dataProvider staleSendProvider
     */
    public function test_a_summary_that_became_stale_while_the_run_was_busy_is_skipped(string $summaryTime, string $runAt, string $sendAt): void
    {
        $this->userSettings[7]['summaryTime'] = $summaryTime;
        $this->userSettings[8]['summary'] = false;
        $dispatcher = $this->dispatcher();
        $later = new class($dispatcher, $this->utc($sendAt)) extends ReminderDispatcher
        {
            public function __construct(ReminderDispatcher $inner, private CarbonImmutable $sendAt)
            {
                // The stand-ins wired into the inner dispatcher.
                $stub = fn (string $property) => (new \ReflectionProperty(ReminderDispatcher::class, $property))->getValue($inner);
                parent::__construct($stub('reminders'), $stub('settings'), $stub('builder'), $stub('notifier'));
            }

            protected function currentTime(): CarbonImmutable
            {
                return $this->sendAt;
            }
        };

        $later->run($this->utc($runAt));

        $this->assertSame([], $this->sentTo());
        $this->assertSame(0, $this->db->table('zp_notifications')->where('userId', 7)->count());
    }

    public static function staleSendProvider(): array
    {
        return [
            'the day ended (yesterday\'s summary)' => ['23:30', '2026-11-22 23:55:00', '2026-11-23 00:02:00'],
            'the window ended' => ['08:00', '2026-11-22 10:59:00', '2026-11-22 11:01:00'],
        ];
    }

    private function utc(string $utc): CarbonImmutable
    {
        return CarbonImmutable::parse($utc, 'UTC');
    }

    /** @return array<int, int> */
    private function sentTo(): array
    {
        return array_column($this->summariesSent, 'user');
    }

    public function test_a_summary_goes_out_once_the_person_s_time_has_come_and_once_a_day(): void
    {
        $dispatcher = $this->dispatcher();

        $dispatcher->run($this->utc('2026-11-22 07:55:00'));
        $this->assertSame([], $this->summariesSent, 'before 08:00 nobody gets one');

        $dispatcher->run($this->utc('2026-11-22 08:00:00'));
        $this->assertSame([7], $this->sentTo(), 'user 8 is set to 09:30; deactivated and API users never');

        $dispatcher->run($this->utc('2026-11-22 08:05:00'));
        $dispatcher->run($this->utc('2026-11-22 09:35:00'));
        $dispatcher->run($this->utc('2026-11-22 10:00:00'));
        $this->assertSame([7, 8], $this->sentTo(), 'the inbox row stops a second summary the same day');

        $dispatcher->run($this->utc('2026-11-23 08:00:00'));
        $this->assertSame([7, 8, 7], $this->sentTo(), 'the next day, again');
        $this->assertSame('2026-11-23 08:00:00', $this->heartbeat);
    }

    public function test_the_inbox_check_is_what_stops_the_second_send(): void
    {
        // Without the check (a repository that never finds the row), the second run sends again.
        $forgetful = new class($this->db) extends ReminderRepository
        {
            public function __construct(\Illuminate\Database\ConnectionInterface $db)
            {
                (new \ReflectionProperty(parent::class, 'db'))->setValue($this, $db);
            }

            public function summariesSentSince(array $sinceByUser): array
            {
                return [];
            }
        };
        $this->dispatcher(repository: $forgetful)->run($this->utc('2026-11-22 08:00:00'));
        $this->dispatcher(repository: $forgetful)->run($this->utc('2026-11-22 08:05:00'));
        $this->assertSame([7, 7], $this->sentTo());

        // With it, the row written by the first run is found.
        $this->summariesSent = [];
        $this->db->table('zp_notifications')->delete();
        $this->dispatcher()->run($this->utc('2026-11-22 08:00:00'));
        $this->dispatcher()->run($this->utc('2026-11-22 08:05:00'));
        $this->assertSame([7], $this->sentTo());
    }

    public function test_only_today_s_summary_row_counts_and_only_this_person_s(): void
    {
        $this->addSummaryRow(7, '2026-11-21 08:00:00');                  // yesterday
        $this->addSummaryRow(8, '2026-11-22 08:00:00');                  // someone else's
        $this->db->table('zp_notifications')->insert(['userId' => 7, 'type' => 'assigned', 'module' => 'tickets', 'datetime' => '2026-11-22 07:00:00']);

        $this->dispatcher()->run($this->utc('2026-11-22 08:00:00'));

        $this->assertSame([7], $this->sentTo());
    }

    public function test_the_day_and_the_window_are_local(): void
    {
        $this->userSettings[7]['timezone'] = 'America/New_York';
        $this->userSettings[8]['summary'] = false;
        // 04:00 UTC on the 22nd is 23:00 on the 21st in New York: yesterday there.
        $this->addSummaryRow(7, '2026-11-22 04:00:00');

        $this->dispatcher()->run($this->utc('2026-11-22 08:00:00')); // 03:00 in New York
        $this->assertSame([], $this->summariesSent);

        $this->dispatcher()->run($this->utc('2026-11-22 13:00:00')); // 08:00 in New York
        $this->assertSame([7], $this->sentTo());

        // 05:30 UTC on the 23rd is 00:30 on the 23rd in New York: today there, so nothing more.
        $this->addSummaryRow(7, '2026-11-23 05:30:00');
        $this->dispatcher()->run($this->utc('2026-11-23 13:00:00'));
        $this->assertSame([7], $this->sentTo());
    }

    public function test_after_the_window_nothing_goes_out_that_day(): void
    {
        $this->dispatcher()->run($this->utc('2026-11-22 11:00:00'));  // 3h past 08:00: still inside
        $this->assertSame([7, 8], $this->sentTo());

        $this->dispatcher()->run($this->utc('2026-11-23 11:05:00'));  // 3h05 past 08:00, 1h35 past 09:30
        $this->assertSame([7, 8, 8], $this->sentTo());
    }

    public function test_an_empty_day_writes_nothing_and_a_later_run_may_still_send_one(): void
    {
        $this->empty = [7 => true];
        $this->userSettings[8]['summary'] = false;

        $this->dispatcher()->run($this->utc('2026-11-22 08:00:00'));
        $this->assertSame([], $this->summariesSent);
        $this->assertSame(0, $this->db->table('zp_notifications')->count(), 'nothing written');

        // Something came due since: a later run inside the window sends the one summary.
        $this->empty = [];
        $this->dispatcher()->run($this->utc('2026-11-22 09:00:00'));
        $this->dispatcher()->run($this->utc('2026-11-22 09:05:00'));
        $this->assertSame([7], $this->sentTo());
        $this->assertSame(2, $this->builds, 'the third run finds the inbox row and builds nothing');
    }

    public function test_summary_off_and_workspace_off(): void
    {
        $this->userSettings[7]['summary'] = false;
        $this->dispatcher()->run($this->utc('2026-11-22 10:00:00'));
        $this->assertSame([8], $this->sentTo());

        $this->summariesSent = [];
        $this->workspaceEnabled = false;
        $this->dispatcher()->run($this->utc('2026-11-23 10:00:00'));
        $this->assertSame([], $this->summariesSent);
        $this->assertSame('2026-11-23 10:00:00', $this->heartbeat, 'the heartbeat is recorded even when reminders are off');
    }

    public function test_a_recurring_to_do_that_resets_in_place_is_in_the_next_cycle_s_summary(): void
    {
        $this->userSettings[8]['summary'] = false;
        $this->addMember(7, 1);
        $this->addTicket(1, ['editorId' => '7', 'dateToFinish' => '2026-11-22 17:00:00']);
        $builder = new TestSummaryBuilder(
            $this->repository(),
            $this->makeEmpty(TicketRepository::class),
            $this->makeEmpty(ProjectRepository::class),
            $this->make(TrustedAppUrl::class, ['forLinks' => fn () => 'https://pm.example.test']),
        );

        $this->dispatcher($builder)->run($this->utc('2026-11-22 08:00:00'));

        // Done, then reset by the recurring plugin: same id, due a week later.
        $this->db->table('zp_tickets')->where('id', 1)->update(['dateToFinish' => '2026-11-29 17:00:00']);
        $this->dispatcher($builder)->run($this->utc('2026-11-28 08:00:00'));

        $this->assertSame([['user' => 7, 'items' => [1]], ['user' => 7, 'items' => [1]]], $this->summariesSent);
    }

    /**
     * The scheduler contract, read from the listener Reminders/register.php actually registers.
     */
    public function test_the_job_runs_every_five_minutes_one_at_a_time_on_one_server(): void
    {
        $registerFile = realpath(APP_ROOT.'/app/Domain/Reminders/register.php');
        $cronListeners = array_filter(
            EventDispatcher::getEventRegistry()['leantime.core.console.consolekernel.schedule.cron'] ?? [],
            fn (array $entry) => $entry['listener'] instanceof \Closure
                && realpath((new \ReflectionFunction($entry['listener']))->getFileName()) === $registerFile
        );
        $this->assertCount(1, $cronListeners, 'Reminders/register.php schedules the reminder job');

        $schedule = new Schedule;
        (array_values($cronListeners)[0]['listener'])(['schedule' => $schedule]);

        $events = array_values(array_filter($schedule->events(), fn ($event) => $event->description === 'reminders:dispatch'));
        $this->assertCount(1, $events);
        $event = $events[0];
        $this->assertInstanceOf(CallbackEvent::class, $event);
        $this->assertSame('*/5 * * * *', $event->expression);
        $this->assertTrue($event->withoutOverlapping);
        $this->assertSame(10, $event->expiresAt);
        $this->assertTrue($event->onOneServer);

        $runs = 0;
        app()->instance(ReminderDispatcher::class, $this->make(ReminderDispatcher::class, [
            'run' => function () use (&$runs) {
                $runs++;

                return 0;
            },
        ]));
        $event->run(app());

        $this->assertSame(1, $runs);
    }
}

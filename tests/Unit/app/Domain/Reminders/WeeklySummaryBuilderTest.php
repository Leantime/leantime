<?php

namespace Unit\app\Domain\Reminders;

use Carbon\CarbonImmutable;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Recap\Services\Recap;
use Leantime\Domain\Reminders\Events\WeeklySummarySectionsFilter;
use Leantime\Domain\Tickets\Permissions\TicketsPermissions;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Unit\TestCase;

/**
 * The week: to-dos due in the next seven local days (listed by day, routines folded, overdue only
 * counted), milestones and project ends, planned days, the look-back from the recap, and plugin
 * sections. Runs the real repository queries on SQLite. "Now" is Monday 2026-11-23 08:00 UTC.
 */
class WeeklySummaryBuilderTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;
    use ReminderDatabase;

    /** @var array<int, array<string, mixed>> what the recap returns */
    private array $finished = [];

    /** @var array<int, array{0: int, 1: string, 2: string}> the recap calls: user, start, end */
    private array $recapCalls = [];

    private array $savedFilters = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->createReminderDatabase();
        $this->addUser(7);
        $this->addProject(1, name: 'Website');
        $this->addMember(7, 1);
        $this->finished = [];
        $this->recapCalls = [];

        // Snapshot the dispatcher's registries and match caches, so a listener added in one test
        // never reaches the next one.
        foreach (['filterRegistry', 'available_hooks', 'patternMatchCache', 'compiledPatternCache', 'filterRegistryVersion'] as $property) {
            $this->savedFilters[$property] = (new \ReflectionProperty(EventDispatcher::class, $property))->getValue();
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->savedFilters as $property => $value) {
            (new \ReflectionProperty(EventDispatcher::class, $property))->setValue(null, $value);
        }

        parent::tearDown();
    }

    private function builder(): TestWeeklySummaryBuilder
    {
        return new TestWeeklySummaryBuilder(
            $this->repository(),
            $this->makeEmpty(TicketRepository::class),
            $this->makeEmpty(ProjectRepository::class),
            $this->make(TrustedAppUrl::class, ['forLinks' => fn () => 'https://pm.example.test']),
            $this->make(Recap::class, [
                'finishedBetweenFor' => function (int $userId, CarbonImmutable $start, CarbonImmutable $end) {
                    $this->recapCalls[] = [$userId, $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')];

                    return $this->finished;
                },
            ]),
        );
    }

    /**
     * @param  array<string, mixed>  $mine
     */
    private function week(array $mine = [], ?array $today = null, int $limit = 5, ?TestWeeklySummaryBuilder $builder = null): array
    {
        $mine += ['taskPreset' => 'default', 'timezone' => 'UTC', 'weeklyLookBack' => true];

        return ($builder ?? $this->builder())->buildWeek(7, CarbonImmutable::parse('2026-11-23 08:00:00', 'UTC'), $mine, $today, $limit);
    }

    public function test_this_week_lists_what_is_due_in_the_next_seven_days(): void
    {
        $this->addTicket(1, ['editorId' => '7', 'dateToFinish' => '2026-11-27 17:00:00', 'headline' => 'Friday report']);
        $this->addTicket(2, ['editorId' => '7', 'dateToFinish' => '2026-11-23 17:00:00', 'headline' => 'Today']);
        $this->addTicket(3, ['editorId' => '7', 'dateToFinish' => '2026-11-30 09:00:00', 'headline' => 'Next Monday']);
        $this->addTicket(4, ['editorId' => '7', 'dateToFinish' => '2026-11-20 09:00:00', 'headline' => 'Last week']);
        $this->addTicket(5, ['editorId' => '7', 'dateToFinish' => '2026-11-25 09:00:00', 'status' => 0, 'headline' => 'Done']);
        $this->addTicket(6, ['editorId' => '99', 'dateToFinish' => '2026-11-25 09:00:00', 'headline' => 'Someone else\'s']);
        $this->addTicket(7, ['editorId' => '7', 'type' => 'milestone', 'editTo' => '2026-11-26 00:00:00', 'headline' => 'Launch']);

        $week = $this->week();

        $this->assertSame(['start' => '2026-11-23', 'end' => '2026-11-29'], $week['week']);
        $this->assertSame(['Today', 'Launch', 'Friday report'], array_column($week['ahead']['items'], 'title'));
        $this->assertSame([0, 3, 4], array_column($week['ahead']['items'], 'daysUntil'));
        $this->assertSame(['kind', 'id', 'title', 'projectId', 'projectName', 'due', 'daysUntil', 'url', 'isYours'], array_keys($week['ahead']['items'][0]), 'the morning summary\'s item shape');
        $this->assertSame(2, $week['ahead']['dueCount']);
        $this->assertSame(1, $week['ahead']['endingCount']);
        $this->assertSame(1, $week['ahead']['overdueCount'], 'overdue is a count, never listed');
        $this->assertSame('ahead', $week['ahead']['kind']);
        $this->assertFalse($week['isEmpty']);
    }

    public function test_more_than_five_become_and_n_more_and_routines_are_one_line(): void
    {
        foreach (range(1, 7) as $id) {
            $this->addTicket($id, ['editorId' => '7', 'dateToFinish' => '2026-11-2'.(3 + ($id % 5)).' 09:00:00']);
        }
        $this->addTicket(8, ['editorId' => '7', 'dateToFinish' => '2026-11-24 09:00:00', 'headline' => 'Stand-up']);
        $this->db->table('zp_recurring_patterns')->insert(['entityId' => 8, 'module' => 'tickets', 'type' => 'daily', 'interval' => 1, 'enabled' => 1]);

        $week = $this->week();

        $this->assertCount(5, $week['ahead']['items']);
        $this->assertSame(2, $week['ahead']['moreCount']);
        $this->assertSame(['count' => 1], $week['ahead']['routine']);
        $this->assertNotContains('Stand-up', array_column($week['ahead']['items'], 'title'));
        $this->assertSame(8, $week['ahead']['dueCount'], 'routines count as due');

        $this->assertCount(7, $this->week(limit: 0)['ahead']['items'], 'limit 0 lists them all');
    }

    public function test_a_to_do_with_reminders_off_is_left_out_and_an_opt_in_is_included(): void
    {
        $this->addTicket(1, ['editorId' => '7', 'dateToFinish' => '2026-11-25 09:00:00', 'headline' => 'Turned off']);
        $this->addTicket(2, ['editorId' => '99', 'dateToFinish' => '2026-11-25 09:00:00', 'headline' => 'Asked about']);
        $this->db->table('zp_settings')->insert([['key' => 'usersettings.7.reminders.task.1', 'value' => 'off'], ['key' => 'usersettings.7.reminders.task.2', 'value' => 'default']]);

        $items = $this->week()['ahead']['items'];

        $this->assertSame(['Asked about'], array_column($items, 'title'));
        $this->assertFalse($items[0]['isYours']);
    }

    public function test_planned_days_are_counted_per_day_with_set_times(): void
    {
        // Date-only plans (local midnight), one spanning Wednesday and Thursday, one time box.
        $this->addTicket(1, ['editorId' => '7', 'editFrom' => '2026-11-25 00:00:00']);
        $this->addTicket(2, ['editorId' => '7', 'editFrom' => '2026-11-25 00:00:00', 'editTo' => '2026-11-27 00:00:00']);
        $this->addTicket(3, ['editorId' => '7', 'editFrom' => '2026-11-26 14:00:00', 'editTo' => '2026-11-26 15:30:00']);
        $this->addTicket(4, ['editorId' => '7', 'editFrom' => '2026-11-25 00:00:00', 'status' => 0]);   // done
        $this->addTicket(5, ['editorId' => '7', 'editFrom' => '2026-12-02 00:00:00']);                    // next week
        $this->addTicket(6, ['editorId' => '7', 'editFrom' => '2026-11-20 00:00:00']);                    // last week

        $ahead = $this->week()['ahead'];

        $this->assertSame([
            ['date' => '2026-11-25', 'count' => 2, 'timeBoxed' => 0],
            ['date' => '2026-11-26', 'count' => 2, 'timeBoxed' => 1],
        ], $ahead['planned']);
        $this->assertSame(3, $ahead['plannedCount']);
    }

    public function test_planned_days_are_read_in_the_person_s_timezone(): void
    {
        // Wednesday midnight in Los Angeles is 08:00 UTC.
        $this->addTicket(1, ['editorId' => '7', 'editFrom' => '2026-11-25 08:00:00']);

        $ahead = $this->week(['timezone' => 'America/Los_Angeles'])['ahead'];

        $this->assertSame([['date' => '2026-11-25', 'count' => 1, 'timeBoxed' => 0]], $ahead['planned']);
    }

    public function test_the_look_back_is_the_seven_days_before_today_and_names_three(): void
    {
        $this->finished = array_map(fn (int $id) => [
            'taskId' => $id, 'headline' => 'Done '.$id, 'projectId' => 1, 'projectName' => 'Website', 'milestoneId' => null,
            'finishedAt' => '2026-11-2'.$id.'T10:00:00Z', 'closedBy' => $id === 2 ? ['userId' => 8, 'name' => 'Marcel'] : null,
        ], [2, 1, 0, 9]);

        $week = $this->week();

        $this->assertSame([[7, '2026-11-16 00:00:00', '2026-11-22 23:59:59']], $this->recapCalls);
        $this->assertSame('lookBack', $week['lookBack']['kind']);
        $this->assertSame(['2026-11-16', '2026-11-22', 4], [$week['lookBack']['start'], $week['lookBack']['end'], $week['lookBack']['count']]);
        $this->assertSame(['Done 2', 'Done 1', 'Done 0'], array_column($week['lookBack']['items'], 'title'));
        $this->assertSame(['userId' => 8, 'name' => 'Marcel'], $week['lookBack']['items'][0]['closedBy']);
        $this->assertSame('https://pm.example.test/dashboard/home#/tickets/showTicket/2', $week['lookBack']['items'][0]['url']);
        $this->assertFalse($week['isEmpty'], 'a look-back alone is something to show');
    }

    public function test_no_look_back_when_nothing_was_finished_or_it_is_turned_off(): void
    {
        $this->assertNull($this->week()['lookBack'], 'never a zero');

        $this->finished = [['taskId' => 1, 'headline' => 'Done', 'projectId' => 1, 'projectName' => 'Website', 'milestoneId' => null, 'finishedAt' => '2026-11-20T10:00:00Z', 'closedBy' => null]];
        $this->recapCalls = [];
        $this->assertNull($this->week(['weeklyLookBack' => false])['lookBack']);
        $this->assertSame([], $this->recapCalls, 'not even read');
    }

    public function test_nothing_to_show_is_empty_and_an_overdue_count_alone_is_nothing(): void
    {
        $this->addTicket(1, ['editorId' => '7', 'dateToFinish' => '2026-11-20 09:00:00']);

        $week = $this->week();

        $this->assertTrue($week['isEmpty']);
        $this->assertSame(1, $week['ahead']['overdueCount']);
    }

    public function test_permissions_and_hidden_projects_follow_the_morning_rules(): void
    {
        $this->addProject(2);
        $this->addTicket(1, ['editorId' => '7', 'dateToFinish' => '2026-11-25 09:00:00', 'projectId' => 2]);
        $this->addTicket(2, ['editorId' => '7', 'editFrom' => '2026-11-25 00:00:00', 'projectId' => 2]);
        $builder = $this->builder();
        $builder->deniedPermissions = ['7|2|'.TicketsPermissions::VIEW];

        $week = $this->week(builder: $builder);

        $this->assertSame([], $week['ahead']['items']);
        $this->assertSame(0, $week['ahead']['plannedCount']);
        $this->assertTrue($week['isEmpty']);
    }

    public function test_today_is_a_section_only_when_given_and_not_empty(): void
    {
        $this->addTicket(1, ['editorId' => '7', 'dateToFinish' => '2026-11-25 09:00:00']);
        $today = ['date' => '2026-11-23', 'items' => [['id' => 9]], 'moreCount' => 0, 'routine' => ['count' => 0, 'items' => []], 'overdueCount' => 0, 'dueToday' => 1, 'dueTomorrow' => 0, 'later' => 0, 'isEmpty' => false];

        $week = $this->week(today: $today);
        $this->assertSame('today', $week['today']['kind']);
        $this->assertSame(['today', 'ahead'], array_column($week['sections'], 'kind'), 'today, then the week');

        $this->assertNull($this->week(today: ['isEmpty' => true] + $today)['today']);
        $this->assertNull($this->week()['today']);
    }

    public function test_plugin_sections_are_cleaned_and_only_count_when_they_say_so(): void
    {
        EventDispatcher::add_filter_listener(WeeklySummarySectionsFilter::class, function (array $sections, WeeklySummarySectionsFilter $filter) {
            $sections[] = [
                'kind' => 'plugin:myweek.picks',
                'title' => '<b>What you picked</b>',
                'lines' => ['Sooner: 2', '<i>Later</i>: 1', '', ['not a line'], 'four', 'five', 'six', 'seven'],
                'url' => '/my-week',
                'countsForSending' => true,
            ];
            $sections[] = ['kind' => 'plugin:myweek.picks', 'title' => 'Duplicate'];
            $sections[] = ['kind' => 'not-a-plugin', 'title' => 'Bad kind'];
            $sections[] = ['kind' => 'plugin:other.notes', 'title' => 'Notes', 'url' => 'https://elsewhere.example.test/x'];
            $sections[] = ['kind' => 'plugin:other.empty', 'title' => '   '];
            $sections[] = ['kind' => 'plugin:other.user', 'title' => 'For '.$filter->userId.' '.$filter->weekStart.'–'.$filter->weekEnd.' '.$filter->timezone];

            return $sections;
        });

        $week = $this->week();
        $plugins = array_values(array_filter($week['sections'], fn (array $section) => str_starts_with($section['kind'], 'plugin:')));

        $this->assertSame(['plugin:myweek.picks', 'plugin:other.notes', 'plugin:other.user'], array_column($plugins, 'kind'));
        $this->assertSame([
            'kind' => 'plugin:myweek.picks',
            'title' => 'What you picked',
            'lines' => ['Sooner: 2', 'Later: 1', 'four', 'five', 'six'],
            'url' => 'https://pm.example.test/my-week',
            'countsForSending' => true,
        ], $plugins[0]);
        $this->assertNull($plugins[1]['url'], 'links leave only for this Leantime');
        $this->assertFalse($plugins[1]['countsForSending']);
        $this->assertSame('For 7 2026-11-23–2026-11-29 UTC', $plugins[2]['title'], 'the filter gets the person and their week');
        $this->assertFalse($week['isEmpty'], 'a section that counts makes a summary on its own');
    }

    public function test_a_plugin_section_that_does_not_count_sends_nothing_on_its_own(): void
    {
        EventDispatcher::add_filter_listener(WeeklySummarySectionsFilter::class, fn (array $sections) => [...$sections, ['kind' => 'plugin:other.notes', 'title' => 'Notes']]);

        $week = $this->week();

        $this->assertTrue($week['isEmpty']);
        $this->assertSame(['plugin:other.notes'], array_column(array_slice($week['sections'], 1), 'kind'));
    }

    public function test_a_failing_plugin_is_left_out_and_the_week_still_builds(): void
    {
        $this->addTicket(1, ['editorId' => '7', 'dateToFinish' => '2026-11-25 09:00:00']);
        EventDispatcher::add_filter_listener(WeeklySummarySectionsFilter::class, function () {
            throw new \RuntimeException('plugin broke');
        });

        $week = $this->week();

        $this->assertSame(['ahead'], array_column($week['sections'], 'kind'));
        $this->assertFalse($week['isEmpty']);
    }
}

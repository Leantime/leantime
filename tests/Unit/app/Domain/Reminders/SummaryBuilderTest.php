<?php

namespace Unit\app\Domain\Reminders;

use Carbon\CarbonImmutable;
use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Reminders\Services\SummaryBuilder;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Unit\TestCase;

/**
 * The morning summary for one person on one local day: what's theirs, when each preset shows a
 * to-do, routines, overdue, the cap, milestones and project ends. Runs the real queries on SQLite.
 */
class SummaryBuilderTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;
    use ReminderDatabase;

    /** Sunday 22 November 2026, 13:00 UTC. */
    private CarbonImmutable $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = CarbonImmutable::parse('2026-11-22 13:00:00', 'UTC');
        $this->createReminderDatabase();
        $this->addUser(7);
        $this->addProject(1, name: 'Website');
        $this->addMember(7, 1);
    }

    private function builder(array $hiddenProjects = []): TestSummaryBuilder
    {
        $builder = new TestSummaryBuilder(
            $this->repository(),
            $this->makeEmpty(TicketRepository::class),
            $this->makeEmpty(ProjectRepository::class),
            $this->make(TrustedAppUrl::class, ['forLinks' => fn () => 'https://pm.example.test']),
        );
        $builder->hiddenProjects = $hiddenProjects;

        return $builder;
    }

    private function build(string $timezone = 'UTC', string $preset = 'default', array $hiddenProjects = [], array $denied = []): array
    {
        $builder = $this->builder($hiddenProjects);
        $builder->deniedPermissions = $denied;

        return $builder->build(7, $this->now, $timezone, $preset);
    }

    /** @return array<int, int> listed item ids */
    private function ids(array $summary): array
    {
        return array_map(fn (array $item) => $item['id'], $summary['items']);
    }

    public function test_default_preset_shows_the_day_before_and_the_day_it_is_due(): void
    {
        $this->addTicket(1, ['editorId' => '7', 'dateToFinish' => '2026-11-22 17:00:00']);
        $this->addTicket(2, ['editorId' => '7', 'dateToFinish' => '2026-11-23 17:00:00']);
        $this->addTicket(3, ['editorId' => '7', 'dateToFinish' => '2026-11-24 17:00:00']);
        $this->addTicket(4, ['editorId' => '7']); // no due date

        $summary = $this->build();

        $this->assertSame([1, 2], $this->ids($summary));
        $this->assertSame(1, $summary['dueToday']);
        $this->assertSame(1, $summary['dueTomorrow']);
        $this->assertSame(0, $summary['later']);
        $this->assertSame('2026-11-22', $summary['date']);
        $this->assertSame('https://pm.example.test/dashboard/home#/tickets/showTicket/1', $summary['items'][0]['url']);
        $this->assertSame('2026-11-22T17:00:00Z', $summary['items'][0]['due']);
        $this->assertSame('Website', $summary['items'][0]['projectName']);
        $this->assertFalse($summary['isEmpty']);
    }

    public function test_yours_means_assigned_to_you_or_unassigned_and_created_by_you(): void
    {
        $due = '2026-11-22 17:00:00';
        $this->addTicket(1, ['editorId' => '7', 'userId' => 99, 'dateToFinish' => $due]);   // assigned to me
        $this->addTicket(2, ['editorId' => null, 'userId' => 7, 'dateToFinish' => $due]);   // unassigned, mine
        $this->addTicket(3, ['editorId' => '', 'userId' => 7, 'dateToFinish' => $due]);     // unassigned (empty), mine
        $this->addTicket(4, ['editorId' => '0', 'userId' => 7, 'dateToFinish' => $due]);    // unassigned (0), mine
        $this->addTicket(5, ['editorId' => null, 'userId' => 99, 'dateToFinish' => $due]);  // unassigned, someone else's
        $this->addTicket(6, ['editorId' => '99', 'userId' => 7, 'dateToFinish' => $due]);   // I made it, someone else has it

        $this->assertSame([1, 2, 3, 4], $this->ids($this->build()));
    }

    public function test_done_closed_archived_and_hidden_are_left_out(): void
    {
        $due = '2026-11-22 17:00:00';
        $this->addProject(2, state: 1);   // closed
        $this->addProject(3, state: -1);  // archived
        $this->addProject(4);             // can't see it any more
        $this->addTicket(1, ['editorId' => '7', 'dateToFinish' => $due, 'status' => 0]);
        $this->addTicket(2, ['editorId' => '7', 'dateToFinish' => $due, 'projectId' => 2]);
        $this->addTicket(3, ['editorId' => '7', 'dateToFinish' => $due, 'projectId' => 3]);
        $this->addTicket(4, ['editorId' => '7', 'dateToFinish' => $due, 'projectId' => 4]);
        $this->addTicket(5, ['editorId' => '7', 'dateToFinish' => $due]);

        $this->assertSame([5], $this->ids($this->build(hiddenProjects: [4])));
    }

    public function test_each_preset_on_a_task(): void
    {
        foreach ([1 => 0, 2 => 1, 3 => 2, 4 => 3, 5 => 4] as $id => $days) {
            $this->addTicket($id, ['editorId' => '7', 'dateToFinish' => $this->now->addDays($days)->setTime(17, 0)->format('Y-m-d H:i:s')]);
        }

        $this->assertSame([1, 2], $this->ids($this->build(preset: 'default')));
        $this->assertSame([1], $this->ids($this->build(preset: 'less')));
        $this->assertSame([1, 2, 3, 4], $this->ids($this->build(preset: 'more')), 'more: 3 days before without a start date');
        $this->assertTrue($this->build(preset: 'off')['isEmpty']);
    }

    public function test_more_runs_from_the_start_date(): void
    {
        $this->addTicket(1, ['editorId' => '7', 'dateToFinish' => '2026-12-10 17:00:00', 'editFrom' => '2026-11-20 09:00:00']); // started
        $this->addTicket(2, ['editorId' => '7', 'dateToFinish' => '2026-12-10 17:00:00', 'editFrom' => '2026-11-25 09:00:00']); // not yet

        $this->assertSame([1], $this->ids($this->build(preset: 'more')));
        $this->assertSame([], $this->ids($this->build(preset: 'default')));
    }

    public function test_a_task_choice_wins_over_the_default(): void
    {
        $this->addTicket(1, ['editorId' => '7', 'dateToFinish' => '2026-11-23 17:00:00']);
        $this->addTicket(2, ['editorId' => '7', 'dateToFinish' => '2026-11-23 17:00:00']);
        $this->addTicket(3, ['editorId' => '7', 'dateToFinish' => '2026-11-25 17:00:00']);
        $settings = $this->settingRepository();
        $settings->saveSetting('usersettings.7.reminders.task.1', 'off');
        $settings->saveSetting('usersettings.7.reminders.task.2', 'less');
        $settings->saveSetting('usersettings.7.reminders.task.3', 'more');
        $settings->saveSetting('usersettings.70.reminders.task.2', 'more'); // someone else's choice
        $settings->saveSetting('usersettings.7.reminders.task.x', 'more');  // not a to-do id

        $this->assertSame([3], $this->ids($this->build()));

        $settings->deleteSetting('usersettings.7.reminders.task.1');
        $this->assertSame([1, 3], $this->ids($this->build()), 'removing the choice brings the default back');
    }

    public function test_someone_else_s_to_do_is_off_until_you_opt_in_and_then_only_you_get_it(): void
    {
        $this->addUser(99);
        $this->addMember(99, 1);
        $this->addTicket(1, ['editorId' => '99', 'userId' => 99, 'dateToFinish' => '2026-11-22 17:00:00']);  // Ben's, due today
        $this->addTicket(2, ['editorId' => '99', 'userId' => 7, 'dateToFinish' => '2026-11-22 17:00:00']);   // I made it, Ben has it

        $this->assertSame([], $this->ids($this->build()), 'not yours: off by default');
        $bensBefore = $this->builder()->build(99, $this->now, 'UTC', 'default');

        $this->settingRepository()->saveSetting('usersettings.7.reminders.task.1', 'default');
        $summary = $this->build();

        $this->assertSame([1], $this->ids($summary));
        $this->assertFalse($summary['items'][0]['isYours']);
        $this->assertSame($bensBefore, $this->builder()->build(99, $this->now, 'UTC', 'default'), 'Ben\'s summary doesn\'t change');
        $this->assertSame([1, 2], $this->ids($bensBefore));
        $this->assertTrue($bensBefore['items'][0]['isYours']);
    }

    public function test_an_opt_in_follows_the_same_rules_as_your_own_to_dos(): void
    {
        $this->addProject(4);
        $base = ['editorId' => '99', 'userId' => 99];
        $this->addTicket(1, $base + ['dateToFinish' => '2026-11-23 17:00:00']);                     // due tomorrow: default shows
        $this->addTicket(2, $base + ['dateToFinish' => '2026-11-25 17:00:00']);                     // in 3 days: only "more" shows
        $this->addTicket(3, $base + ['dateToFinish' => '2026-11-22 17:00:00', 'status' => 0]);      // done
        $this->addTicket(4, $base + ['dateToFinish' => '2026-11-22 17:00:00', 'projectId' => 4]);   // can't see the project any more
        $this->addTicket(5, $base);                                                                  // no due date
        $this->addTicket(6, $base + ['dateToFinish' => '2026-11-22 17:00:00']);                     // opted in, then off
        $settings = $this->settingRepository();
        foreach ([1 => 'default', 2 => 'default', 3 => 'more', 4 => 'more', 5 => 'more', 6 => 'off'] as $id => $preset) {
            $settings->saveSetting('usersettings.7.reminders.task.'.$id, $preset);
        }

        $this->assertSame([1], $this->ids($this->build(hiddenProjects: [4])));

        $settings->saveSetting('usersettings.7.reminders.task.2', 'more');
        $this->assertSame([1, 2], $this->ids($this->build(hiddenProjects: [4])));
    }

    public function test_a_to_do_that_is_yours_and_chosen_shows_once(): void
    {
        $this->addTicket(1, ['editorId' => '7', 'dateToFinish' => '2026-11-22 17:00:00']);
        $this->settingRepository()->saveSetting('usersettings.7.reminders.task.1', 'more');

        $summary = $this->build();

        $this->assertSame([1], $this->ids($summary));
        $this->assertSame(1, $summary['dueToday']);
        $this->assertTrue($summary['items'][0]['isYours']);
    }

    public function test_a_stored_choice_keeps_working_when_the_assignee_changes(): void
    {
        $this->addTicket(1, ['editorId' => '99', 'userId' => 99, 'dateToFinish' => '2026-11-23 17:00:00']);
        $this->addTicket(2, ['editorId' => '7', 'userId' => 99, 'dateToFinish' => '2026-11-23 17:00:00']);
        $this->settingRepository()->saveSetting('usersettings.7.reminders.task.1', 'more');

        $this->assertSame([1, 2], $this->ids($this->build()));

        // To-do 1 becomes mine (the choice still applies); I stop being to-do 2's assignee
        // without a stored choice (it stops).
        $this->db->table('zp_tickets')->where('id', 1)->update(['editorId' => '7']);
        $this->db->table('zp_tickets')->where('id', 2)->update(['editorId' => '99']);
        $summary = $this->build();
        $this->assertSame([1], $this->ids($summary));
        $this->assertTrue($summary['items'][0]['isYours']);

        // I stop being to-do 1's assignee again: I opted in, so I keep getting it.
        $this->db->table('zp_tickets')->where('id', 1)->update(['editorId' => '99']);
        $this->assertSame([1], $this->ids($this->build()));
    }

    public function test_a_recurring_to_do_that_resets_in_place_shows_again_next_cycle(): void
    {
        $this->addTicket(1, ['editorId' => '7', 'dateToFinish' => '2026-11-22 17:00:00']);
        $this->assertSame([1], $this->ids($this->build()));

        // Done, then the recurring plugin resets it: same id, back to New, due a week later.
        $this->db->table('zp_tickets')->where('id', 1)->update(['status' => 0]);
        $this->assertSame([], $this->ids($this->build()));
        $this->db->table('zp_tickets')->where('id', 1)->update(['status' => 3, 'dateToFinish' => '2026-11-29 17:00:00']);

        $this->now = CarbonImmutable::parse('2026-11-28 08:00:00', 'UTC');
        $this->assertSame([1], $this->ids($this->build()), 'the day before its new due date');
    }

    public function test_daily_and_weekday_routines_show_once_on_the_day_as_one_line(): void
    {
        $this->addTicket(1, ['editorId' => '7', 'dateToFinish' => '2026-11-22 17:00:00']);
        $this->addTicket(2, ['editorId' => '7', 'dateToFinish' => '2026-11-22 17:00:00']);
        $this->addTicket(3, ['editorId' => '7', 'dateToFinish' => '2026-11-23 17:00:00']); // routine, due tomorrow: not yet
        $this->addTicket(4, ['editorId' => '7', 'dateToFinish' => '2026-11-22 17:00:00']); // weekly on 2 days: not a routine
        $this->addTicket(5, ['editorId' => '7', 'dateToFinish' => '2026-11-23 17:00:00']); // ordinary
        $this->db->table('zp_recurring_patterns')->insert([
            ['entityId' => 1, 'module' => 'tickets', 'type' => 'daily', 'interval' => 1, 'weekDays' => null, 'enabled' => 1],
            ['entityId' => 2, 'module' => 'tickets', 'type' => 'weekly', 'interval' => 1, 'weekDays' => '["mon","tue","wed","thu","fri"]', 'enabled' => 1],
            ['entityId' => 3, 'module' => 'tickets', 'type' => 'daily', 'interval' => 1, 'weekDays' => null, 'enabled' => 1],
            ['entityId' => 4, 'module' => 'tickets', 'type' => 'weekly', 'interval' => 1, 'weekDays' => '["mon","thu"]', 'enabled' => 1],
        ]);

        $summary = $this->build();

        $this->assertSame([4, 5], $this->ids($summary));
        $this->assertSame(2, $summary['routine']['count']);
        $this->assertSame([1, 2], array_column($summary['routine']['items'], 'id'));
        $this->assertSame(3, $summary['dueToday'], 'routines count toward "due today"');
    }

    public function test_overdue_is_one_count_and_never_makes_a_summary_alone(): void
    {
        $this->addTicket(1, ['editorId' => '7', 'dateToFinish' => '2026-11-20 17:00:00']);
        $this->addTicket(2, ['editorId' => '7', 'dateToFinish' => '2026-10-01 17:00:00']);

        $summary = $this->build();
        $this->assertSame(2, $summary['overdueCount']);
        $this->assertSame([], $summary['items']);
        $this->assertTrue($summary['isEmpty']);

        $this->addTicket(3, ['editorId' => '7', 'dateToFinish' => '2026-11-22 17:00:00']);
        $summary = $this->build();
        $this->assertSame(2, $summary['overdueCount']);
        $this->assertSame([3], $this->ids($summary));
        $this->assertSame(0, $this->build(preset: 'off')['overdueCount'], 'off is off, overdue too');
    }

    public function test_five_items_then_and_n_more(): void
    {
        for ($id = 1; $id <= 8; $id++) {
            $this->addTicket($id, ['editorId' => '7', 'dateToFinish' => '2026-11-22 17:00:00', 'priority' => (string) (9 - $id)]);
        }

        $summary = $this->build();

        $this->assertCount(5, $summary['items']);
        $this->assertSame(3, $summary['moreCount']);
        $this->assertSame(8, $summary['dueToday']);
        $this->assertSame([8, 7, 6, 5, 4], $this->ids($summary), 'same day: most important first');
    }

    public function test_limit_0_lists_everything_in_the_same_order_and_shape(): void
    {
        for ($id = 1; $id <= 8; $id++) {
            $this->addTicket($id, ['editorId' => '7', 'dateToFinish' => '2026-11-22 17:00:00', 'priority' => (string) (9 - $id)]);
        }
        $this->addTicket(9, ['editorId' => '7', 'dateToFinish' => '2026-11-22 17:00:00']);
        $this->db->table('zp_recurring_patterns')->insert(['entityId' => 9, 'module' => 'tickets', 'type' => 'daily', 'interval' => 1, 'weekDays' => null, 'enabled' => 1]);

        $capped = $this->builder()->build(7, $this->now, 'UTC');
        $all = $this->builder()->build(7, $this->now, 'UTC', 'default', 0);
        $three = $this->builder()->build(7, $this->now, 'UTC', 'default', 3);

        $this->assertSame([8, 7, 6, 5, 4, 3, 2, 1], $this->ids($all));
        $this->assertSame(0, $all['moreCount']);
        $this->assertSame(array_slice($all['items'], 0, 5), $capped['items'], 'the top 5 are the start of the whole list');
        $this->assertSame([8, 7, 6], $this->ids($three));
        $this->assertSame(5, $three['moreCount']);
        foreach (['routine', 'overdueCount', 'dueToday', 'dueTomorrow', 'later', 'date', 'isEmpty'] as $field) {
            $this->assertSame($capped[$field], $all[$field], $field.' doesn\'t depend on the limit');
        }
        $this->assertSame([9], array_column($all['routine']['items'], 'id'));
        $this->assertArrayHasKey('isYours', $all['items'][7]);
    }

    public function test_days_are_the_person_s_local_days(): void
    {
        // 03:00 UTC on the 23rd is 22:00 on the 22nd in New York, where it's 08:00 on the 22nd.
        $this->addTicket(1, ['editorId' => '7', 'dateToFinish' => '2026-11-23 03:00:00']);

        $newYork = $this->build('America/New_York');
        $this->assertSame('2026-11-22', $newYork['date']);
        $this->assertSame(0, $newYork['items'][0]['daysUntil']);

        $this->assertSame(1, $this->build('UTC')['items'][0]['daysUntil']);
        $this->assertSame(0, $this->build('Pacific/Auckland')['items'][0]['daysUntil'], 'Auckland is already on the 23rd');
    }

    public function test_milestones_show_a_week_before_and_the_day_before(): void
    {
        $this->addTicket(1, ['type' => 'milestone', 'editorId' => '7', 'editTo' => '2026-11-29 12:00:00']); // 7 days
        $this->addTicket(2, ['type' => 'milestone', 'editorId' => '7', 'editTo' => '2026-11-23 12:00:00']); // 1 day
        $this->addTicket(3, ['type' => 'milestone', 'editorId' => '7', 'editTo' => '2026-11-25 12:00:00']); // 3 days
        $this->addTicket(4, ['type' => 'milestone', 'editorId' => null, 'userId' => 7, 'editTo' => '2026-11-23 12:00:00']); // unassigned, mine
        $this->addTicket(5, ['type' => 'milestone', 'editorId' => '99', 'editTo' => '2026-11-23 12:00:00']); // someone else's
        $this->addTicket(6, ['type' => 'milestone', 'editorId' => '7', 'editTo' => '2026-11-23 12:00:00', 'status' => 0]); // done
        $this->addTicket(7, ['type' => 'milestone', 'editorId' => '7', 'dateToFinish' => '2026-11-22 12:00:00']); // milestones aren't to-dos

        $summary = $this->build();

        $this->assertSame([2, 4, 1], $this->ids($summary));
        $this->assertSame('milestone', $summary['items'][0]['kind']);
        $this->assertSame('https://pm.example.test/dashboard/home#/tickets/editMilestone/2', $summary['items'][0]['url']);
    }

    public function test_project_ends_go_to_the_managers_or_to_everyone_when_there_are_none(): void
    {
        $this->addUser(8, role: '30'); // manager
        $this->addUser(9);
        $this->addProject(5, end: '2026-11-23 12:00:00', name: 'Launch');
        $this->addMember(7, 5);
        $this->addMember(8, 5);
        $this->addProject(6, end: '2026-11-29 12:00:00', name: 'Small team');
        $this->addMember(7, 6);
        $this->addMember(9, 6);
        $this->addProject(7, end: '2026-11-25 12:00:00', name: 'Not this week');
        $this->addMember(7, 7);
        $this->addProject(8, state: 1, end: '2026-11-23 12:00:00', name: 'Closed');
        $this->addMember(7, 8);

        $mine = $this->build();
        $this->assertSame([6], $this->ids($mine), 'project 5 has a manager, so only they hear about it');
        $this->assertSame('project', $mine['items'][0]['kind']);
        $this->assertSame(7, $mine['items'][0]['daysUntil']);

        $managers = $this->builder()->build(8, $this->now, 'UTC');
        $this->assertSame([5], $this->ids($managers));

        // A project manager by project role counts too.
        $this->db->table('zp_relationuserproject')->where('userId', 9)->where('projectId', 6)->update(['projectRole' => '30']);
        $this->assertSame([], $this->ids($this->build()));
    }

    public function test_nothing_is_sent_that_the_person_s_own_role_may_not_view(): void
    {
        $this->addProject(2);
        $this->addMember(7, 2);
        $this->addProject(5, end: '2026-11-23 12:00:00', name: 'Launch');
        $this->addMember(7, 5);
        $this->addTicket(1, ['editorId' => '7', 'dateToFinish' => '2026-11-22 17:00:00']);                    // mine, project 1
        $this->addTicket(2, ['editorId' => '7', 'dateToFinish' => '2026-11-22 17:00:00', 'projectId' => 2]);  // mine, project 2
        $this->addTicket(3, ['editorId' => '99', 'userId' => 99, 'dateToFinish' => '2026-11-22 17:00:00', 'projectId' => 2]); // opted in
        $this->addTicket(4, ['type' => 'milestone', 'editorId' => '7', 'editTo' => '2026-11-23 12:00:00', 'projectId' => 2]);
        $this->settingRepository()->saveSetting('usersettings.7.reminders.task.3', 'default');

        $this->assertSame([1, 2, 3, 5, 4], $this->ids($this->build()), 'today first, then tomorrow');

        // On the team of project 2 and 5, but the role there lacks tickets.view / projects.view.
        $summary = $this->build(denied: ['7|2|tickets.view', '7|5|projects.view']);
        $this->assertSame([1], $this->ids($summary), 'no to-do, opt-in, milestone or project end the role can\'t see');
    }

    public function test_api_accounts_on_the_team_are_not_its_managers(): void
    {
        $this->addUser(40, role: '40', source: 'api'); // an admin API key with project access
        $this->addProject(5, end: '2026-11-23 12:00:00', name: 'Launch');
        $this->addMember(7, 5);
        $this->addMember(40, 5);

        $this->assertSame([5], $this->ids($this->build()), 'the team still has no human manager, so everyone on it hears');
    }

    public function test_only_today_s_routines_are_grouped_and_later_ones_are_listed_by_date(): void
    {
        $this->addTicket(1, ['editorId' => '7', 'dateToFinish' => '2026-11-22 17:00:00']); // routine today
        $this->addTicket(2, ['editorId' => '7', 'dateToFinish' => '2026-11-23 17:00:00']); // routine tomorrow, chosen "more"
        $this->db->table('zp_recurring_patterns')->insert([
            ['entityId' => 1, 'module' => 'tickets', 'type' => 'daily', 'interval' => 1, 'weekDays' => null, 'enabled' => 1],
            ['entityId' => 2, 'module' => 'tickets', 'type' => 'daily', 'interval' => 1, 'weekDays' => null, 'enabled' => 1],
        ]);
        $this->settingRepository()->saveSetting('usersettings.7.reminders.task.2', 'more');

        $summary = $this->build();

        $this->assertSame([2], $this->ids($summary), 'tomorrow\'s occurrence is listed with its day');
        $this->assertSame(1, $summary['items'][0]['daysUntil']);
        $this->assertSame([1], array_column($summary['routine']['items'], 'id'));
        $this->assertSame(1, $summary['dueToday']);
        $this->assertSame(1, $summary['dueTomorrow']);
    }

    public function test_priority_0_means_no_priority_and_sorts_after_critical(): void
    {
        $due = '2026-11-22 17:00:00';
        $this->addTicket(1, ['editorId' => '7', 'dateToFinish' => $due, 'priority' => '0', 'headline' => 'A no priority']);
        $this->addTicket(2, ['editorId' => '7', 'dateToFinish' => $due, 'priority' => '1', 'headline' => 'B critical']);
        $this->addTicket(3, ['editorId' => '7', 'dateToFinish' => $due, 'priority' => '', 'headline' => 'C missing']);

        $this->assertSame([2, 1, 3], $this->ids($this->build()));
    }

    public function test_items_sort_by_day_then_projects_milestones_tasks(): void
    {
        $this->addProject(5, end: '2026-11-23 12:00:00', name: 'Launch');
        $this->addMember(7, 5);
        $this->addTicket(1, ['editorId' => '7', 'dateToFinish' => '2026-11-23 09:00:00']);
        $this->addTicket(2, ['type' => 'milestone', 'editorId' => '7', 'editTo' => '2026-11-23 12:00:00']);
        $this->addTicket(3, ['editorId' => '7', 'dateToFinish' => '2026-11-22 18:00:00']);

        $this->assertSame([['task', 3], ['project', 5], ['milestone', 2], ['task', 1]], array_map(fn ($item) => [$item['kind'], $item['id']], $this->build()['items']));
    }

    public function test_nothing_coming_up_is_empty(): void
    {
        $summary = $this->build();

        $this->assertTrue($summary['isEmpty']);
        $this->assertSame([], $summary['items']);
        $this->assertSame(0, $summary['moreCount']);
    }

    /**
     * @dataProvider presetCases
     */
    public function test_preset_rules(string $preset, int $daysUntil, ?int $daysSinceStart, bool $shows): void
    {
        $this->assertSame($shows, SummaryBuilder::presetShows($preset, $daysUntil, $daysSinceStart));
    }

    public static function presetCases(): array
    {
        return [
            'default, today' => ['default', 0, null, true],
            'default, tomorrow' => ['default', 1, null, true],
            'default, in 2 days' => ['default', 2, null, false],
            'less, today' => ['less', 0, null, true],
            'less, tomorrow' => ['less', 1, null, false],
            'more, 3 days, no start' => ['more', 3, null, true],
            'more, 4 days, no start' => ['more', 4, null, false],
            'more, 10 days, started' => ['more', 10, 2, true],
            'more, 10 days, starts later' => ['more', 10, -1, false],
            'more, tomorrow, starts later' => ['more', 1, -3, true],
            'off, today' => ['off', 0, null, false],
            'overdue' => ['default', -1, null, false],
        ];
    }
}

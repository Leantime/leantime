<?php

namespace Unit\app\Domain\Reminders;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\NotFoundException;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Domain\Reminders\Services\Reminders;
use Leantime\Domain\Reminders\Services\ReminderSettings;
use Leantime\Domain\Reminders\Services\SummaryBuilder;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Unit\TestCase;

/**
 * The reminders API acts for the signed-in person only, and only on to-dos they can see. Choices
 * are user settings: a choice for one to-do is stored only when it differs from "inherit".
 * Runs the real settings repository on SQLite.
 */
class RemindersServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;
    use ReminderDatabase;

    /** @var array<int, int> project ids the session user may view to-dos in */
    private array $viewable = [1];

    /** Whether the session user may edit company settings. */
    private bool $companyEditor = false;

    protected function setUp(): void
    {
        parent::setUp();

        session(['userdata' => ['id' => 7]]);
        $this->createReminderDatabase();
        $this->addUser(7);
        $this->viewable = [1];
        $this->companyEditor = false;
    }

    private function service(): Reminders
    {
        $config = $this->make(Environment::class);
        $config->defaultTimezone = 'UTC';

        return new Reminders(
            new ReminderSettings($this->settingRepository(), $config),
            $this->make(SummaryBuilder::class, ['build' => fn (int $userId, $now, string $timezone, string $preset) => ['for' => $userId, 'preset' => $preset]]),
            $this->make(TicketRepository::class, [
                'getTicket' => function ($id) {
                    if (! in_array((int) $id, [1, 2, 3], true)) {
                        return false;
                    }
                    $ticket = new TicketModel;
                    $ticket->id = (int) $id;
                    // to-do 1 is in project 1, to-do 2 in project 2; to-do 3 is in project 1 without a due date
                    $ticket->projectId = (int) $id === 3 ? 1 : (int) $id;
                    $ticket->dateToFinish = (int) $id === 3 ? '0000-00-00 00:00:00' : '2026-11-25 17:00:00';

                    return $ticket;
                },
            ]),
            $this->make(PermissionService::class, [
                'authorize' => function (string $permission, ?int $projectId = null, ?bool $forceGlobal = null) {
                    if ($permission === 'company.settings.edit' && $forceGlobal === true && $this->companyEditor) {
                        return;
                    }
                    if ($permission !== 'tickets.view' || ! in_array($projectId, $this->viewable, true)) {
                        throw new AuthorizationException;
                    }
                },
                'currentUserCan' => fn (string $permission, ?int $projectId = null, ?bool $forceGlobal = null) => $permission === 'company.settings.view' && $forceGlobal === true && $this->companyEditor,
            ]),
            $this->repository(),
        );
    }

    /** @return array<string, string> the session user's reminder keys in zp_settings */
    private function storedKeys(): array
    {
        return $this->db->table('zp_settings')->where('key', 'like', 'usersettings.7.reminders.%')->orderBy('key')->pluck('value', 'key')->all();
    }

    public function test_everything_needs_a_signed_in_person(): void
    {
        session(['userdata' => []]);

        $this->expectException(AuthorizationException::class);
        $this->service()->getSummary();
    }

    public function test_the_summary_is_always_the_session_user_s(): void
    {
        $this->assertSame(['for' => 7, 'preset' => 'default'], $this->service()->getSummary());
    }

    public function test_settings_are_validated_and_saved_as_user_settings(): void
    {
        $result = $this->service()->saveSettings(summary: false, summaryTime: '07:30', taskPreset: 'more');

        $this->assertSame([
            'usersettings.7.reminders.summary' => '0',
            'usersettings.7.reminders.summaryTime' => '07:30',
            'usersettings.7.reminders.taskPreset' => 'more',
        ], $this->storedKeys());
        $this->assertSame(['summary' => false, 'summaryTime' => '07:30', 'taskPreset' => 'more', 'timezone' => 'UTC', 'workspaceEnabled' => true], $result);

        foreach ([['summaryTime' => '7:30'], ['summaryTime' => '24:00'], ['taskPreset' => 'loud']] as $bad) {
            try {
                $this->service()->saveSettings(...$bad);
                $this->fail('accepted '.json_encode($bad));
            } catch (ValidationException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_a_task_choice_round_trips_through_user_settings_and_inherit_deletes_it(): void
    {
        $service = $this->service();
        $service->saveSettings(taskPreset: 'less');

        $this->assertSame(['preset' => 'less', 'chosen' => false], $this->presetOf($service->getTaskReminders(1)));
        $this->assertArrayNotHasKey('usersettings.7.reminders.task.1', $this->storedKeys(), 'nothing stored until a choice is made');

        $service->setTaskPreset(1, 'more');
        $this->assertSame('more', $this->storedKeys()['usersettings.7.reminders.task.1']);
        $this->assertSame(['preset' => 'more', 'chosen' => true], $this->presetOf($this->service()->getTaskReminders(1)));
        $this->assertSame([1 => 'more'], $this->repository()->taskChoices(7), 'the builder reads it with the prefix query');

        $service->setTaskPreset(1, 'inherit');
        $this->assertArrayNotHasKey('usersettings.7.reminders.task.1', $this->storedKeys(), 'inherit deletes the key');
        $this->assertSame(['preset' => 'less', 'chosen' => false], $this->presetOf($this->service()->getTaskReminders(1)));
        $this->assertSame([], $this->repository()->taskChoices(7));
    }

    public function test_task_methods_check_the_person_can_see_the_task(): void
    {
        $service = $this->service();

        foreach ([fn () => $service->getTaskReminders(2), fn () => $service->setTaskPreset(2, 'off')] as $call) {
            try {
                $call();
                $this->fail('a to-do in a project the person can\'t see');
            } catch (AuthorizationException) {
                $this->addToAssertionCount(1);
            }
        }
        $this->assertSame([], $this->storedKeys());

        $this->expectException(NotFoundException::class);
        $service->getTaskReminders(404);
    }

    public function test_an_unknown_preset_is_refused(): void
    {
        $this->expectException(ValidationException::class);
        $this->service()->setTaskPreset(1, 'loud');
    }

    public function test_the_picker_state_says_what_applies_what_it_follows_and_whether_it_can_work(): void
    {
        $this->assertSame([
            'preset' => 'default',
            'chosen' => false,
            'inherited' => 'default',
            'defaultPreset' => 'default',
            'hasDueDate' => true,
            'summary' => true,
            'workspaceEnabled' => true,
        ], $this->service()->getTaskReminders(1));

        $this->assertFalse($this->service()->getTaskReminders(3)['hasDueDate'], '0000-00-00 is no due date');

        $this->service()->saveSettings(summary: false);
        $this->assertFalse($this->service()->getTaskReminders(1)['summary']);
    }

    public function test_a_to_do_that_repeats_every_weekday_follows_less_by_default(): void
    {
        $this->db->table('zp_recurring_patterns')->insert(['entityId' => 1, 'module' => 'tickets', 'type' => 'daily', 'interval' => 1, 'enabled' => 1]);

        $state = $this->service()->getTaskReminders(1);

        $this->assertSame('less', $state['inherited']);
        $this->assertSame('less', $state['preset']);
    }

    public function test_picking_a_reminder_stores_a_choice_only_when_it_differs_from_what_the_to_do_follows(): void
    {
        $service = $this->service();

        $state = $service->chooseTaskReminder(1, 'more');
        $this->assertSame('more', $this->storedKeys()['usersettings.7.reminders.task.1']);
        $this->assertSame(['preset' => 'more', 'chosen' => true], $this->presetOf($state));

        $state = $service->chooseTaskReminder(1, 'default');
        $this->assertArrayNotHasKey('usersettings.7.reminders.task.1', $this->storedKeys(), 'picking the default removes the choice');
        $this->assertSame(['preset' => 'default', 'chosen' => false], $this->presetOf($state));
    }

    public function test_use_this_for_all_my_to_dos_changes_the_default_and_clears_this_to_do_s_choice(): void
    {
        $service = $this->service();
        $service->chooseTaskReminder(1, 'off');

        $state = $service->chooseTaskReminder(1, 'less', forAllMyTodos: true);

        $this->assertSame(['usersettings.7.reminders.taskPreset' => 'less'], $this->storedKeys());
        $this->assertSame(['preset' => 'less', 'chosen' => false], $this->presetOf($state));
        $this->assertSame('less', $state['defaultPreset']);
    }

    public function test_picking_checks_the_to_do_and_the_preset(): void
    {
        $service = $this->service();

        try {
            $service->chooseTaskReminder(2, 'off');
            $this->fail('a to-do in a project the person can\'t see');
        } catch (AuthorizationException) {
            $this->addToAssertionCount(1);
        }

        try {
            $service->chooseTaskReminder(1, 'inherit');
            $this->fail('the picker takes the four presets only');
        } catch (ValidationException) {
            $this->addToAssertionCount(1);
        }

        $this->assertSame([], $this->storedKeys());
    }

    public function test_only_company_settings_editors_can_turn_reminders_off_for_the_workspace(): void
    {
        try {
            $this->service()->setWorkspaceEnabled(false);
            $this->fail('someone without company settings rights turned reminders off');
        } catch (AuthorizationException) {
            $this->addToAssertionCount(1);
        }
        $this->assertTrue($this->service()->getSettings()['workspaceEnabled']);
        $this->assertFalse($this->service()->canManageWorkspace());

        $this->companyEditor = true;
        $this->assertTrue($this->service()->canManageWorkspace());

        $this->service()->setWorkspaceEnabled(false);
        $this->assertFalse($this->service()->getSettings()['workspaceEnabled']);
        $this->assertSame('0', $this->db->table('zp_settings')->where('key', 'companysettings.reminders.enabled')->value('value'));

        $this->service()->setWorkspaceEnabled(true);
        $this->assertTrue($this->service()->getSettings()['workspaceEnabled']);
        $this->assertNull($this->db->table('zp_settings')->where('key', 'companysettings.reminders.enabled')->value('value'), 'on just removes the switch');
    }

    public function test_the_scheduler_warning_follows_the_last_run(): void
    {
        $now = \Carbon\CarbonImmutable::parse('2026-11-22 12:00:00', 'UTC');

        $this->assertTrue($this->service()->schedulerLooksStopped($now), 'never ran');

        $this->settingRepository()->saveSetting('reminders.lastRun', '2026-11-22 11:05:00');
        $this->assertFalse($this->service()->schedulerLooksStopped($now), '55 minutes ago');

        $this->settingRepository()->saveSetting('reminders.lastRun', '2026-11-22 10:59:00');
        $this->assertTrue($this->service()->schedulerLooksStopped($now), '61 minutes ago');

        $this->settingRepository()->saveSetting('reminders.lastRun', 'garbage');
        $this->assertTrue($this->service()->schedulerLooksStopped($now));
    }

    /** @return array{preset: string, chosen: bool} */
    private function presetOf(array $state): array
    {
        return ['preset' => $state['preset'], 'chosen' => $state['chosen']];
    }
}

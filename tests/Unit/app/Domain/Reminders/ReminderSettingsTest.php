<?php

namespace Unit\app\Domain\Reminders;

use Leantime\Core\Configuration\Environment;
use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Reminders\Repositories\Reminders as ReminderRepository;
use Leantime\Domain\Reminders\Services\ReminderSettings;
use Leantime\Domain\Reminders\Services\SummaryBuilder;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Unit\TestCase;

/**
 * Reminder settings default to on at 08:00 in the person's timezone, ignore stored values that
 * aren't valid, and the workspace switch can only turn reminders off.
 */
class ReminderSettingsTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function settings(array $stored): ReminderSettings
    {
        $config = $this->make(Environment::class);
        $config->defaultTimezone = 'Europe/Berlin';

        return new ReminderSettings(
            $this->make(SettingRepository::class, [
                'getSettingsForKeys' => fn (array $keys) => array_intersect_key($stored, array_flip($keys)),
                'getSetting' => fn (string $key) => $stored[$key] ?? false,
            ]),
            $config,
        );
    }

    public function test_the_time_format_is_the_person_s_own_else_the_language_default(): void
    {
        app()->instance(\Leantime\Core\Language::class, $this->make(\Leantime\Core\Language::class, [
            '__' => fn (string $key) => $key === 'language.timeformat' ? 'h:i A' : $key,
        ]));

        $this->assertSame('H:i', $this->settings(['usersettings.7.time_format' => 'H:i'])->timeFormat(7));
        $this->assertSame('h:i A', $this->settings(['usersettings.8.time_format' => 'H:i'])->timeFormat(7), 'not someone else\'s');
        $this->assertSame('h:i A', $this->settings(['usersettings.7.time_format' => ''])->timeFormat(7));

        app()->forgetInstance(\Leantime\Core\Language::class);
    }

    public function test_defaults(): void
    {
        $this->assertSame(
            ['summary' => true, 'summaryTime' => '08:00', 'taskPreset' => 'default', 'timezone' => 'Europe/Berlin'],
            $this->settings([])->forUser(7)
        );
        $this->assertTrue($this->settings([])->workspaceEnabled());
    }

    public function test_stored_values_and_invalid_ones(): void
    {
        $settings = $this->settings([
            'usersettings.7.reminders.summary' => '0',
            'usersettings.7.reminders.summaryTime' => '06:45',
            'usersettings.7.reminders.taskPreset' => 'more',
            'usersettings.7.timezone' => 'America/Chicago',
            'usersettings.8.reminders.summaryTime' => '6am',
            'usersettings.8.reminders.taskPreset' => 'loud',
            'usersettings.8.timezone' => 'Mars/Olympus',
        ]);

        $both = $settings->forUsers([7, 8]);
        $this->assertSame(['summary' => false, 'summaryTime' => '06:45', 'taskPreset' => 'more', 'timezone' => 'America/Chicago'], $both[7]);
        $this->assertSame(['summary' => true, 'summaryTime' => '08:00', 'taskPreset' => 'default', 'timezone' => 'Europe/Berlin'], $both[8]);
    }

    public function test_the_workspace_switch_only_turns_reminders_off(): void
    {
        $this->assertFalse($this->settings(['companysettings.reminders.enabled' => '0'])->workspaceEnabled());
        $this->assertTrue($this->settings(['companysettings.reminders.enabled' => '1'])->workspaceEnabled());
    }

    /**
     * The real role check (not the test stand-in the builder tests use): company role manager or
     * above, or project role manager.
     */
    public function test_who_counts_as_a_project_manager(): void
    {
        $builder = new SummaryBuilder(
            $this->makeEmpty(ReminderRepository::class),
            $this->makeEmpty(TicketRepository::class),
            $this->makeEmpty(ProjectRepository::class),
            $this->makeEmpty(TrustedAppUrl::class),
        );
        $isManager = (new \ReflectionMethod(SummaryBuilder::class, 'isManager'))->getClosure($builder);

        $this->assertTrue($isManager('30', ''), 'company manager');
        $this->assertTrue($isManager('50', ''), 'owner');
        $this->assertFalse($isManager('20', ''), 'editor');
        $this->assertTrue($isManager('20', '30'), 'editor who manages this project');
        $this->assertTrue($isManager('20', 'manager'), 'project role stored by name');
        $this->assertFalse($isManager('20', '20'), 'editor in this project too');
        $this->assertFalse($isManager('10', '50'), 'owner is not a project role');
    }
}

<?php

namespace Unit\app\Domain\Reminders\Hxcontrollers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Leantime\Domain\Reminders\Hxcontrollers\SummarySettings;
use Leantime\Domain\Reminders\Hxcontrollers\WorkspaceReminders;
use Leantime\Domain\Reminders\Services\Reminders;
use Leantime\Domain\Setting\Permissions\SettingPermissions;
use Symfony\Component\HttpFoundation\Response;
use Unit\TestCase;

/**
 * "Morning summary" in your own settings and "Reminders" in company settings. Both save only on
 * POST (the Frontcontroller dispatches GET to custom actions too, and the origin check skips GET).
 * The workspace switch needs company settings rights; the scheduler warning only shows to people
 * who have them.
 */
class ReminderSettingsControllersTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<string, mixed> what the controller handed the template */
    private array $assigned = [];

    /** @var array<int, array> calls that changed something */
    private array $changes = [];

    private bool $manager = false;

    private bool $stopped = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assigned = [];
        $this->changes = [];
        $this->manager = false;
        $this->stopped = true;
    }

    private function reminders(): Reminders
    {
        return $this->make(Reminders::class, [
            'getSettings' => fn () => ['summary' => true, 'summaryTime' => '08:00', 'taskPreset' => 'default', 'timezone' => 'America/New_York', 'workspaceEnabled' => true],
            'saveSettings' => function (?bool $summary = null, ?string $summaryTime = null, ?string $taskPreset = null) {
                if ($summaryTime !== null && preg_match('/^\d\d:\d\d$/', $summaryTime) !== 1) {
                    throw new ValidationException(['summaryTime' => ['Use a time like 08:00.']]);
                }
                $this->changes[] = ['saveSettings', $summary, $summaryTime, $taskPreset];

                return [];
            },
            'setWorkspaceEnabled' => function (bool $enabled) {
                $this->changes[] = ['setWorkspaceEnabled', $enabled];

                return true;
            },
            'canManageWorkspace' => fn () => $this->manager,
            'schedulerLooksStopped' => fn () => $this->stopped,
        ]);
    }

    /**
     * @template T
     *
     * @param  class-string<T>  $class
     * @return T
     */
    private function controller(string $class, string $method, array $parameters = []): object
    {
        $request = IncomingRequest::create('/hx/reminders/x/save', $method, $parameters);
        $tpl = $this->make(Template::class, [
            'emptyResponse' => fn ($code = 200) => new Response('', $code),
            'assign' => function ($name, $value) {
                $this->assigned[$name] = $value;
            },
        ]);
        $language = $this->make(Language::class, ['__' => fn (string $key) => $key]);

        $controller = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
        foreach (['incomingRequest' => $request, 'tpl' => $tpl, 'language' => $language] as $property => $value) {
            $reflection = new \ReflectionProperty($class, $property);
            $reflection->setValue($controller, $value);
        }
        $controller->init($this->reminders());

        return $controller;
    }

    public function test_saving_your_summary_settings_needs_post(): void
    {
        $response = $this->controller(SummarySettings::class, 'GET', ['summary' => '0'])->save();

        $this->assertSame(405, $response->getStatusCode());
        $this->assertSame([], $this->changes);
    }

    public function test_saving_your_summary_settings_passes_the_form_values(): void
    {
        $fragment = $this->controller(SummarySettings::class, 'POST', ['summary' => '0', 'summaryTime' => '07:30', 'taskPreset' => 'less'])->save();

        $this->assertSame('form', $fragment);
        $this->assertSame([['saveSettings', false, '07:30', 'less']], $this->changes);
        $this->assertSame('reminders.settings_saved', $this->assigned['savedMessage']);
        $this->assertSame('America/New_York', $this->assigned['settings']['timezone'], 'the form shows the timezone');
    }

    public function test_a_bad_time_says_so_and_saves_nothing(): void
    {
        $this->controller(SummarySettings::class, 'POST', ['summary' => '1', 'summaryTime' => '7pm', 'taskPreset' => 'default'])->save();

        $this->assertSame([], $this->changes);
        $this->assertSame('reminders.settings_not_saved', $this->assigned['errorMessage']);
        $this->assertArrayNotHasKey('savedMessage', $this->assigned);
    }

    public function test_the_scheduler_warning_shows_only_to_company_settings_people_and_only_when_stopped(): void
    {
        $this->controller(SummarySettings::class, 'GET')->get();
        $this->assertFalse($this->assigned['showSchedulerWarning'], 'not for everyone');

        $this->manager = true;
        $this->controller(SummarySettings::class, 'GET')->get();
        $this->assertTrue($this->assigned['showSchedulerWarning']);

        $this->stopped = false;
        $this->controller(SummarySettings::class, 'GET')->get();
        $this->assertFalse($this->assigned['showSchedulerWarning'], 'not when the scheduler ran in the last hour');
    }

    public function test_the_workspace_switch_needs_company_settings_rights_and_post(): void
    {
        $this->assertPermission(WorkspaceReminders::class, 'get', SettingPermissions::COMPANY_VIEW);
        $this->assertPermission(WorkspaceReminders::class, 'save', SettingPermissions::COMPANY_EDIT);

        $this->assertSame(405, $this->controller(WorkspaceReminders::class, 'GET', ['enabled' => '0'])->save()->getStatusCode());
        $this->assertSame([], $this->changes);

        $this->controller(WorkspaceReminders::class, 'POST', ['enabled' => '0'])->save();
        $this->controller(WorkspaceReminders::class, 'POST', ['enabled' => '1'])->save();
        $this->assertSame([['setWorkspaceEnabled', false], ['setWorkspaceEnabled', true]], $this->changes);
    }

    public function test_company_settings_show_the_warning_when_the_scheduler_stopped(): void
    {
        $this->controller(WorkspaceReminders::class, 'GET')->get();
        $this->assertTrue($this->assigned['showSchedulerWarning']);

        $this->stopped = false;
        $this->controller(WorkspaceReminders::class, 'GET')->get();
        $this->assertFalse($this->assigned['showSchedulerWarning']);
    }

    public function test_the_time_field_takes_any_minute(): void
    {
        $plugins = $this->createMock(\Leantime\Core\Plugins\Plugins::class);
        $plugins->method('getEnabledPluginPaths')->willReturn([]);
        app()->instance(\Leantime\Core\Plugins\Plugins::class, $plugins);

        $html = view('reminders::partials.summarySettings', [
            'settings' => ['summary' => true, 'summaryTime' => '08:02', 'taskPreset' => 'default', 'timezone' => 'UTC', 'workspaceEnabled' => true],
            'presets' => ['default', 'more', 'less', 'off'],
            'showSchedulerWarning' => false,
        ])->fragment('form');

        $this->assertMatchesRegularExpression('/<input type="time"[^>]*value="08:02"[^>]*step="60"/', $html, 'the server takes any HH:MM, so the field does too');
    }

    public function test_viewers_get_the_open_to_do_s_menu_for_their_reminders_and_nothing_more(): void
    {
        $template = (string) file_get_contents(APP_ROOT.'/app/Domain/Tickets/Templates/showTicketModal.blade.php');
        $guard = fn (string $check, string $then) => '~'.preg_quote($check, '~').'\s*'.preg_quote($then, '~').'~';

        $this->assertStringContainsString("@canany(['tickets.view', 'tickets.edit', 'tickets.delete', 'timesheets.create'], \$ticket->projectId)", $template, 'tickets.view opens the menu');
        $this->assertMatchesRegularExpression($guard("@canany(['tickets.edit', 'tickets.delete'], \$ticket->projectId)", '<li class="nav-header">'), $template, 'the To-do heading needs edit or delete');
        $this->assertMatchesRegularExpression($guard("@can('tickets.edit', \$ticket->projectId)", '<li><a href="#/tickets/moveTicket/'), $template, 'Move keeps its own check');
        $this->assertMatchesRegularExpression($guard("@can('tickets.delete', \$ticket->projectId)", '<li><a href="#/tickets/delTicket/'), $template, 'Delete keeps its own check');
        $this->assertMatchesRegularExpression($guard("@can('timesheets.create')", "@module('timesheets'"), $template, 'time tracking keeps its own check');
        $this->assertMatchesRegularExpression($guard("@can('tickets.view', \$ticket->projectId)", "@include('reminders::partials.taskReminderMenuMount'"), $template);
    }

    private function assertPermission(string $class, string $method, string $permission): void
    {
        $attributes = (new \ReflectionMethod($class, $method))->getAttributes(RequiresPermission::class);
        $this->assertCount(1, $attributes, $class.'::'.$method.' declares its permission');
        $declared = $attributes[0]->newInstance();
        $this->assertSame($permission, $declared->permission);
        $this->assertTrue($declared->global);
    }
}

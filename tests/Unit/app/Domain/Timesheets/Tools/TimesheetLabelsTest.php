<?php

namespace Unit\app\Domain\Timesheets\Tools;

use Carbon\CarbonImmutable;
use Laravel\Mcp\Request;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Language;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Timesheets\Services\Timesheets;
use Leantime\Domain\Timesheets\Tools\GetProjectTimesheetsTool;
use Leantime\Domain\Timesheets\Tools\GetTimesheetSummaryTool;
use Unit\TestCase;

class TimesheetLabelsTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    public function test_both_timesheet_tools_translate_hour_type_labels(): void
    {
        session(['userdata.id' => 1, 'userdata.role' => 'admin', 'usersettings.timezone' => 'UTC', 'usersettings.language' => 'en-US', 'usersettings.date_format' => 'Y-m-d', 'usersettings.time_format' => 'H:i']);
        app()->instance(Environment::class, $this->make(Environment::class, ['defaultTimezone' => 'UTC', 'language' => 'en-US']));
        app()->instance(Language::class, $this->make(Language::class, ['__' => fn ($key) => $key === 'label.general_billable' ? 'General, billable' : $key]));
        CarbonImmutable::mixin(new CarbonMacros('UTC', 'en-US', 'Y-m-d', 'H:i'));

        $timesheets = $this->make(Timesheets::class, [
            'getAll' => fn () => [['hours' => 2, 'firstname' => 'A', 'lastname' => 'B', 'userId' => 1, 'ticketId' => 7, 'headline' => 'Task', 'kind' => 'GENERAL_BILLABLE']],
            'getLoggableHourTypes' => fn () => ['GENERAL_BILLABLE' => 'label.general_billable'],
        ]);
        $projects = $this->make(Projects::class, ['getProject' => fn () => ['name' => 'Project']]);
        $permissions = $this->make(PermissionService::class, ['currentUserCan' => fn () => true]);
        $dates = ['dateFrom' => '2026-11-01', 'dateTo' => '2026-11-30'];

        $project = (new GetProjectTimesheetsTool($timesheets, $projects, $permissions))->handle(new Request($dates + ['projectId' => 1]));
        $summary = (new GetTimesheetSummaryTool($timesheets))->handle(new Request($dates + ['groupBy' => 'kind']));

        foreach ([$project, $summary] as $response) {
            $this->assertStringContainsString('General, billable', (string) $response->content());
            $this->assertStringNotContainsString('label.general_billable', (string) $response->content());
        }
    }
}

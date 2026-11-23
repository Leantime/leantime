<?php

namespace Unit\app\Domain\Reports\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Configuration\AppSettings as AppSettingCore;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Reports\Repositories\Reports as ReportRepository;
use Leantime\Domain\Reports\Services\Reports;
use Leantime\Domain\Setting\Services\Setting as SettingsService;
use Leantime\Domain\Sprints\Repositories\Sprints as SprintRepository;
use Leantime\Domain\Sprints\Services\Sprints as SprintService;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Unit\TestCase;

/**
 * cronDailyIngestion() walks every project in one loop. A project whose report cannot be
 * computed or stored (for example PostgreSQL rejecting a fractional story-point total) used to
 * abort the loop, so every project after it silently lost its daily zp_stats row. The failure
 * must stay isolated to that project.
 */
class DailyIngestionIsolationTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    protected function setUp(): void
    {
        parent::setUp();

        // Session values and stubs required by dtHelper().
        session(['usersettings.timezone' => 'UTC']);
        session(['usersettings.language' => 'en-US']);
        session(['usersettings.date_format' => 'Y-m-d']);
        session(['usersettings.time_format' => 'H:i']);

        app()->instance(EnvironmentCore::class, $this->make(EnvironmentCore::class, [
            'defaultTimezone' => 'UTC',
            'language' => 'en-US',
        ]));

        $language = $this->createMock(LanguageCore::class);
        $language->method('__')->willReturnCallback(fn (string $index): string => match ($index) {
            'language.dateformat' => 'Y-m-d',
            'language.timeformat' => 'H:i',
            default => $index,
        });
        app()->instance(LanguageCore::class, $language);

        CarbonImmutable::mixin(new CarbonMacros('UTC', 'en-US', 'Y-m-d', 'H:i'));
    }

    /**
     * @param  array<int, int>  $checked  Receives the project ids whose report was looked up.
     * @param  array<int, int>  $computed  Receives the project ids whose report was computed.
     */
    private function service(array &$checked, array &$computed): Reports
    {
        return new Reports(
            $this->make(AppSettingCore::class),
            $this->make(EnvironmentCore::class),
            $this->make(ProjectRepository::class, [
                'getAll' => fn (): array => [['id' => 1], ['id' => 2], ['id' => 3]],
            ]),
            $this->make(SprintRepository::class, [
                'getCurrentSprint' => fn (): false => false,
            ]),
            $this->make(ReportRepository::class, [
                'checkLastReportEntries' => function (int $projectId) use (&$checked): array {
                    $checked[] = $projectId;

                    if ($projectId === 1) {
                        throw new \RuntimeException('PostgreSQL rejected the row');
                    }

                    return [];
                },
                'runTicketReport' => function (int $projectId) use (&$computed): false {
                    $computed[] = $projectId;

                    return false;
                },
            ]),
            $this->make(SettingsService::class),
            $this->make(TicketRepository::class),
            $this->make(SprintService::class),
        );
    }

    public function test_a_failing_project_does_not_stop_the_projects_after_it(): void
    {
        $checked = [];
        $computed = [];

        $this->service($checked, $computed)->cronDailyIngestion();

        $this->assertSame([1, 2, 3], $checked, 'every project should still be attempted');
        $this->assertSame([2, 3], $computed, 'the projects after the failure should still be ingested');
    }
}

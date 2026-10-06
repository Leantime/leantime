<?php

namespace Unit\app\Domain\Setting\Services;

use Leantime\Core\Files\Contracts\FileManagerInterface;
use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
use Leantime\Domain\Reports\Services\Reports as ReportService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Unit\TestCase;

/**
 * Unit tests for the Setting service helpers extracted during the
 * thin-controller refactor (getProjectLabel).
 */
class SettingServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Builds a real Setting service, allowing each dependency to be
     * overridden with a stub so we can observe the label resolution.
     */
    private function makeService(
        ?SettingRepository $settingsRepo = null,
        ?TicketRepository $ticketsRepo = null,
        ?IdeaRepository $ideaRepo = null,
    ): SettingService {
        return new SettingService(
            $settingsRepo ?? $this->make(SettingRepository::class),
            $this->makeEmpty(FileManagerInterface::class),
            $ticketsRepo ?? $this->make(TicketRepository::class),
            $ideaRepo ?? $this->make(IdeaRepository::class),
        );
    }

    public function test_get_project_label_reads_ticket_state_label_name(): void
    {
        $ticketsRepo = $this->make(TicketRepository::class, [
            'getStateLabels' => fn () => [
                3 => ['name' => 'In Progress'],
            ],
        ]);

        $label = $this->makeService(ticketsRepo: $ticketsRepo)->getProjectLabel('ticketlabels', 3, 1);

        $this->assertSame('In Progress', $label);
    }

    public function test_get_project_label_returns_empty_for_missing_ticket_label(): void
    {
        $ticketsRepo = $this->make(TicketRepository::class, [
            'getStateLabels' => fn () => [
                3 => ['name' => 'In Progress'],
            ],
        ]);

        $label = $this->makeService(ticketsRepo: $ticketsRepo)->getProjectLabel('ticketlabels', 99, 1);

        $this->assertSame('', $label);
    }

    public function test_get_project_label_reads_idea_label_name(): void
    {
        $ideaRepo = $this->make(IdeaRepository::class, [
            'getCanvasLabels' => fn () => [
                1 => ['name' => 'Backlog', 'class' => 'label-default'],
            ],
        ]);

        $label = $this->makeService(ideaRepo: $ideaRepo)->getProjectLabel('idealabels', 1, 1);

        $this->assertSame('Backlog', $label);
    }

    public function test_get_project_label_returns_empty_for_unknown_module(): void
    {
        $label = $this->makeService()->getProjectLabel('doesnotexist', 1, 1);

        $this->assertSame('', $label);
    }

    /**
     * Runs saveCompanySettings() with only the telemetry toggle (no other settings), with the
     * stored opt-out flag set to $storedOptOut, and records which ReportService calls it made.
     *
     * @param  array<string, mixed>  $params
     * @return array{saved: bool, calls: array<int, string>}
     */
    private function saveTelemetryToggle(array $params, string|false $storedOptOut): array
    {
        $calls = [];
        $reports = $this->make(ReportService::class, [
            'optOutTelemetry' => function () use (&$calls) {
                $calls[] = 'optOut';
            },
            'optInTelemetry' => function () use (&$calls) {
                $calls[] = 'optIn';
            },
        ]);
        app()->instance(ReportService::class, $reports);

        $settingsRepo = $this->make(SettingRepository::class, [
            'getSetting' => fn ($key) => $key === 'companysettings.telemetry.optOut' ? $storedOptOut : false,
        ]);

        try {
            $saved = $this->makeService(settingsRepo: $settingsRepo)->saveCompanySettings($params);
        } finally {
            app()->forgetInstance(ReportService::class);
        }

        return ['saved' => $saved, 'calls' => $calls];
    }

    public function test_unchecking_the_toggle_opts_out_once(): void
    {
        $result = $this->saveTelemetryToggle(['telemetryActive' => false], false);

        $this->assertSame(['optOut'], $result['calls']);
        $this->assertTrue($result['saved'], 'an opt-out counts as a save even without other settings');
    }

    public function test_unchecked_toggle_when_already_opted_out_sends_no_second_notice(): void
    {
        $result = $this->saveTelemetryToggle(['telemetryActive' => false], 'true');

        $this->assertSame([], $result['calls']);
    }

    public function test_checking_the_toggle_after_an_opt_out_opts_back_in(): void
    {
        $result = $this->saveTelemetryToggle(['telemetryActive' => true], 'true');

        $this->assertSame(['optIn'], $result['calls']);
        $this->assertTrue($result['saved']);
    }

    public function test_checked_toggle_when_already_opted_in_changes_nothing(): void
    {
        $result = $this->saveTelemetryToggle(['telemetryActive' => true], 'false');

        $this->assertSame([], $result['calls']);
    }

    public function test_callers_without_the_toggle_leave_telemetry_untouched(): void
    {
        $result = $this->saveTelemetryToggle(['messageFrequency' => 300], false);

        $this->assertSame([], $result['calls']);
        $this->assertFalse($result['saved']);
    }
}

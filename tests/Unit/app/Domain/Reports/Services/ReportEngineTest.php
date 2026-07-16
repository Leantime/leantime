<?php

namespace Tests\Unit\App\Domain\Reports\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Language;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Core\Support\DateTimeHelper;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas as GoalcanvasService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Reports\Models\ReportPeriod;
use Leantime\Domain\Reports\Repositories\ReportEngine as ReportEngineRepository;
use Leantime\Domain\Reports\Services\ReportEngine;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Unit\TestCase;

class ReportEngineTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private ReportEngine $service;

    private ReportEngineRepository&MockObject $repository;

    private TicketRepository&MockObject $ticketRepository;

    private ProjectService&MockObject $projectService;

    private GoalcanvasService&MockObject $goalService;

    protected function setUp(): void
    {
        parent::setUp();

        $environmentMock = $this->make(Environment::class, [
            'defaultTimezone' => 'UTC',
            'language' => 'en-US',
        ]);
        app()->instance(Environment::class, $environmentMock);

        $languageMock = $this->createMock(Language::class);
        $languageMock->method('__')->willReturnCallback(function ($index) {
            $map = [
                'language.dateformat' => 'm/d/Y',
                'language.timeformat' => 'h:i A',
            ];

            return $map[$index] ?? null;
        });
        app()->instance(Language::class, $languageMock);

        CarbonImmutable::mixin(new CarbonMacros('UTC', 'en_US', 'm/d/Y', 'h:i A'));
        app()->instance(DateTimeHelper::class, new DateTimeHelper);

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-08 12:00:00', 'UTC'));

        $this->repository = $this->createMock(ReportEngineRepository::class);
        $this->ticketRepository = $this->createMock(TicketRepository::class);
        $this->projectService = $this->createMock(ProjectService::class);
        $this->goalService = $this->createMock(GoalcanvasService::class);

        $this->service = new ReportEngine(
            $this->repository,
            $this->ticketRepository,
            $this->projectService,
            $this->goalService,
        );

        $permissionService = $this->createMock(PermissionService::class);
        $permissionService->method('currentUserCan')->willReturn(true);
        $this->service->setPermissionService($permissionService);

        // Status vocabulary for project 10: 1 = NEW, 2 = INPROGRESS, 3 = DONE.
        $this->ticketRepository->method('getStateLabels')->willReturn([
            1 => ['statusType' => 'NEW', 'name' => 'New'],
            2 => ['statusType' => 'INPROGRESS', 'name' => 'In Progress'],
            3 => ['statusType' => 'DONE', 'name' => 'Done'],
        ]);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        app()->forgetInstance(DateTimeHelper::class);

        parent::tearDown();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function milestone(int $id, array $overrides = []): object
    {
        return (object) array_merge([
            'id' => $id,
            'headline' => 'Milestone '.$id,
            'description' => '',
            'outcomeImpact' => null,
            'date' => '2026-01-01 00:00:00',
            'projectId' => 10,
            'status' => 1,
            'editFrom' => null,
            'editTo' => null,
            'modified' => null,
            'projectName' => 'Project 10',
            'type' => 'milestone',
            'tags' => 'var(--grey)',
        ], $overrides);
    }

    private function lastQuarter(): ReportPeriod
    {
        // With test-now 2026-07-08 UTC this is Apr 1 – Jun 30 2026 (UTC).
        return ReportPeriod::lastQuarter();
    }

    public function test_milestones_bucket_into_completed_overdue_in_progress_and_upcoming(): void
    {
        $this->repository->method('getMilestonesForProjects')->willReturn([
            // Done, history transition inside the period -> completed.
            $this->milestone(1, ['status' => 3]),
            // Done, history transition long before the period -> allDone only.
            $this->milestone(2, ['status' => 3]),
            // Open with a past due date -> overdue.
            $this->milestone(3, ['editFrom' => '2026-05-01 00:00:00', 'editTo' => '2026-06-01 00:00:00']),
            // Open, starting two months after the period -> upcoming (Q3 2026).
            $this->milestone(4, ['editFrom' => '2026-08-15 00:00:00', 'editTo' => '2026-09-15 00:00:00']),
            // Open and completely unscheduled -> in progress.
            $this->milestone(5),
            // Open, starting after the two-quarter horizon -> dropped from upcoming.
            $this->milestone(6, ['editFrom' => '2027-06-01 00:00:00', 'editTo' => '2027-07-01 00:00:00']),
        ]);
        $this->repository->method('getStatusHistoryForTickets')->willReturn([
            (object) ['ticketId' => 1, 'changeValue' => '2', 'dateModified' => '2026-05-01 09:00:00'],
            (object) ['ticketId' => 1, 'changeValue' => '3', 'dateModified' => '2026-05-10 09:00:00'],
            (object) ['ticketId' => 2, 'changeValue' => '3', 'dateModified' => '2026-01-15 09:00:00'],
        ]);
        $this->repository->method('getTasksForMilestones')->willReturn([]);
        $this->repository->method('getDueDateChangesForTickets')->willReturn([]);

        $report = $this->service->getMilestoneReportForProjects([10], $this->lastQuarter());

        $this->assertSame([1], array_map(fn ($m) => $m->id, $report['completed']));
        $this->assertSame('2026-05-10 09:00:00', $report['completed'][0]->completedOn->format('Y-m-d H:i:s'));
        $this->assertSame([3], array_map(fn ($m) => $m->id, $report['overdue']));
        $this->assertSame([5], array_map(fn ($m) => $m->id, $report['inProgress']));
        $this->assertSame([4], array_map(fn ($m) => $m->id, $report['upcoming']));
        $this->assertArrayHasKey('Q3 2026', $report['upcomingByQuarter']);
        $this->assertCount(2, $report['allDone']);
    }

    public function test_completion_date_falls_back_to_due_date_then_modified_without_history(): void
    {
        $this->repository->method('getMilestonesForProjects')->willReturn([
            // No history, has a due date inside the period.
            $this->milestone(1, ['status' => 3, 'editTo' => '2026-05-20 00:00:00']),
            // No history, no due date, modified inside the period.
            $this->milestone(2, ['status' => 3, 'modified' => '2026-06-15 08:00:00']),
            // History exists but never transitions into DONE -> falls back to modified.
            $this->milestone(3, ['status' => 3, 'modified' => '2026-06-20 08:00:00']),
        ]);
        $this->repository->method('getStatusHistoryForTickets')->willReturn([
            (object) ['ticketId' => 3, 'changeValue' => '2', 'dateModified' => '2026-06-01 09:00:00'],
        ]);
        $this->repository->method('getTasksForMilestones')->willReturn([]);
        $this->repository->method('getDueDateChangesForTickets')->willReturn([]);

        $report = $this->service->getMilestoneReportForProjects([10], $this->lastQuarter());

        $completedOn = [];
        foreach ($report['completed'] as $milestone) {
            $completedOn[$milestone->id] = $milestone->completedOn->format('Y-m-d H:i:s');
        }

        $this->assertSame('2026-05-20 00:00:00', $completedOn[1]);
        $this->assertSame('2026-06-15 08:00:00', $completedOn[2]);
        $this->assertSame('2026-06-20 08:00:00', $completedOn[3]);
    }

    public function test_milestone_progress_uses_weighted_task_scores(): void
    {
        $this->repository->method('getMilestonesForProjects')->willReturn([
            $this->milestone(1, ['editFrom' => '2026-06-01 00:00:00', 'editTo' => '2026-09-01 00:00:00']),
        ]);
        $this->repository->method('getStatusHistoryForTickets')->willReturn([]);
        $this->repository->method('getDueDateChangesForTickets')->willReturn([]);
        $this->repository->method('getTasksForMilestones')->willReturn([
            // Done: 5 points × priority-1 factor 2 = 10. Open: 5 × factor 1 (priority 5) = 5.
            (object) ['id' => 100, 'headline' => 'Done task', 'status' => 3, 'projectId' => 10, 'milestoneid' => 1, 'storypoints' => 5, 'priority' => 1, 'editTo' => null, 'dateToFinish' => null],
            (object) ['id' => 101, 'headline' => 'Open task', 'status' => 1, 'projectId' => 10, 'milestoneid' => 1, 'storypoints' => 5, 'priority' => 5, 'editTo' => null, 'dateToFinish' => null],
        ]);

        $report = $this->service->getMilestoneReportForProjects([10], $this->lastQuarter());

        $milestone = $report['inProgress'][0];
        $this->assertEqualsWithDelta(66.67, $milestone->percentDone, 0.01);
        $this->assertSame(['done' => 1, 'total' => 2], $milestone->taskStats);
        // Key tasks list leads with completed work.
        $this->assertSame(100, $milestone->keyTasks[0]->id);
        $this->assertTrue($milestone->keyTasks[0]->isDone);
    }

    public function test_slippage_reports_milestones_pushed_out_and_added_mid_period(): void
    {
        $this->repository->method('getMilestonesForProjects')->willReturn([
            // Open, now due after the period, with an in-period due-date change -> pushed out.
            $this->milestone(1, ['editFrom' => '2026-04-10 00:00:00', 'editTo' => '2026-09-15 00:00:00']),
            // Created mid-period -> added.
            $this->milestone(2, ['date' => '2026-05-05 00:00:00', 'editFrom' => '2026-05-05 00:00:00', 'editTo' => '2026-12-01 00:00:00']),
        ]);
        $this->repository->method('getStatusHistoryForTickets')->willReturn([]);
        $this->repository->method('getTasksForMilestones')->willReturn([]);
        $this->repository->method('getDueDateChangesForTickets')->willReturn([
            (object) ['ticketId' => 1, 'changeValue' => '2026-09-15 00:00:00', 'dateModified' => '2026-06-10 09:00:00'],
            (object) ['ticketId' => 1, 'changeValue' => '2026-09-15 00:00:00', 'dateModified' => '2026-06-20 09:00:00'],
        ]);

        $report = $this->service->getMilestoneReportForProjects([10], $this->lastQuarter());

        $this->assertCount(1, $report['slippage']['pushedOut']);
        $this->assertSame(1, $report['slippage']['pushedOut'][0]->id);
        $this->assertSame(2, $report['slippage']['pushedOut'][0]->dueDateMoves);
        $this->assertSame([2], array_map(fn ($m) => $m->id, $report['slippage']['addedMidPeriod']));
    }

    public function test_goal_report_resolves_rollups_and_progress(): void
    {
        $this->repository->method('getGoalsForProjects')->willReturn([
            (object) ['id' => 1, 'title' => 'Graduates', 'description' => '', 'status' => 'status_ontrack', 'metricType' => 'count', 'startValue' => 0.0, 'currentValue' => 42.0, 'endValue' => 60.0, 'setting' => '', 'milestoneId' => '', 'kpi' => '', 'startDate' => null, 'endDate' => null, 'canvasId' => 5, 'projectId' => 10, 'boardTitle' => 'Goals', 'milestoneHeadline' => null],
            (object) ['id' => 2, 'title' => 'Rollup KPI', 'description' => '', 'status' => 'status_atrisk', 'metricType' => 'count', 'startValue' => 0.0, 'currentValue' => 0.0, 'endValue' => 100.0, 'setting' => 'linkAndReport', 'milestoneId' => '', 'kpi' => '', 'startDate' => null, 'endDate' => null, 'canvasId' => 5, 'projectId' => 10, 'boardTitle' => 'Goals', 'milestoneHeadline' => null],
        ]);
        $this->goalService->method('getChildGoalsForReporting')->with(2)->willReturn(25.0);

        $report = $this->service->getGoalReportForProjects([10]);

        $this->assertEqualsWithDelta(70.0, $report['goals'][0]->goalProgress, 0.01);
        $this->assertEqualsWithDelta(25.0, $report['goals'][1]->currentValue, 0.01);
        $this->assertEqualsWithDelta(25.0, $report['goals'][1]->goalProgress, 0.01);
        $this->assertSame(['ontrack' => 1, 'atrisk' => 1, 'miss' => 0], $report['counts']);
    }

    public function test_status_updates_group_by_project_and_respect_limit(): void
    {
        $this->repository->method('getStatusUpdatesForProjects')->willReturn([
            (object) ['id' => 1, 'projectId' => 10, 'text' => 'newest', 'date' => '2026-06-20 10:00:00', 'status' => 'green', 'authorFirstname' => 'A', 'authorLastname' => 'B', 'authorProfileId' => null],
            (object) ['id' => 2, 'projectId' => 10, 'text' => 'older', 'date' => '2026-05-01 10:00:00', 'status' => 'yellow', 'authorFirstname' => 'A', 'authorLastname' => 'B', 'authorProfileId' => null],
            (object) ['id' => 3, 'projectId' => 11, 'text' => 'other project', 'date' => '2026-05-02 10:00:00', 'status' => 'green', 'authorFirstname' => 'C', 'authorLastname' => 'D', 'authorProfileId' => null],
        ]);

        $updates = $this->service->getStatusUpdatesForProjects([10, 11], $this->lastQuarter(), 1);

        $this->assertCount(1, $updates[10]);
        $this->assertSame('newest', $updates[10][0]->text);
        $this->assertCount(1, $updates[11]);
    }

    public function test_project_summaries_flag_stale_and_alerting_projects(): void
    {
        $this->repository->method('getProjectsMeta')->willReturn([
            10 => (object) ['id' => 10, 'name' => 'Fresh red project', 'details' => '<p>Some <b>html</b> description</p>', 'clientId' => 1, 'state' => 0, 'start' => null, 'end' => null, 'type' => 'project', 'parent' => null, 'clientName' => 'Client'],
            11 => (object) ['id' => 11, 'name' => 'Silent project', 'details' => '', 'clientId' => 1, 'state' => 0, 'start' => null, 'end' => null, 'type' => 'project', 'parent' => null, 'clientName' => 'Client'],
        ]);
        $this->repository->method('getLatestStatusUpdateForProjects')->willReturn([
            10 => (object) ['projectId' => 10, 'text' => 'Behind on hiring', 'date' => '2026-07-01 10:00:00', 'status' => 'red', 'authorFirstname' => 'A', 'authorLastname' => 'B'],
            // Project 11 has no status update at all.
        ]);
        $this->projectService->method('getProjectProgress')->willReturn(['percent' => 40.0, 'estimatedCompletionDate' => false, 'plannedCompletionDate' => '']);

        $summaries = $this->service->getProjectSummaries([10, 11]);

        $this->assertSame('red', $summaries[10]->latestStatus);
        $this->assertFalse($summaries[10]->isStale);
        $this->assertSame('Some html description', $summaries[10]->descriptionExcerpt);
        $this->assertNull($summaries[11]->latestStatus);
        $this->assertTrue($summaries[11]->isStale);
    }

    public function test_effort_totals_by_project_and_milestone(): void
    {
        $this->repository->method('getHoursLoggedForProjects')->willReturn([
            (object) ['projectId' => 10, 'milestoneId' => 1, 'loggedHours' => 12.5],
            (object) ['projectId' => 10, 'milestoneId' => 0, 'loggedHours' => 3.0],
            (object) ['projectId' => 11, 'milestoneId' => 2, 'loggedHours' => 4.25],
        ]);

        $effort = $this->service->getEffortForProjects([10, 11], $this->lastQuarter());

        $this->assertEqualsWithDelta(19.75, $effort['total'], 0.001);
        $this->assertEqualsWithDelta(15.5, $effort['byProject'][10], 0.001);
        $this->assertEqualsWithDelta(12.5, $effort['byMilestone'][1], 0.001);
        $this->assertArrayNotHasKey(0, $effort['byMilestone']);
    }

    public function test_build_report_composes_needs_attention_and_deltas(): void
    {
        $this->repository->method('getMilestonesForProjects')->willReturn([
            // Completed this period.
            $this->milestone(1, ['status' => 3]),
            // Completed in the prior period (feeds the delta).
            $this->milestone(2, ['status' => 3]),
            // Overdue -> needs attention.
            $this->milestone(3, ['editTo' => '2026-06-01 00:00:00']),
        ]);
        $this->repository->method('getStatusHistoryForTickets')->willReturn([
            (object) ['ticketId' => 1, 'changeValue' => '3', 'dateModified' => '2026-05-10 09:00:00'],
            (object) ['ticketId' => 2, 'changeValue' => '3', 'dateModified' => '2026-02-10 09:00:00'],
        ]);
        $this->repository->method('getTasksForMilestones')->willReturn([]);
        $this->repository->method('getDueDateChangesForTickets')->willReturn([]);
        $this->repository->method('getGoalsForProjects')->willReturn([
            (object) ['id' => 1, 'title' => 'At-risk goal', 'description' => '', 'status' => 'status_atrisk', 'metricType' => '', 'startValue' => 0.0, 'currentValue' => 1.0, 'endValue' => 10.0, 'setting' => '', 'milestoneId' => '', 'kpi' => '', 'startDate' => null, 'endDate' => null, 'canvasId' => 5, 'projectId' => 10, 'boardTitle' => 'Goals', 'milestoneHeadline' => null],
        ]);
        $this->repository->method('getStatusUpdatesForProjects')->willReturn([]);
        $this->repository->method('getHoursLoggedForProjects')->willReturn([]);
        $this->repository->method('getProjectsMeta')->willReturn([
            10 => (object) ['id' => 10, 'name' => 'Project', 'details' => '', 'clientId' => 1, 'state' => 0, 'start' => null, 'end' => null, 'type' => 'project', 'parent' => null, 'clientName' => 'Client'],
        ]);
        $this->repository->method('getLatestStatusUpdateForProjects')->willReturn([]);
        $this->projectService->method('getProjectProgress')->willReturn(['percent' => 10.0, 'estimatedCompletionDate' => false, 'plannedCompletionDate' => '']);

        $report = $this->service->buildReport([10], $this->lastQuarter());

        $this->assertSame(1, $report['stats']['completed']);
        $this->assertSame(1, $report['deltas']['completedPrior']);
        $this->assertSame(0, $report['deltas']['completedDelta']);
        $this->assertCount(1, $report['needsAttention']['overdueMilestones']);
        $this->assertCount(1, $report['needsAttention']['goalsAtRisk']);
        // No status update ever -> the project is flagged as silent.
        $this->assertCount(1, $report['needsAttention']['staleProjects']);
    }
}

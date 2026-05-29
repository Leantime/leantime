<?php

namespace Unit\app\Domain\Goalcanvas\Services;

use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas as GoalcanvaRepository;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas as GoalcanvasService;
use Unit\TestCase;

/**
 * Unit tests for the Goalcanvas service progress math: goalProgress is
 * (currentValue - startValue) / (endValue - startValue) * 100, clamped to
 * 0..100, and child-goal value aggregation for linkAndReport goals.
 */
class GoalcanvasServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function service(GoalcanvaRepository $repo): GoalcanvasService
    {
        return new GoalcanvasService($repo);
    }

    public function test_computes_goal_progress_as_percentage_of_range(): void
    {
        $repo = $this->make(GoalcanvaRepository::class, [
            'getCanvasItemsById' => fn () => [
                ['id' => 1, 'setting' => 'linkonly', 'startValue' => 0.0, 'endValue' => 100.0, 'currentValue' => 50.0],
            ],
        ]);

        $goals = $this->service($repo)->getCanvasItemsById(1);

        $this->assertEqualsWithDelta(50, $goals[0]['goalProgress'], 0.01);
    }

    public function test_clamps_progress_between_zero_and_one_hundred(): void
    {
        $repo = $this->make(GoalcanvaRepository::class, [
            'getCanvasItemsById' => fn () => [
                ['id' => 1, 'setting' => 'linkonly', 'startValue' => 0.0, 'endValue' => 100.0, 'currentValue' => 150.0],
                ['id' => 2, 'setting' => 'linkonly', 'startValue' => 0.0, 'endValue' => 100.0, 'currentValue' => -20.0],
            ],
        ]);

        $goals = $this->service($repo)->getCanvasItemsById(1);

        $this->assertSame(100, $goals[0]['goalProgress']);
        $this->assertSame(0, $goals[1]['goalProgress']);
    }

    public function test_zero_range_yields_zero_progress(): void
    {
        $repo = $this->make(GoalcanvaRepository::class, [
            'getCanvasItemsById' => fn () => [
                ['id' => 1, 'setting' => 'linkonly', 'startValue' => 50.0, 'endValue' => 50.0, 'currentValue' => 50.0],
            ],
        ]);

        $goals = $this->service($repo)->getCanvasItemsById(1);

        $this->assertSame(0, $goals[0]['goalProgress']);
    }

    public function test_child_goal_reporting_sums_by_setting(): void
    {
        // linkonly children contribute their own currentValue; linkAndReport
        // children contribute their rolled-up childCurrentValue.
        $repo = $this->make(GoalcanvaRepository::class, [
            'getCanvasItemsByKPI' => fn () => [
                ['setting' => 'linkonly', 'currentValue' => 10.0, 'childCurrentValue' => 999.0],
                ['setting' => 'linkAndReport', 'currentValue' => 0.0, 'childCurrentValue' => 5.0],
            ],
        ]);

        $sum = $this->service($repo)->getChildGoalsForReporting(1);

        $this->assertEqualsWithDelta(15.0, $sum, 0.01);
    }
}

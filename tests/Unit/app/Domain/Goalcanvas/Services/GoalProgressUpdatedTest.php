<?php

namespace Unit\app\Domain\Goalcanvas\Services;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\Goalcanvas\Events\GoalProgressUpdated;
use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas as GoalcanvaRepository;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas as GoalcanvasService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Unit\TestCase;

/**
 * GoalProgressUpdated fires when a goal's current value changes; targetReached only when this
 * update crossed the target (in the goal's direction).
 */
class GoalProgressUpdatedTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private array $dispatcherSnapshot = [];

    /** @var array<int, GoalProgressUpdated> */
    private array $updates = [];

    protected function setUp(): void
    {
        parent::setUp();

        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach (['eventRegistry', 'available_hooks', 'patternMatchCache', 'compiledPatternCache', 'eventRegistryVersion'] as $prop) {
            $this->dispatcherSnapshot[$prop] = $reflection->getProperty($prop)->getValue();
        }

        $this->updates = [];
        EventDispatcher::add_event_listener(GoalProgressUpdated::class, function (GoalProgressUpdated $event) {
            $this->updates[] = $event;
        });
    }

    protected function tearDown(): void
    {
        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach ($this->dispatcherSnapshot as $prop => $value) {
            $reflection->getProperty($prop)->setValue(null, $value);
        }

        parent::tearDown();
    }

    /** A service whose goal #7 (project 9) is stored with the given values and patches succeed. */
    private function serviceWithGoal(array $storedGoal): GoalcanvasService
    {
        $repo = $this->make(GoalcanvaRepository::class, [
            'getCanvasItemProjectId' => fn (...$args) => 9,
            'getSingleCanvasItem' => fn () => $storedGoal,
            'patchCanvasItem' => fn () => true,
            'editCanvasItem' => fn () => null,
        ]);

        $service = new GoalcanvasService($repo, $this->make(ProjectService::class));
        $service->setPermissionService($this->make(PermissionService::class, [
            'authorize' => fn () => null,
            'currentUserCan' => fn () => true,
        ]));

        return $service;
    }

    public function test_crossing_the_target_reports_target_reached(): void
    {
        $this->serviceWithGoal(['startValue' => 0, 'currentValue' => 80, 'endValue' => 100])
            ->patchGoalItem(7, ['currentValue' => 100]);

        $this->assertCount(1, $this->updates);
        $this->assertSame(7, $this->updates[0]->goalId);
        $this->assertSame(9, $this->updates[0]->projectId);
        $this->assertSame(80.0, $this->updates[0]->previousValue);
        $this->assertSame(100.0, $this->updates[0]->currentValue);
        $this->assertSame(100.0, $this->updates[0]->endValue);
        $this->assertTrue($this->updates[0]->targetReached);
    }

    public function test_progress_below_the_target_is_not_reached(): void
    {
        $this->serviceWithGoal(['startValue' => 0, 'currentValue' => 10, 'endValue' => 100])
            ->patchGoalItem(7, ['currentValue' => 50]);

        $this->assertCount(1, $this->updates);
        $this->assertFalse($this->updates[0]->targetReached);
    }

    public function test_a_goal_already_past_its_target_is_not_reached_again(): void
    {
        $this->serviceWithGoal(['startValue' => 0, 'currentValue' => 110, 'endValue' => 100])
            ->patchGoalItem(7, ['currentValue' => 120]);

        $this->assertCount(1, $this->updates);
        $this->assertFalse($this->updates[0]->targetReached);
    }

    public function test_a_decreasing_goal_reaches_its_target_from_above(): void
    {
        $this->serviceWithGoal(['startValue' => 50, 'currentValue' => 20, 'endValue' => 10])
            ->patchGoalItem(7, ['currentValue' => 8]);

        $this->assertCount(1, $this->updates);
        $this->assertTrue($this->updates[0]->targetReached);
    }

    public function test_an_unchanged_value_or_other_fields_do_not_fire(): void
    {
        $service = $this->serviceWithGoal(['startValue' => 0, 'currentValue' => 10, 'endValue' => 100]);

        $service->patchGoalItem(7, ['currentValue' => 10]);
        $service->patchGoalItem(7, ['description' => 'Renamed']);
        $service->updateGoalItem(['id' => 7, 'currentValue' => '10', 'description' => 'Form re-save']);

        $this->assertSame([], $this->updates);
    }

    public function test_the_full_form_update_reports_a_changed_value(): void
    {
        $this->serviceWithGoal(['startValue' => 0, 'currentValue' => 10, 'endValue' => 100])
            ->updateGoalItem(['id' => 7, 'currentValue' => '100', 'endValue' => '100', 'startValue' => '0']);

        $this->assertCount(1, $this->updates);
        $this->assertTrue($this->updates[0]->targetReached);
    }
}

<?php

namespace Unit\app\Domain\Goalcanvas\Services;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas as GoalcanvaRepository;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas as GoalcanvasService;
use Unit\TestCase;

/**
 * Unit tests for the Goalcanvas service:
 *  - progress math: goalProgress is (currentValue - startValue) / (endValue - startValue) * 100,
 *    clamped to 0..100, and child-goal value aggregation for linkAndReport goals;
 *  - the fail-closed by-id board/item CRUD chokepoint (every by-id op resolves the entity's real
 *    project via the inherited resolvers, scoped to the "goalcanvas" type, and authorizes a
 *    goals.* verb — reads soft-deny without loading, writes throw without writing).
 */
class GoalcanvasServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function allowingPermissions(): PermissionService
    {
        return $this->make(PermissionService::class, [
            'authorize' => fn () => null,
            'currentUserCan' => fn () => true,
        ]);
    }

    private function service(GoalcanvaRepository $repo, ?PermissionService $perms = null): GoalcanvasService
    {
        $service = new GoalcanvasService($repo);
        $service->setPermissionService($perms ?? $this->allowingPermissions());

        return $service;
    }

    public function test_computes_goal_progress_as_percentage_of_range(): void
    {
        $repo = $this->make(GoalcanvaRepository::class, [
            'getCanvasProjectId' => fn () => 9,
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
            'getCanvasProjectId' => fn () => 9,
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
            'getCanvasProjectId' => fn () => 9,
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
            'getCanvasItemProjectId' => fn () => 9,
            'getCanvasItemsByKPI' => fn () => [
                ['setting' => 'linkonly', 'currentValue' => 10.0, 'childCurrentValue' => 999.0],
                ['setting' => 'linkAndReport', 'currentValue' => 0.0, 'childCurrentValue' => 5.0],
            ],
        ]);

        $sum = $this->service($repo)->getChildGoalsForReporting(1);

        $this->assertEqualsWithDelta(15.0, $sum, 0.01);
    }

    // ---------------------------------------------------------------------
    // Fail-closed by-id board/item CRUD chokepoint.
    // ---------------------------------------------------------------------

    public function test_get_canvas_items_returns_empty_for_foreign_board_without_loading(): void
    {
        $loaded = 0;
        $repo = $this->make(GoalcanvaRepository::class, [
            'getCanvasProjectId' => fn () => null,
            'getCanvasItemsById' => function () use (&$loaded) {
                $loaded++;

                return [['id' => 1]];
            },
        ]);

        $this->assertSame([], $this->service($repo)->getCanvasItemsById(999));
        $this->assertSame(0, $loaded, 'A foreign/unknown board must not have its goals read');
    }

    public function test_child_goal_reporting_returns_zero_for_foreign_parent(): void
    {
        $loaded = 0;
        $repo = $this->make(GoalcanvaRepository::class, [
            'getCanvasItemProjectId' => fn () => null,
            'getCanvasItemsByKPI' => function () use (&$loaded) {
                $loaded++;

                return [];
            },
        ]);

        $this->assertSame(0, $this->service($repo)->getChildGoalsForReporting(999));
        $this->assertSame(0, $loaded, 'A foreign/unknown parent goal must not have its children read');
    }

    public function test_get_goal_item_soft_denies_when_view_not_permitted(): void
    {
        $loaded = 0;
        $repo = $this->make(GoalcanvaRepository::class, [
            'getCanvasItemProjectId' => fn () => 9,
            'getSingleCanvasItem' => function () use (&$loaded) {
                $loaded++;

                return ['id' => 1];
            },
        ]);

        $perms = $this->make(PermissionService::class, ['currentUserCan' => fn () => false]);

        $this->assertFalse($this->service($repo, $perms)->getGoalItem(1));
        $this->assertSame(0, $loaded, 'An unauthorized item returns false without loading (no oracle)');
    }

    public function test_update_goal_item_resolves_project_from_item_id_not_payload_canvas_id(): void
    {
        $resolvedItemId = null;
        $wrote = 0;
        $repo = $this->make(GoalcanvaRepository::class, [
            'getCanvasItemProjectId' => function ($id) use (&$resolvedItemId) {
                $resolvedItemId = $id;

                return 9;
            },
            'editCanvasItem' => function () use (&$wrote) {
                $wrote++;
            },
        ]);

        $this->service($repo)->updateGoalItem(['itemId' => 42, 'canvasId' => 9999, 'description' => 'x']);

        $this->assertSame(42, $resolvedItemId, 'Project resolved from itemId, not the payload canvasId');
        $this->assertSame(1, $wrote);
    }

    public function test_patch_goal_item_throws_and_never_writes_for_unresolved_item(): void
    {
        $patched = 0;
        $repo = $this->make(GoalcanvaRepository::class, [
            'getCanvasItemProjectId' => fn () => null,
            'patchCanvasItem' => function () use (&$patched) {
                $patched++;

                return true;
            },
        ]);

        try {
            $this->service($repo)->patchGoalItem(5, ['status' => 'x']);
            $this->fail('Expected AuthorizationException');
        } catch (AuthorizationException) {
        }
        $this->assertSame(0, $patched);
    }

    public function test_delete_goal_item_throws_and_never_deletes_for_unresolved_item(): void
    {
        $deleted = 0;
        $repo = $this->make(GoalcanvaRepository::class, [
            'getCanvasItemProjectId' => fn () => null,
            'delCanvasItem' => function () use (&$deleted) {
                $deleted++;
            },
        ]);

        try {
            $this->service($repo)->deleteGoalItem(5);
            $this->fail('Expected AuthorizationException');
        } catch (AuthorizationException) {
        }
        $this->assertSame(0, $deleted);
    }

    public function test_create_goal_item_throws_and_never_inserts_for_unknown_board(): void
    {
        $inserted = 0;
        $repo = $this->make(GoalcanvaRepository::class, [
            'getCanvasProjectId' => fn () => null,
            'addCanvasItem' => function () use (&$inserted) {
                $inserted++;

                return '1';
            },
        ]);

        try {
            $this->service($repo)->createGoalItem(['canvasId' => 9999, 'box' => 'goal']);
            $this->fail('Expected AuthorizationException');
        } catch (AuthorizationException) {
        }
        $this->assertSame(0, $inserted);
    }

    public function test_create_goal_api_throws_for_unknown_board(): void
    {
        $inserted = 0;
        $repo = $this->make(GoalcanvaRepository::class, [
            'getCanvasProjectId' => fn () => null,
            'createGoal' => function () use (&$inserted) {
                $inserted++;

                return '1';
            },
        ]);

        try {
            $this->service($repo)->createGoal(['canvasId' => 9999, 'box' => 'goal']);
            $this->fail('Expected AuthorizationException');
        } catch (AuthorizationException) {
        }
        $this->assertSame(0, $inserted);
    }

    public function test_delete_goal_board_throws_for_unresolved_board(): void
    {
        $deleted = 0;
        $repo = $this->make(GoalcanvaRepository::class, [
            'getCanvasProjectId' => fn () => null,
            'deleteCanvas' => function () use (&$deleted) {
                $deleted++;
            },
        ]);

        try {
            $this->service($repo)->deleteGoalBoard(5);
            $this->fail('Expected AuthorizationException');
        } catch (AuthorizationException) {
        }
        $this->assertSame(0, $deleted);
    }

    public function test_update_goalboard_throws_for_unresolved_board(): void
    {
        $updated = 0;
        $repo = $this->make(GoalcanvaRepository::class, [
            'getCanvasProjectId' => fn () => null,
            'updateCanvas' => function () use (&$updated) {
                $updated++;

                return 1;
            },
        ]);

        try {
            $this->service($repo)->updateGoalboard(['id' => 5, 'title' => 'x']);
            $this->fail('Expected AuthorizationException');
        } catch (AuthorizationException) {
        }
        $this->assertSame(0, $updated);
    }

    public function test_merge_goal_board_requires_both_boards_to_resolve(): void
    {
        $merged = 0;
        $repo = $this->make(GoalcanvaRepository::class, [
            'getCanvasProjectId' => fn ($id) => $id === 1 ? 9 : null,
            'mergeCanvas' => function () use (&$merged) {
                $merged++;

                return true;
            },
        ]);

        try {
            $this->service($repo)->mergeGoalBoard(2, 1);
            $this->fail('Expected AuthorizationException');
        } catch (AuthorizationException) {
        }
        $this->assertSame(0, $merged);
    }

    public function test_copy_goal_board_throws_when_source_unresolved(): void
    {
        $copied = 0;
        $repo = $this->make(GoalcanvaRepository::class, [
            'getCanvasProjectId' => fn () => null,
            'copyCanvas' => function () use (&$copied) {
                $copied++;

                return 1;
            },
        ]);

        try {
            $this->service($repo)->copyGoalBoard(5, 7, 1, 'Copy');
            $this->fail('Expected AuthorizationException');
        } catch (AuthorizationException) {
        }
        $this->assertSame(0, $copied);
    }
}

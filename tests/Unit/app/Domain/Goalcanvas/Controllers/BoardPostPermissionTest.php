<?php

namespace Tests\Unit\app\Domain\Goalcanvas\Controllers;

use Leantime\Core\Auth\Permissions\PermissionEnforcer;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Domain\Blueprints\Permissions\BlueprintsPermissions;
use Leantime\Domain\Canvas\Controllers\ShowCanvas as CanvasShowCanvas;
use Leantime\Domain\Goalcanvas\Controllers\Dashboard as GoalDashboard;
use Leantime\Domain\Goalcanvas\Controllers\ShowCanvas as GoalShowCanvas;
use Leantime\Domain\Goalcanvas\Permissions\GoalcanvasPermissions;
use Leantime\Domain\Logicmodelcanvas\Controllers\ShowCanvas as LogicModelShowCanvas;

/**
 * The board pages' post handlers create, rename, clone, merge and import boards. The services
 * behind each action check that action's permission, so the controller must only require view.
 * Requiring edit there refused a role that may create boards but not edit them.
 */
class BoardPostPermissionTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    public static function boardPostHandlers(): array
    {
        return [
            'goal board' => [GoalShowCanvas::class, GoalcanvasPermissions::VIEW],
            'goal dashboard' => [GoalDashboard::class, GoalcanvasPermissions::VIEW],
            'legacy canvas board' => [CanvasShowCanvas::class, BlueprintsPermissions::VIEW],
            'logic model board' => [LogicModelShowCanvas::class, BlueprintsPermissions::VIEW],
        ];
    }

    /**
     * @dataProvider boardPostHandlers
     */
    public function test_board_post_requires_only_view(string $controller, string $viewPermission): void
    {
        session(['currentProject' => 3]);

        $askedPermissions = [];
        $permissions = $this->make(PermissionService::class, [
            'currentUserCan' => function (string $key) use (&$askedPermissions, $viewPermission): bool {
                $askedPermissions[] = $key;

                return $key === $viewPermission;
            },
        ]);

        (new PermissionEnforcer($permissions))->enforce($controller, 'post', []);

        $this->assertSame([$viewPermission], $askedPermissions);
    }
}

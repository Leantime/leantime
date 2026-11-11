<?php

namespace Unit\app\Domain\Goalcanvas\Controllers;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Leantime\Domain\Goalcanvas\Controllers\DelCanvasItem;
use Leantime\Domain\Goalcanvas\Permissions\GoalcanvasPermissions;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas as GoalcanvasService;
use Symfony\Component\HttpFoundation\Response;
use Unit\TestCase;

/**
 * The delete confirmation is offered by the goal's own project (canvasDialog), so it is
 * authorized against that project, not the session project.
 */
class DelCanvasItemTest extends TestCase
{
    /** @var array<int, array{0: string, 1: int|null}> */
    private array $authorizedChecks = [];

    private function controller(array|false $goalItem, array $allowedProjects): DelCanvasItem
    {
        $goalService = $this->createMock(GoalcanvasService::class);
        $goalService->method('getGoalItem')->willReturn($goalItem);
        app()->instance(GoalcanvasService::class, $goalService);

        $permissions = $this->createMock(PermissionService::class);
        $permissions->method('authorize')->willReturnCallback(function (string $key, ?int $projectId = null) use ($allowedProjects): void {
            $this->authorizedChecks[] = [$key, $projectId];
            if (! in_array($projectId, $allowedProjects, true)) {
                throw new AuthorizationException;
            }
        });
        app()->instance(PermissionService::class, $permissions);

        $tpl = $this->createMock(Template::class);
        $tpl->method('displayPartial')->willReturnCallback(
            fn (string $template, int $responseCode = 200) => new Response($template, $responseCode)
        );

        return new DelCanvasItem(
            $this->createMock(IncomingRequest::class),
            $tpl,
            $this->createMock(Language::class),
        );
    }

    public function test_confirmation_is_authorized_against_the_goals_project(): void
    {
        session(['currentProject' => 3]);

        $response = $this->controller(['id' => 5, 'projectId' => 7], [7])->get(['id' => 5]);

        $this->assertSame('goalcanvas.delCanvasItem', $response->getContent());
        $this->assertSame([[GoalcanvasPermissions::DELETE, 7]], $this->authorizedChecks);
    }

    public function test_confirmation_is_refused_without_delete_in_the_goals_project(): void
    {
        // Allowed in the session project (3) only; the goal lives in 7.
        session(['currentProject' => 3]);

        $this->expectException(AuthorizationException::class);

        $this->controller(['id' => 5, 'projectId' => 7], [3])->get(['id' => 5]);
    }

    public function test_missing_or_invisible_goal_returns_not_found(): void
    {
        $response = $this->controller(false, [7])->get(['id' => 5]);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame([], $this->authorizedChecks);
    }
}

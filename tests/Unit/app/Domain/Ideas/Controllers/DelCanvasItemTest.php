<?php

namespace Unit\app\Domain\Ideas\Controllers;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Leantime\Domain\Ideas\Controllers\DelCanvasItem;
use Leantime\Domain\Ideas\Permissions\IdeasPermissions;
use Leantime\Domain\Ideas\Services\Ideas as IdeaService;
use Symfony\Component\HttpFoundation\Response;
use Unit\TestCase;

/**
 * The idea dialog offers Delete by the idea's own project, so the confirmation is authorized
 * against that project, not the session project.
 */
class DelCanvasItemTest extends TestCase
{
    /** @var array<int, array{0: string, 1: int|null}> */
    private array $authorizedChecks = [];

    private function controller(array $idea, array $allowedProjects): DelCanvasItem
    {
        $ideaService = $this->createMock(IdeaService::class);
        $ideaService->method('getIdeaItem')->willReturn($idea);
        app()->instance(IdeaService::class, $ideaService);

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

        return new DelCanvasItem($this->createMock(IncomingRequest::class), $tpl, $this->createMock(Language::class));
    }

    public function test_confirmation_is_authorized_against_the_ideas_project(): void
    {
        session(['currentProject' => 3]);

        $response = $this->controller(['id' => 5, 'projectId' => 9], [9])->get(['id' => 5]);

        $this->assertSame('ideas.delCanvasItem', $response->getContent());
        $this->assertSame([[IdeasPermissions::DELETE, 9]], $this->authorizedChecks);
    }

    public function test_confirmation_is_refused_without_delete_in_the_ideas_project(): void
    {
        session(['currentProject' => 3]);

        $this->expectException(AuthorizationException::class);

        $this->controller(['id' => 5, 'projectId' => 9], [3])->get(['id' => 5]);
    }

    public function test_unknown_idea_returns_not_found(): void
    {
        $response = $this->controller([], [9])->get(['id' => 5]);

        $this->assertSame(404, $response->getStatusCode());
    }
}

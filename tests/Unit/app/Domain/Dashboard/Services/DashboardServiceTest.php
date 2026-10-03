<?php

namespace Unit\app\Domain\Dashboard\Services;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Comments\Services\Comments as CommentService;
use Leantime\Domain\Dashboard\Services\Dashboard;
use Leantime\Domain\Reactions\Services\Reactions as ReactionService;
use Unit\TestCase;

/**
 * Unit tests for the Dashboard service logic extracted from the
 * Dashboard\Show controller.
 */
class DashboardServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function makeService(
        ?CommentService $commentService = null,
        ?CommentRepository $commentRepository = null,
        ?ReactionService $reactionsService = null,
        ?PermissionService $permissions = null,
    ): Dashboard {
        $service = new Dashboard(
            $commentService ?? $this->make(CommentService::class),
            $commentRepository ?? $this->make(CommentRepository::class),
            $reactionsService ?? $this->make(ReactionService::class),
        );
        $service->setPermissionService($permissions ?? $this->make(PermissionService::class, [
            'authorize' => fn () => null,
            'currentUserCan' => fn () => true,
        ]));

        return $service;
    }

    public function test_get_project_comments_attaches_replies_per_comment(): void
    {
        $commentService = $this->make(CommentService::class, [
            'getComments' => fn () => [
                ['id' => 1, 'text' => 'first'],
                ['id' => 2, 'text' => 'second'],
            ],
        ]);
        $commentRepository = $this->make(CommentRepository::class, [
            'getReplies' => fn ($id) => [['id' => 100 + $id, 'commentParent' => $id]],
        ]);

        $result = $this->makeService($commentService, $commentRepository)
            ->getProjectCommentsWithReplies(7);

        $this->assertCount(2, $result);
        $this->assertSame([['id' => 101, 'commentParent' => 1]], $result[0]['replies']);
        $this->assertSame([['id' => 102, 'commentParent' => 2]], $result[1]['replies']);
    }

    public function test_get_project_comments_returns_empty_when_no_comments(): void
    {
        $commentService = $this->make(CommentService::class, [
            'getComments' => fn () => false,
        ]);

        $result = $this->makeService($commentService)->getProjectCommentsWithReplies(7);

        $this->assertSame([], $result);
    }

    public function test_get_project_comments_rejects_invalid_project_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->makeService()->getProjectCommentsWithReplies(0);
    }

    public function test_count_project_comments_casts_to_int(): void
    {
        $commentRepository = $this->make(CommentRepository::class, [
            'countComments' => fn () => '5',
        ]);

        $result = $this->makeService(null, $commentRepository)->countProjectComments(3);

        $this->assertSame(5, $result);
    }

    public function test_count_project_comments_is_denied_for_a_project_the_caller_cannot_view(): void
    {
        $authorizedProject = null;
        $commentRepository = $this->make(CommentRepository::class, [
            'countComments' => function () {
                throw new \RuntimeException('must not count comments of a project the caller cannot view');
            },
        ]);
        $permissions = $this->make(PermissionService::class, [
            'authorize' => function (string $key, ?int $projectId = null) use (&$authorizedProject): void {
                $authorizedProject = $projectId;

                throw new AuthorizationException;
            },
        ]);

        try {
            $this->makeService(null, $commentRepository, null, $permissions)->countProjectComments(42);
            $this->fail('countProjectComments must authorize the requested project');
        } catch (AuthorizationException) {
            $this->assertSame(42, $authorizedProject, 'The check must target the requested project, not the session one');
        }
    }

    public function test_count_project_comments_rejects_invalid_project_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->makeService()->countProjectComments(-1);
    }

    public function test_user_has_favorited_project_true_when_reactions_present(): void
    {
        $reactionsService = $this->make(ReactionService::class, [
            'getUserReactions' => fn () => [['reaction' => 'favorite']],
        ]);

        $result = $this->makeService(null, null, $reactionsService)
            ->userHasFavoritedProject(42, 9);

        $this->assertTrue($result);
    }

    public function test_user_has_favorited_project_false_when_empty(): void
    {
        $reactionsService = $this->make(ReactionService::class, [
            'getUserReactions' => fn () => [],
        ]);

        $result = $this->makeService(null, null, $reactionsService)
            ->userHasFavoritedProject(42, 9);

        $this->assertFalse($result);
    }

    public function test_user_has_favorited_project_false_when_repo_returns_false(): void
    {
        $reactionsService = $this->make(ReactionService::class, [
            'getUserReactions' => fn () => false,
        ]);

        $result = $this->makeService(null, null, $reactionsService)
            ->userHasFavoritedProject(42, 9);

        $this->assertFalse($result);
    }
}

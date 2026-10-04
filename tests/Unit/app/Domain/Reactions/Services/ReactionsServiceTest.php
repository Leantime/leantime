<?php

namespace Unit\app\Domain\Reactions\Services;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Reactions\Repositories\Reactions as ReactionsRepository;
use Leantime\Domain\Reactions\Services\Reactions;
use Unit\TestCase;

/**
 * Unit tests for the Reactions service JSON-RPC entry points: react/unreact act as the session
 * user only, and every entry point is fenced to the reacted-on entity's REAL project (resolved
 * server-side from module/moduleId; unknown modules fail closed).
 */
class ReactionsServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Builds the service with a comment repository that resolves projects from a fixed map
     * ("module:id" => projectId) and a permission engine that grants VIEW only on $viewable.
     *
     * @param  array<string, int>  $projectByEntity
     * @param  array<int, int>  $viewable
     * @param  array<int, array<string, mixed>>  $comments  comment id => comment row
     */
    private function makeService(
        ReactionsRepository $repo,
        array $projectByEntity = ['ticket:5' => 9],
        array $viewable = [9],
        array $comments = [],
    ): Reactions {
        $commentRepo = $this->make(CommentRepository::class, [
            'resolveModuleProjectId' => fn (string $module, int $moduleId) => $projectByEntity[$module.':'.$moduleId] ?? null,
            'getComment' => fn (int $id) => $comments[$id] ?? false,
        ]);

        $service = new Reactions($repo, $commentRepo);
        $service->setPermissionService($this->make(PermissionService::class, [
            'currentUserCan' => fn (string $key, ?int $projectId = null) => in_array($projectId, $viewable, true),
        ]));

        return $service;
    }

    public function test_react_uses_the_session_user(): void
    {
        session(['userdata' => ['id' => 42]]);

        $capturedUserId = null;
        $repo = $this->make(ReactionsRepository::class, [
            'getUserReactions' => fn (...$args) => [],
            'addReaction' => function ($userId, ...$rest) use (&$capturedUserId) {
                $capturedUserId = $userId;

                return true;
            },
        ]);

        $result = $this->makeService($repo)->react('tickets', 5, 'thumbsup');

        $this->assertTrue($result);
        $this->assertSame(42, $capturedUserId, 'react() must persist the session user, not a passed id');
    }

    public function test_unreact_uses_the_session_user(): void
    {
        session(['userdata' => ['id' => 7]]);

        $capturedUserId = null;
        $repo = $this->make(ReactionsRepository::class, [
            'removeUserReaction' => function ($userId, ...$rest) use (&$capturedUserId) {
                $capturedUserId = $userId;

                return true;
            },
        ]);

        $result = $this->makeService($repo)->unreact('tickets', 5, 'thumbsup');

        $this->assertTrue($result);
        $this->assertSame(7, $capturedUserId, 'unreact() must remove for the session user, not a passed id');
    }

    public function test_react_is_denied_on_an_entity_in_a_project_the_caller_cannot_view(): void
    {
        session(['userdata' => ['id' => 42]]);

        $repo = $this->make(ReactionsRepository::class, [
            'getUserReactions' => fn (...$args) => [],
            'addReaction' => function () {
                throw new \RuntimeException('must not write a reaction on a foreign project entity');
            },
            'removeUserReaction' => function () {
                throw new \RuntimeException('must not remove a reaction on a foreign project entity');
            },
        ]);

        // Ticket 6 lives in project 99, which the caller cannot view.
        $service = $this->makeService($repo, ['ticket:6' => 99], [9]);

        $this->assertFalse($service->react('ticket', 6, 'thumbsup'));
        $this->assertFalse($service->unreact('ticket', 6, 'thumbsup'));
    }

    public function test_unknown_module_fails_closed(): void
    {
        $repo = $this->make(ReactionsRepository::class, [
            'getEntityReactionsWithUsers' => function () {
                throw new \RuntimeException('unknown modules must not be read');
            },
            'getGroupedEntityReactions' => function () {
                throw new \RuntimeException('unknown modules must not be read');
            },
        ]);

        $service = $this->makeService($repo);

        $this->assertSame([], $service->getEntityReactionsWithUsers('somethingElse', 5));
        $this->assertSame([], $service->getGroupedEntityReactions('somethingElse', 5));
    }

    public function test_comment_reactions_resolve_the_comments_host_project(): void
    {
        $repo = $this->make(ReactionsRepository::class, [
            'getEntityReactionsWithUsers' => fn () => [['reaction' => 'like', 'users' => ['Ann']]],
        ]);

        // Comment 11 sits on ticket 5 (project 9, viewable); comment 12 on ticket 6 (project 99, not).
        $service = $this->makeService(
            $repo,
            ['ticket:5' => 9, 'ticket:6' => 99],
            [9],
            [
                11 => ['id' => 11, 'module' => 'ticket', 'moduleId' => 5],
                12 => ['id' => 12, 'module' => 'ticket', 'moduleId' => 6],
            ],
        );

        $this->assertNotSame([], $service->getEntityReactionsWithUsers('comment', 11));
        $this->assertSame([], $service->getEntityReactionsWithUsers('comment', 12));
    }

    public function test_project_favorite_requires_access_to_the_project(): void
    {
        session(['userdata' => ['id' => 42]]);

        $added = [];
        $repo = $this->make(ReactionsRepository::class, [
            'getUserReactions' => fn (...$args) => [],
            'addReaction' => function ($userId, $module, $moduleId) use (&$added) {
                $added[] = $moduleId;

                return true;
            },
        ]);

        // Project 5 is viewable, project 6 is not.
        $service = $this->makeService($repo, ['project:5' => 5, 'project:6' => 6], [5]);

        $this->assertTrue($service->react('project', 5, 'favorite'));
        $this->assertFalse($service->react('project', 6, 'favorite'));
        $this->assertSame([5], $added);
    }

    /**
     * Non-positive user or entity ids are invalid and must be rejected before the repository is touched.
     *
     * @dataProvider invalidReactionIds
     */
    public function test_add_reaction_rejects_non_positive_ids(int $userId, int $moduleId): void
    {
        $repoCalled = false;
        $repo = $this->make(ReactionsRepository::class, [
            'addReaction' => function () use (&$repoCalled) {
                $repoCalled = true;

                return true;
            },
            'getUserReactions' => function () use (&$repoCalled) {
                $repoCalled = true;

                return [];
            },
        ]);

        $this->assertFalse($this->makeService($repo)->addReaction($userId, 'ticket', $moduleId, 'like'));
        $this->assertFalse($repoCalled);
    }

    /**
     * @return array<string, array{int, int}>
     */
    public static function invalidReactionIds(): array
    {
        return [
            'zero user' => [0, 5],
            'negative user' => [-1, 5],
            'zero entity' => [42, 0],
            'negative entity' => [42, -3],
        ];
    }
}

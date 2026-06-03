<?php

namespace Unit\app\Domain\Comments\Services;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Comments\Services\Comments;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Reactions\Services\Reactions as ReactionsService;
use Unit\TestCase;

/**
 * Unit tests for the Comments service: reaction orchestration plus the project-scoped
 * authorization fences (comments are read/moderated against the host entity's REAL project).
 */
class CommentsServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** The session user used across the reaction tests. */
    private const SESSION_USER = 5;

    protected function setUp(): void
    {
        parent::setUp();
        session(['userdata.id' => self::SESSION_USER]);
    }

    /**
     * Build the service. By default the comment repository resolves a real (ticket) comment in
     * project 9 and the permission engine allows everything; pass overrides to exercise denials.
     */
    private function makeService(
        ReactionsService $reactionsService,
        ?CommentRepository $repo = null,
        ?PermissionService $permissions = null,
    ): Comments {
        $service = new Comments(
            $repo ?? $this->defaultRepo(),
            $this->make(ProjectService::class),
            $this->make(LanguageCore::class),
            $reactionsService,
        );
        $service->setPermissionService($permissions ?? $this->allowingPermissions());

        return $service;
    }

    private function defaultRepo(): CommentRepository
    {
        return $this->make(CommentRepository::class, [
            'getComment' => fn () => ['id' => 99, 'userId' => self::SESSION_USER, 'module' => 'ticket', 'moduleId' => 1],
            'resolveModuleProjectId' => fn () => 9,
        ]);
    }

    private function allowingPermissions(): PermissionService
    {
        return $this->make(PermissionService::class, [
            'authorize' => fn () => null,
            'currentUserCan' => fn () => true,
        ]);
    }

    private function denyingPermissions(): PermissionService
    {
        return $this->make(PermissionService::class, [
            'authorize' => function (): void {
                throw new AuthorizationException;
            },
            'currentUserCan' => fn () => false,
        ]);
    }

    private function noopReactions(): ReactionsService
    {
        return $this->make(ReactionsService::class, []);
    }

    // ---------------------------------------------------------------------
    // Reaction orchestration (existing behaviour, now session-pinned).
    // ---------------------------------------------------------------------

    public function test_toggle_rejects_unknown_reaction_type(): void
    {
        $added = false;
        $removed = false;

        $reactionsService = $this->make(ReactionsService::class, [
            'getReactionType' => fn () => false,
            'addReaction' => function () use (&$added) {
                $added = true;

                return true;
            },
            'removeReaction' => function () use (&$removed) {
                $removed = true;

                return true;
            },
        ]);

        $result = $this->makeService($reactionsService)->toggleCommentReaction(self::SESSION_USER, 99, 'bogus');

        $this->assertFalse($result);
        $this->assertFalse($added, 'No reaction should be added for an unknown type');
        $this->assertFalse($removed, 'No reaction should be removed for an unknown type');
    }

    public function test_toggle_off_removes_existing_same_reaction(): void
    {
        $removeCalls = [];
        $added = false;

        $reactionsService = $this->make(ReactionsService::class, [
            'getReactionType' => fn () => 'positive',
            'getUserReactions' => fn () => [['reaction' => 'thumbsup']],
            'removeReaction' => function ($userId, $module, $moduleId, $reaction) use (&$removeCalls) {
                $removeCalls[] = [$userId, $module, $moduleId, $reaction];

                return true;
            },
            'addReaction' => function () use (&$added) {
                $added = true;

                return true;
            },
        ]);

        $result = $this->makeService($reactionsService)->toggleCommentReaction(self::SESSION_USER, 99, 'thumbsup');

        $this->assertTrue($result);
        $this->assertFalse($added, 'Toggling off should not add a reaction');
        $this->assertSame([[self::SESSION_USER, 'comment', 99, 'thumbsup']], $removeCalls);
    }

    public function test_toggle_on_replaces_existing_sentiment(): void
    {
        $removeCalls = [];
        $addCalls = [];

        $reactionsService = $this->make(ReactionsService::class, [
            'getReactionType' => fn () => 'positive',
            'getUserReactions' => function ($userId, $module, $moduleId, $reaction = '') {
                if ($reaction !== '') {
                    return [];
                }

                return [['reaction' => 'thumbsdown']];
            },
            'removeReaction' => function ($userId, $module, $moduleId, $reaction) use (&$removeCalls) {
                $removeCalls[] = [$userId, $module, $moduleId, $reaction];

                return true;
            },
            'addReaction' => function ($userId, $module, $moduleId, $reaction) use (&$addCalls) {
                $addCalls[] = [$userId, $module, $moduleId, $reaction];

                return true;
            },
        ]);

        $result = $this->makeService($reactionsService)->toggleCommentReaction(self::SESSION_USER, 99, 'thumbsup');

        $this->assertTrue($result);
        $this->assertSame([[self::SESSION_USER, 'comment', 99, 'thumbsdown']], $removeCalls);
        $this->assertSame([[self::SESSION_USER, 'comment', 99, 'thumbsup']], $addCalls);
    }

    public function test_toggle_on_with_no_existing_reactions_just_adds(): void
    {
        $removeCalls = [];
        $addCalls = [];

        $reactionsService = $this->make(ReactionsService::class, [
            'getReactionType' => fn () => 'positive',
            'getUserReactions' => fn () => false,
            'removeReaction' => function (...$args) use (&$removeCalls) {
                $removeCalls[] = $args;

                return true;
            },
            'addReaction' => function ($userId, $module, $moduleId, $reaction) use (&$addCalls) {
                $addCalls[] = [$userId, $module, $moduleId, $reaction];

                return true;
            },
        ]);

        $result = $this->makeService($reactionsService)->toggleCommentReaction(self::SESSION_USER, 99, 'thumbsup');

        $this->assertTrue($result);
        $this->assertSame([], $removeCalls, 'Nothing to remove when there are no existing reactions');
        $this->assertSame([[self::SESSION_USER, 'comment', 99, 'thumbsup']], $addCalls);
    }

    public function test_get_comment_reactions_flattens_user_reaction_codes(): void
    {
        $reactionsService = $this->make(ReactionsService::class, [
            'getEntityReactionsWithUsers' => fn () => ['thumbsup' => ['count' => 2]],
            'getUserReactions' => fn () => [
                ['reaction' => 'thumbsup'],
                ['reaction' => 'heart'],
            ],
        ]);

        $result = $this->makeService($reactionsService)->getCommentReactions(99, 5);

        $this->assertSame(['thumbsup' => ['count' => 2]], $result['reactions']);
        $this->assertSame(['thumbsup', 'heart'], $result['userReactions']);
    }

    public function test_get_comment_reactions_handles_anonymous_user(): void
    {
        $reactionsService = $this->make(ReactionsService::class, [
            'getEntityReactionsWithUsers' => fn () => [],
            'getUserReactions' => fn () => [['reaction' => 'thumbsup']],
        ]);

        $result = $this->makeService($reactionsService)->getCommentReactions(99, 0);

        $this->assertSame([], $result['reactions']);
        $this->assertSame([], $result['userReactions']);
    }

    // ---------------------------------------------------------------------
    // Project-scoped authorization fences (the IDOR hardening).
    // ---------------------------------------------------------------------

    public function test_toggle_reaction_uses_session_user_not_caller_supplied_id(): void
    {
        // A caller passes someone else's id; the service must react as the SESSION user only.
        $addCalls = [];
        $reactionsService = $this->make(ReactionsService::class, [
            'getReactionType' => fn () => 'positive',
            'getUserReactions' => fn () => false,
            'addReaction' => function ($userId, $module, $moduleId, $reaction) use (&$addCalls) {
                $addCalls[] = [$userId, $module, $moduleId, $reaction];

                return true;
            },
        ]);

        $this->makeService($reactionsService)->toggleCommentReaction(999, 99, 'thumbsup');

        $this->assertSame([[self::SESSION_USER, 'comment', 99, 'thumbsup']], $addCalls, 'Reaction must use the session user, not the caller-supplied id');
    }

    public function test_toggle_reaction_is_denied_for_a_foreign_project(): void
    {
        // Valid reaction type so the method reaches the project fence (not the type guard).
        $reactions = $this->make(ReactionsService::class, ['getReactionType' => fn () => 'positive']);
        $service = $this->makeService($reactions, $this->defaultRepo(), $this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->toggleCommentReaction(self::SESSION_USER, 99, 'thumbsup');
    }

    public function test_get_comments_is_denied_for_a_foreign_project(): void
    {
        // getComments resolves the host entity's project and authorizes VIEW there; a denying
        // engine must throw before any comment data is returned.
        $service = $this->makeService($this->noopReactions(), $this->defaultRepo(), $this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->getComments('ticket', 123);
    }

    public function test_delete_comment_denies_non_author_moderation_cross_project(): void
    {
        // Comment belongs to another user; the session user is NOT a moderator in the comment's
        // project (denying engine) -> deleteComment must refuse and never reach the repo delete.
        $repo = $this->make(CommentRepository::class, [
            'getComment' => fn () => ['id' => 99, 'userId' => 7, 'module' => 'ticket', 'moduleId' => 1],
            'resolveModuleProjectId' => fn () => 9,
            'deleteComment' => function (): bool {
                throw new \RuntimeException('delete must not run when moderation is denied');
            },
        ]);
        $service = $this->makeService($this->noopReactions(), $repo, $this->denyingPermissions());

        $this->assertFalse($service->deleteComment(99));
    }

    public function test_delete_comment_allows_the_author_without_moderation(): void
    {
        $deleted = null;
        $repo = $this->make(CommentRepository::class, [
            // Authored by the session user -> author branch, no moderation check needed.
            'getComment' => fn () => ['id' => 99, 'userId' => self::SESSION_USER, 'module' => 'ticket', 'moduleId' => 1],
            'resolveModuleProjectId' => fn () => 9,
            'deleteComment' => function ($id) use (&$deleted): bool {
                $deleted = $id;

                return true;
            },
        ]);
        // Denying engine proves the author path does NOT depend on comments.moderate.
        $service = $this->makeService($this->noopReactions(), $repo, $this->denyingPermissions());

        $this->assertTrue($service->deleteComment(99));
        $this->assertSame(99, $deleted);
    }
}

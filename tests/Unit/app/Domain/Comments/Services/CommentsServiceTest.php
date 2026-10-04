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
        ?ProjectService $projects = null,
    ): Comments {
        $service = new Comments(
            $repo ?? $this->defaultRepo(),
            $projects ?? $this->make(ProjectService::class),
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

    /**
     * #3756: a comment on a canvas-family target (wiki article, idea, *canvasitem) was
     * permission-checked and then silently discarded, because only tickets and projects were
     * loaded as the host entity. It must now be written.
     */
    public function test_add_comment_writes_for_canvas_family_modules(): void
    {
        session(['userdata.id' => self::SESSION_USER, 'userdata.name' => 'Tester', 'currentProject' => 9]);

        foreach (['article', 'idea', 'leancanvasitem'] as $module) {
            $written = null;
            $repo = $this->make(CommentRepository::class, [
                'resolveModuleProjectId' => fn () => 9,
                'addComment' => function ($mapper, $writtenModule) use (&$written) {
                    $written = [$writtenModule, $mapper['moduleId']];

                    return '501';
                },
            ]);

            $projects = $this->make(ProjectService::class, ['notifyProjectUsers' => fn () => null]);

            $result = $this->makeService($this->noopReactions(), $repo, null, $projects)->addComment(['text' => 'hello'], $module, 140);

            $this->assertTrue($result, "$module comment must be written");
            $this->assertSame([$module, 140], $written);
        }
    }

    /**
     * #3067 / #2164: over JSON-RPC the entity arrives as an array (or a string), and the ticket
     * notification path read ->type off it ("Attempt to read property on string/array"). The
     * real ticket must be loaded instead of trusting the caller-supplied shape.
     */
    public function test_add_comment_on_a_ticket_loads_the_ticket_when_the_entity_is_not_an_object(): void
    {
        session(['userdata.id' => self::SESSION_USER, 'userdata.name' => 'Tester', 'currentProject' => 9]);

        $ticket = new \Leantime\Domain\Tickets\Models\Tickets(['id' => 1, 'projectId' => 9, 'type' => 'task', 'headline' => 'H']);
        $this->app->instance(\Leantime\Domain\Tickets\Services\Tickets::class, $this->make(\Leantime\Domain\Tickets\Services\Tickets::class, [
            'getTicket' => fn () => $ticket,
        ]));

        $repo = $this->make(CommentRepository::class, [
            'resolveModuleProjectId' => fn () => 9,
            'addComment' => fn () => '502',
        ]);
        $projects = $this->make(ProjectService::class, ['notifyProjectUsers' => fn () => null]);
        $service = $this->makeService($this->noopReactions(), $repo, null, $projects);

        $this->assertTrue($service->addComment(['text' => 'hi'], 'ticket', 1, ['id' => 1, 'type' => 'task']));
        $this->assertTrue($service->addComment(['text' => 'hi'], 'ticket', 1, 'a string'));
    }

    /**
     * The notification goes to the project the comment was authorized against, not the ambient
     * session project (a browser session can be "in" project A while commenting on B over RPC).
     */
    public function test_add_comment_notifies_the_items_project_not_the_session_project(): void
    {
        session(['userdata.id' => self::SESSION_USER, 'userdata.name' => 'Tester', 'currentProject' => 1]);

        $notified = null;
        $repo = $this->make(CommentRepository::class, [
            'resolveModuleProjectId' => fn () => 9,
            'addComment' => fn () => '503',
        ]);
        $projects = $this->make(ProjectService::class, [
            'notifyProjectUsers' => function ($notification) use (&$notified) {
                $notified = $notification;
            },
        ]);

        $this->makeService($this->noopReactions(), $repo, null, $projects)->addComment(['text' => 'hi'], 'idea', 140);

        $this->assertSame(9, $notified->projectId);
        $this->assertStringContainsString('projectId=9', $notified->url['url']);
    }

    public function test_add_comment_on_a_missing_canvas_item_writes_nothing(): void
    {
        $repo = $this->make(CommentRepository::class, [
            'resolveModuleProjectId' => fn () => null,
            'addComment' => function () {
                $this->fail('nothing may be written for an item that does not exist');
            },
        ]);

        // A missing item is reported as not found (#3704) rather than a silent false. A
        // caller-supplied entity must not stand in for an item that doesn't resolve (or belongs to
        // a different canvas type): canvas-family entities are always resolved server-side.
        foreach ([null, ['id' => 404, 'projectId' => 9]] as $suppliedEntity) {
            try {
                $this->makeService($this->noopReactions(), $repo)->addComment(['text' => 'hello'], 'article', 404, $suppliedEntity);
                $this->fail('a comment on a missing item must raise NotFoundException');
            } catch (\Leantime\Core\Exceptions\NotFoundException) {
                $this->addToAssertionCount(1);
            }
        }
    }

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
        // Valid reaction type so the method reaches the project fence (not the type guard). A denied
        // cross-project comment returns false — same as a missing comment, so no existence oracle.
        $reactions = $this->make(ReactionsService::class, ['getReactionType' => fn () => 'positive']);
        $service = $this->makeService($reactions, $this->defaultRepo(), $this->denyingPermissions());

        $this->assertFalse($service->toggleCommentReaction(self::SESSION_USER, 99, 'thumbsup'));
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

    public function test_get_comment_reactions_is_denied_for_a_foreign_project(): void
    {
        // A denied cross-project comment returns the SAME empty payload as a missing comment
        // (soft-deny), so reactor identities/sentiment never leak AND missing vs unauthorized are
        // indistinguishable — no commentId existence oracle.
        $service = $this->makeService($this->noopReactions(), $this->defaultRepo(), $this->denyingPermissions());

        $this->assertSame(['reactions' => [], 'userReactions' => []], $service->getCommentReactions(99, self::SESSION_USER));
    }

    public function test_get_comment_reactions_returns_empty_for_missing_comment(): void
    {
        // A missing comment yields the same empty payload a DENIED comment does (see above), so the
        // two are indistinguishable — no commentId existence oracle.
        $repo = $this->make(CommentRepository::class, [
            'getComment' => fn () => false,
            'resolveModuleProjectId' => fn () => 9,
        ]);
        $service = $this->makeService($this->noopReactions(), $repo, $this->denyingPermissions());

        $this->assertSame(['reactions' => [], 'userReactions' => []], $service->getCommentReactions(404, self::SESSION_USER));
    }

    // ---------------------------------------------------------------------
    // #3704: structured errors instead of a silent false
    // ---------------------------------------------------------------------

    public function test_add_comment_accepts_the_plural_ticket_module(): void
    {
        session(['userdata.id' => self::SESSION_USER, 'userdata.name' => 'Tester', 'currentProject' => 9]);

        $ticket = new \Leantime\Domain\Tickets\Models\Tickets(['id' => 1, 'projectId' => 9, 'type' => 'task', 'headline' => 'H']);
        $this->app->instance(\Leantime\Domain\Tickets\Services\Tickets::class, $this->make(\Leantime\Domain\Tickets\Services\Tickets::class, [
            'getTicket' => fn () => $ticket,
        ]));

        $writtenModule = null;
        $repo = $this->make(CommentRepository::class, [
            'resolveModuleProjectId' => fn () => 9,
            'addComment' => function ($mapper, $module) use (&$writtenModule) {
                $writtenModule = $module;

                return '503';
            },
        ]);
        $projects = $this->make(ProjectService::class, ['notifyProjectUsers' => fn () => null]);

        $this->assertTrue($this->makeService($this->noopReactions(), $repo, null, $projects)->addComment(['text' => 'hi'], 'tickets', 1));
        $this->assertSame('ticket', $writtenModule);
    }

    public function test_add_comment_rejects_an_unknown_module_with_a_validation_error(): void
    {
        $repo = $this->make(CommentRepository::class, [
            'addComment' => function () {
                throw new \RuntimeException('must not write a comment for an unknown module');
            },
        ]);

        try {
            $this->makeService($this->noopReactions(), $repo)->addComment(['text' => 'hi'], 'bogus', 1);
            $this->fail('an unknown module must not fail silently');
        } catch (\Leantime\Core\Exceptions\ValidationException $e) {
            $this->assertArrayHasKey('module', $e->getErrorData());
            $this->assertStringContainsString('bogus', $e->getClientMessage());
        }
    }

    public function test_add_comment_on_a_missing_ticket_is_not_found(): void
    {
        $this->app->instance(\Leantime\Domain\Tickets\Services\Tickets::class, $this->make(\Leantime\Domain\Tickets\Services\Tickets::class, [
            'getTicket' => fn () => false,
        ]));

        $this->expectException(\Leantime\Core\Exceptions\NotFoundException::class);

        $this->makeService($this->noopReactions())->addComment(['text' => 'hi'], 'ticket', 404);
    }

    public function test_get_comments_accepts_the_same_module_aliases_as_add_comment(): void
    {
        $seen = [];
        $repo = $this->make(CommentRepository::class, [
            'resolveModuleProjectId' => function ($module) use (&$seen) {
                $seen[] = $module;

                return 9;
            },
            'getComments' => function ($module) use (&$seen) {
                $seen[] = $module;

                return [];
            },
        ]);

        $this->makeService($this->noopReactions(), $repo)->getComments('tickets', 1);

        $this->assertSame(['ticket', 'ticket'], $seen);
    }

    public function test_get_comments_reports_a_missing_entity_instead_of_an_empty_thread(): void
    {
        $repo = $this->make(CommentRepository::class, [
            'resolveModuleProjectId' => fn () => null,
            'getComments' => function () {
                throw new \RuntimeException('must not query comments of a missing entity');
            },
        ]);

        $this->expectException(\Leantime\Core\Exceptions\NotFoundException::class);

        $this->makeService($this->noopReactions(), $repo)->getComments('ticket', 404);
    }

    public function test_get_comments_strict_mode_rejects_an_unknown_module(): void
    {
        $this->expectException(\Leantime\Core\Exceptions\ValidationException::class);

        $this->makeService($this->noopReactions())->getComments('bogus', 1, strict: true);
    }

    public function test_get_comments_still_reads_client_and_plugin_modules_for_web_callers(): void
    {
        $repo = $this->make(CommentRepository::class, [
            'resolveModuleProjectId' => fn () => null,
            'getComments' => fn () => [['id' => 1]],
        ]);

        $this->assertSame([['id' => 1]], $this->makeService($this->noopReactions(), $repo)->getComments('client', 3));
    }

    public function test_add_comment_ignores_a_caller_supplied_entity_for_an_unknown_module(): void
    {
        $repo = $this->make(CommentRepository::class, [
            'addComment' => function () {
                throw new \RuntimeException('a fake entity must not unlock writes to an arbitrary module');
            },
        ]);

        $this->expectException(\Leantime\Core\Exceptions\ValidationException::class);

        $this->makeService($this->noopReactions(), $repo)->addComment(['text' => 'hi'], 'secretmodule', 5, ['id' => 5, 'projectId' => 9]);
    }

    public function test_add_comment_to_loaded_entity_keeps_client_comments_working(): void
    {
        session(['userdata.id' => self::SESSION_USER, 'userdata.name' => 'Tester', 'currentProject' => 9]);

        $written = null;
        $repo = $this->make(CommentRepository::class, [
            'resolveModuleProjectId' => fn () => null,
            'addComment' => function ($mapper, $module) use (&$written) {
                $written = [$module, $mapper['moduleId']];

                return '504';
            },
        ]);
        $projects = $this->make(ProjectService::class, ['notifyProjectUsers' => fn () => null]);

        $this->assertTrue($this->makeService($this->noopReactions(), $repo, null, $projects)
            ->addCommentToLoadedEntity(['text' => 'hi'], 'client', 3, ['id' => 3, 'name' => 'ACME']));
        $this->assertSame(['client', 3], $written);
    }

    public function test_strict_get_comments_checks_that_the_project_exists(): void
    {
        $repo = $this->make(CommentRepository::class, [
            'getComments' => function () {
                throw new \RuntimeException('must not query comments of a missing project');
            },
        ]);
        $projects = $this->make(ProjectService::class, ['getProject' => fn () => false]);

        $this->expectException(\Leantime\Core\Exceptions\NotFoundException::class);

        $this->makeService($this->noopReactions(), $repo, null, $projects)->getComments('project', 404, strict: true);
    }
}

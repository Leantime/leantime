<?php

namespace Unit\app\Domain\Ideas\Services;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Ideas\Repositories\Ideas as IdeasRepository;
use Leantime\Domain\Ideas\Services\Ideas as IdeaService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Unit\TestCase;

/**
 * Unit tests for the Ideas service: board/item helpers plus the project-scoped authorization
 * fences. Idea boards (zp_canvas type 'idea') and items (zp_canvas_items) are project-scoped; reads
 * and mutations fence against the entity's REAL project (item -> board -> project), failing closed
 * on the shared canvas tables. The pre-existing userCanAccessCanvasItem checks are migrated onto the
 * permission engine.
 */
class IdeasServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private const SESSION_USER = 5;

    protected function setUp(): void
    {
        parent::setUp();
        session(['userdata.id' => self::SESSION_USER]);
    }

    private function makeService(
        ?IdeasRepository $ideasRepo = null,
        ?CommentRepository $commentsRepo = null,
        ?LanguageCore $language = null,
        ?PermissionService $perms = null,
    ): IdeaService {
        $service = new IdeaService(
            $ideasRepo ?? $this->make(IdeasRepository::class),
            $commentsRepo ?? $this->make(CommentRepository::class),
            $this->make(ProjectService::class),
            $this->make(TicketService::class),
            $language ?? $this->make(LanguageCore::class),
        );
        $service->setPermissionService($perms ?? $this->allowingPermissions());

        return $service;
    }

    /** A repo whose board #3 / item #5 both resolve to project 9. */
    private function ideaRepoInProject9(array $overrides = []): IdeasRepository
    {
        return $this->make(IdeasRepository::class, array_merge([
            'getSingleCanvas' => fn () => [['id' => 3, 'projectId' => 9, 'title' => 'Board']],
            'getSingleCanvasItem' => fn () => ['id' => 5, 'canvasId' => 3, 'box' => 'idea'],
        ], $overrides));
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

    // ---------------------------------------------------------------------
    // Item factory / normalization (behavioural).
    // ---------------------------------------------------------------------

    public function test_get_idea_item_returns_empty_default_for_null_id(): void
    {
        $item = $this->makeService()->getIdeaItem(null, 'research');

        $this->assertSame('', $item['id']);
        $this->assertSame('research', $item['box']);
        $this->assertSame('idea', $item['status']);
        $this->assertSame('', $item['milestoneId']);
    }

    public function test_get_idea_item_defaults_type_to_idea(): void
    {
        $this->assertSame('idea', $this->makeService()->getIdeaItem(null)['box']);
    }

    public function test_get_idea_item_normalizes_zero_box_to_idea(): void
    {
        $repo = $this->ideaRepoInProject9([
            'getSingleCanvasItem' => fn () => ['id' => 5, 'box' => '0', 'canvasId' => 3, 'description' => 'x'],
        ]);

        $item = $this->makeService(ideasRepo: $repo)->getIdeaItem(5);

        $this->assertSame('idea', $item['box']);
        $this->assertSame(5, $item['id']);
    }

    public function test_get_idea_item_keeps_non_zero_box(): void
    {
        $repo = $this->ideaRepoInProject9([
            'getSingleCanvasItem' => fn () => ['id' => 7, 'box' => 'prototype', 'canvasId' => 3],
        ]);

        $this->assertSame('prototype', $this->makeService(ideasRepo: $repo)->getIdeaItem(7)['box']);
    }

    public function test_ensure_board_exists_returns_zero_when_boards_present(): void
    {
        $addCalls = 0;
        $repo = $this->make(IdeasRepository::class, [
            'addCanvas' => function () use (&$addCalls) {
                $addCalls++;

                return '99';
            },
        ]);

        $this->assertSame(0, $this->makeService(ideasRepo: $repo)->ensureBoardExists(1, 2, [['id' => 10]]));
        $this->assertSame(0, $addCalls, 'No board should be created when one already exists');
    }

    public function test_ensure_board_exists_creates_default_when_none(): void
    {
        $repo = $this->make(IdeasRepository::class, ['addCanvas' => fn () => '99']);
        $language = $this->make(LanguageCore::class, ['__' => fn () => 'Board']);

        $this->assertSame(99, $this->makeService(ideasRepo: $repo, language: $language)->ensureBoardExists(1, 2, []));
    }

    public function test_get_all_boards_normalizes_false_to_empty_array(): void
    {
        $repo = $this->make(IdeasRepository::class, ['getAllCanvas' => fn () => false]);

        $this->assertSame([], $this->makeService(ideasRepo: $repo)->getAllBoards(1));
    }

    public function test_get_board_items_normalizes_false_to_empty_array(): void
    {
        $repo = $this->ideaRepoInProject9(['getCanvasItemsById' => fn () => false]);

        $this->assertSame([], $this->makeService(ideasRepo: $repo)->getBoardItems(3));
    }

    public function test_get_board_title_returns_empty_when_not_found(): void
    {
        $repo = $this->make(IdeasRepository::class, ['getSingleCanvas' => fn () => false]);

        $this->assertSame('', $this->makeService(ideasRepo: $repo)->getBoardTitle(123));
    }

    public function test_get_board_title_returns_first_row_title(): void
    {
        $repo = $this->make(IdeasRepository::class, [
            'getSingleCanvas' => fn () => [['id' => 1, 'title' => 'My Board', 'projectId' => 9]],
        ]);

        $this->assertSame('My Board', $this->makeService(ideasRepo: $repo)->getBoardTitle(1));
    }

    // ---------------------------------------------------------------------
    // Read fences (single-entity-by-id).
    // ---------------------------------------------------------------------

    public function test_get_board_is_denied_for_a_foreign_project(): void
    {
        $service = $this->makeService(ideasRepo: $this->ideaRepoInProject9(), perms: $this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->getBoard(3);
    }

    public function test_get_board_items_is_denied_for_a_foreign_project(): void
    {
        $service = $this->makeService(ideasRepo: $this->ideaRepoInProject9(), perms: $this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->getBoardItems(3);
    }

    // ---------------------------------------------------------------------
    // Mutation fences (fail closed + project-scoped).
    // ---------------------------------------------------------------------

    public function test_patch_idea_item_is_denied_without_edit(): void
    {
        $service = $this->makeService(ideasRepo: $this->ideaRepoInProject9(), perms: $this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->patchIdeaItem(5, ['box' => 'done']);
    }

    public function test_patch_idea_item_allowed_with_edit(): void
    {
        $repo = $this->ideaRepoInProject9(['patchCanvasItem' => fn () => true]);

        $this->assertTrue($this->makeService(ideasRepo: $repo)->patchIdeaItem(5, ['box' => 'done']));
    }

    public function test_patch_idea_item_fails_closed_for_unknown_item(): void
    {
        $patched = false;
        $repo = $this->make(IdeasRepository::class, [
            'getSingleCanvasItem' => fn () => false,
            'patchCanvasItem' => function () use (&$patched): bool {
                $patched = true;

                return true;
            },
        ]);

        $this->assertFalse($this->makeService(ideasRepo: $repo)->patchIdeaItem(999, ['box' => 'done']));
        $this->assertFalse($patched, 'A non-idea/unknown id must never reach the repo patch');
    }

    public function test_update_idea_item_fails_closed_for_unknown_item(): void
    {
        $edited = false;
        $repo = $this->make(IdeasRepository::class, [
            'getSingleCanvasItem' => fn () => false,
            'editCanvasItem' => function () use (&$edited): void {
                $edited = true;
            },
        ]);

        $input = ['itemId' => 999, 'box' => 'idea', 'description' => 'x', 'status' => 'idea', 'data' => '', 'tags' => '', 'canvasId' => 3, 'milestoneId' => ''];

        $this->assertSame(0, $this->makeService(ideasRepo: $repo)->updateIdeaItem($input, 9, self::SESSION_USER));
        $this->assertFalse($edited, 'A non-idea/unknown id must never reach the repo edit');
    }

    public function test_update_idea_item_is_denied_without_edit(): void
    {
        $service = $this->makeService(ideasRepo: $this->ideaRepoInProject9(), perms: $this->denyingPermissions());
        $input = ['itemId' => 5, 'box' => 'idea', 'description' => 'x', 'status' => 'idea', 'data' => '', 'tags' => '', 'canvasId' => 3, 'milestoneId' => ''];

        $this->expectException(AuthorizationException::class);

        $service->updateIdeaItem($input, 9, self::SESSION_USER);
    }

    public function test_create_idea_item_is_denied_without_create(): void
    {
        $service = $this->makeService(ideasRepo: $this->ideaRepoInProject9(), perms: $this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->createIdeaItem(['box' => 'idea', 'description' => 'x', 'status' => 'idea', 'data' => '', 'canvasId' => 3], 9, self::SESSION_USER);
    }

    public function test_create_board_is_denied_without_create(): void
    {
        $service = $this->makeService(perms: $this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->createBoard('New board', 9, self::SESSION_USER);
    }

    public function test_update_board_is_denied_without_edit(): void
    {
        $service = $this->makeService(ideasRepo: $this->ideaRepoInProject9(), perms: $this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->updateBoard(3, 'Renamed');
    }

    public function test_update_board_fails_closed_for_unknown_board(): void
    {
        $updated = false;
        $repo = $this->make(IdeasRepository::class, [
            'getSingleCanvas' => fn () => [],
            'updateCanvas' => function () use (&$updated) {
                $updated = true;

                return 1;
            },
        ]);

        $this->assertFalse($this->makeService(ideasRepo: $repo)->updateBoard(999, 'Renamed'));
        $this->assertFalse($updated, 'A non-idea/unknown board id must never reach the repo update');
    }

    public function test_delete_canvas_is_denied_and_does_not_delete(): void
    {
        $repo = $this->ideaRepoInProject9([
            'deleteCanvas' => function (): void {
                throw new \RuntimeException('delete must not run when denied');
            },
        ]);
        $service = $this->makeService(ideasRepo: $repo, perms: $this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->deleteCanvas(3);
    }

    public function test_delete_canvas_item_is_denied_and_does_not_delete(): void
    {
        $repo = $this->ideaRepoInProject9([
            'delCanvasItem' => function (): void {
                throw new \RuntimeException('delete must not run when denied');
            },
        ]);
        $service = $this->makeService(ideasRepo: $repo, perms: $this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->deleteCanvasItem(5);
    }

    // ---------------------------------------------------------------------
    // Batch mutators reject (return false) without writing.
    // ---------------------------------------------------------------------

    public function test_reorder_ideas_rejects_batch_when_cannot_edit(): void
    {
        $sorted = false;
        $repo = $this->ideaRepoInProject9([
            'updateIdeaSorting' => function () use (&$sorted): bool {
                $sorted = true;

                return true;
            },
        ]);
        $service = $this->makeService(ideasRepo: $repo, perms: $this->denyingPermissions());

        $this->assertFalse($service->reorderIdeas([['id' => 5, 'sortIndex' => 1]]));
        $this->assertFalse($sorted, 'A denied batch must never reach the repo sort');
    }

    public function test_bulk_update_status_rejects_batch_when_cannot_edit(): void
    {
        $repo = $this->ideaRepoInProject9(['bulkUpdateIdeaStatus' => fn () => true]);
        $service = $this->makeService(ideasRepo: $repo, perms: $this->denyingPermissions());

        $this->assertFalse($service->bulkUpdateStatus(['done' => 'item[]=5']));
    }

    // ---------------------------------------------------------------------
    // Comment delete: author allowed; non-author requires moderation.
    // ---------------------------------------------------------------------

    public function test_remove_idea_comment_allows_the_author_without_moderation(): void
    {
        $deleted = null;
        $commentsRepo = $this->make(CommentRepository::class, [
            'getComment' => fn () => ['id' => 1, 'userId' => self::SESSION_USER, 'moduleId' => 5],
            'deleteComment' => function ($id) use (&$deleted): bool {
                $deleted = $id;

                return true;
            },
        ]);
        // Denying engine proves the author path does NOT require comments.moderate.
        $service = $this->makeService(ideasRepo: $this->ideaRepoInProject9(), commentsRepo: $commentsRepo, perms: $this->denyingPermissions());

        $service->removeIdeaComment(1);

        $this->assertSame(1, $deleted);
    }

    public function test_remove_idea_comment_denies_non_author_without_moderation(): void
    {
        $commentsRepo = $this->make(CommentRepository::class, [
            'getComment' => fn () => ['id' => 1, 'userId' => 7, 'moduleId' => 5],
            'deleteComment' => function (): bool {
                throw new \RuntimeException('delete must not run when moderation is denied');
            },
        ]);
        $service = $this->makeService(ideasRepo: $this->ideaRepoInProject9(), commentsRepo: $commentsRepo, perms: $this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->removeIdeaComment(1);
    }

    // ---------------------------------------------------------------------
    // Fail-closed on a non-resolving (non-idea) id: never authorize against a null project, never
    // fall back to the caller-supplied project. (A null project here means "not an idea entity".)
    // ---------------------------------------------------------------------

    public function test_get_idea_comments_fails_closed_for_non_idea_entity(): void
    {
        $fetched = false;
        $repo = $this->make(IdeasRepository::class, ['getSingleCanvasItem' => fn () => false]);
        $commentsRepo = $this->make(CommentRepository::class, [
            'getComments' => function () use (&$fetched) {
                $fetched = true;

                return [];
            },
        ]);
        // Denying engine: if it reached authorize(VIEW, null) it would throw; fail-closed returns [] first.
        $service = $this->makeService(ideasRepo: $repo, commentsRepo: $commentsRepo, perms: $this->denyingPermissions());

        $this->assertSame([], $service->getIdeaComments('ticket', 123));
        $this->assertFalse($fetched, 'A non-idea entity must short-circuit before the comment read');
    }

    public function test_create_idea_item_fails_closed_for_non_idea_board(): void
    {
        $created = false;
        $repo = $this->make(IdeasRepository::class, [
            'getSingleCanvas' => fn () => [], // canvasId is not an idea board
            'addCanvasItem' => function () use (&$created) {
                $created = true;

                return '1';
            },
        ]);
        $service = $this->makeService(ideasRepo: $repo);

        $this->assertSame(0, $service->createIdeaItem(['box' => 'idea', 'description' => 'x', 'status' => 'idea', 'data' => '', 'canvasId' => 999], 9, self::SESSION_USER));
        $this->assertFalse($created, 'A non-idea board canvasId must never create an item against the caller project');
    }

    public function test_add_idea_comment_fails_closed_for_non_idea_item(): void
    {
        $added = false;
        $repo = $this->make(IdeasRepository::class, ['getSingleCanvasItem' => fn () => false]);
        $commentsRepo = $this->make(CommentRepository::class, [
            'addComment' => function () use (&$added) {
                $added = true;

                return '1';
            },
        ]);
        $service = $this->makeService(ideasRepo: $repo, commentsRepo: $commentsRepo);

        $this->assertFalse($service->addIdeaComment('hi', 999, 0, 9, self::SESSION_USER));
        $this->assertFalse($added, 'A non-idea item must never receive a comment against the caller project');
    }

    public function test_remove_idea_comment_fails_closed_for_non_author_non_idea_comment(): void
    {
        $deleted = false;
        $repo = $this->make(IdeasRepository::class, ['getSingleCanvasItem' => fn () => false]);
        $commentsRepo = $this->make(CommentRepository::class, [
            'getComment' => fn () => ['id' => 1, 'userId' => 7, 'moduleId' => 999],
            'deleteComment' => function () use (&$deleted): bool {
                $deleted = true;

                return true;
            },
        ]);
        // Allowing engine: proves the refusal is the fail-closed null guard, not a denied authorize.
        $service = $this->makeService(ideasRepo: $repo, commentsRepo: $commentsRepo, perms: $this->allowingPermissions());

        $service->removeIdeaComment(1);

        $this->assertFalse($deleted, 'A non-author comment on a non-idea item must not be deleted');
    }

    // ---------------------------------------------------------------------
    // Relocation / mass-assignment fences (Copilot review): the incoming canvasId/params can move
    // an item to another board, so the target board's project must also be authorized.
    // ---------------------------------------------------------------------

    public function test_patch_idea_item_strips_relocation_and_identity_fields(): void
    {
        // patchCanvasItem updates any column it receives; canvasId/id/author must be stripped so a
        // caller can't relocate the item to another board/project or rewrite its identity.
        $patched = null;
        $repo = $this->ideaRepoInProject9([
            'patchCanvasItem' => function ($id, $params) use (&$patched): bool {
                $patched = $params;

                return true;
            },
        ]);
        $service = $this->makeService(ideasRepo: $repo);

        $service->patchIdeaItem(5, ['status' => 'done', 'canvasId' => 999, 'id' => 1, 'author' => 7]);

        $this->assertArrayNotHasKey('canvasId', $patched);
        $this->assertArrayNotHasKey('id', $patched);
        $this->assertArrayNotHasKey('author', $patched);
        $this->assertSame('done', $patched['status'], 'Legitimate fields still pass through');
    }

    public function test_update_idea_item_denies_relocation_to_a_foreign_board(): void
    {
        // Item lives in project 9 (board 3); the edit's canvasId points at board 99 in project 7.
        // The user may edit project 9 but NOT project 7 -> the relocation is denied before the write.
        $repo = $this->make(IdeasRepository::class, [
            'getSingleCanvasItem' => fn () => ['id' => 5, 'canvasId' => 3, 'box' => 'idea'],
            'getSingleCanvas' => fn ($id) => $id === 99 ? [['projectId' => 7]] : [['projectId' => 9]],
            'editCanvasItem' => function (): void {
                throw new \RuntimeException('relocation must be blocked before the write');
            },
        ]);
        $perms = $this->make(PermissionService::class, [
            'authorize' => function (string $p, ?int $projectId = null): void {
                if ($projectId === 7) {
                    throw new AuthorizationException;
                }
            },
            'currentUserCan' => fn () => true,
        ]);
        $service = $this->makeService(ideasRepo: $repo, perms: $perms);

        $this->expectException(AuthorizationException::class);

        $service->updateIdeaItem(['itemId' => 5, 'box' => 'idea', 'description' => 'x', 'status' => 'idea', 'data' => '', 'tags' => '', 'canvasId' => 99, 'milestoneId' => ''], 9, self::SESSION_USER);
    }

    public function test_update_idea_item_fails_closed_when_target_board_is_not_an_idea_board(): void
    {
        $edited = false;
        $repo = $this->make(IdeasRepository::class, [
            'getSingleCanvasItem' => fn () => ['id' => 5, 'canvasId' => 3, 'box' => 'idea'],
            'getSingleCanvas' => fn ($id) => $id === 3 ? [['projectId' => 9]] : [], // target 99 -> not an idea board
            'editCanvasItem' => function () use (&$edited): void {
                $edited = true;
            },
        ]);
        $service = $this->makeService(ideasRepo: $repo);

        $this->assertSame(0, $service->updateIdeaItem(['itemId' => 5, 'box' => 'idea', 'description' => 'x', 'status' => 'idea', 'data' => '', 'tags' => '', 'canvasId' => 99, 'milestoneId' => ''], 9, self::SESSION_USER));
        $this->assertFalse($edited, 'A non-idea target board must never receive the relocated item');
    }
}

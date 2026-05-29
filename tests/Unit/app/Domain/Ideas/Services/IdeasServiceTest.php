<?php

namespace Unit\app\Domain\Ideas\Services;

use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Ideas\Repositories\Ideas as IdeasRepository;
use Leantime\Domain\Ideas\Services\Ideas as IdeaService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Unit\TestCase;

/**
 * Unit tests for the Ideas service helpers extracted during the
 * thin-controller refactor (board listing, item factory/normalization,
 * default-board creation).
 */
class IdeasServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Builds a real Ideas service, allowing each dependency to be
     * overridden with a stub so we can observe persistence/orchestration calls.
     */
    private function makeService(
        ?IdeasRepository $ideasRepo = null,
        ?CommentRepository $commentsRepo = null,
        ?ProjectService $projectService = null,
        ?TicketService $ticketService = null,
        ?LanguageCore $language = null,
    ): IdeaService {
        return new IdeaService(
            $ideasRepo ?? $this->make(IdeasRepository::class),
            $commentsRepo ?? $this->make(CommentRepository::class),
            $projectService ?? $this->make(ProjectService::class),
            $ticketService ?? $this->make(TicketService::class),
            $language ?? $this->make(LanguageCore::class),
        );
    }

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
        $item = $this->makeService()->getIdeaItem(null);

        $this->assertSame('idea', $item['box']);
    }

    public function test_get_idea_item_normalizes_zero_box_to_idea(): void
    {
        $repo = $this->make(IdeasRepository::class, [
            'getSingleCanvasItem' => fn () => ['id' => 5, 'box' => '0', 'description' => 'x'],
        ]);

        $item = $this->makeService(ideasRepo: $repo)->getIdeaItem(5);

        $this->assertSame('idea', $item['box']);
        $this->assertSame(5, $item['id']);
    }

    public function test_get_idea_item_keeps_non_zero_box(): void
    {
        $repo = $this->make(IdeasRepository::class, [
            'getSingleCanvasItem' => fn () => ['id' => 7, 'box' => 'prototype'],
        ]);

        $item = $this->makeService(ideasRepo: $repo)->getIdeaItem(7);

        $this->assertSame('prototype', $item['box']);
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

        $result = $this->makeService(ideasRepo: $repo)
            ->ensureBoardExists(1, 2, [['id' => 10]]);

        $this->assertSame(0, $result);
        $this->assertSame(0, $addCalls, 'No board should be created when one already exists');
    }

    public function test_ensure_board_exists_creates_default_when_none(): void
    {
        $repo = $this->make(IdeasRepository::class, [
            'addCanvas' => fn () => '99',
        ]);
        $language = $this->make(LanguageCore::class, [
            '__' => fn () => 'Board',
        ]);

        $result = $this->makeService(ideasRepo: $repo, language: $language)
            ->ensureBoardExists(1, 2, []);

        $this->assertSame(99, $result);
    }

    public function test_get_all_boards_normalizes_false_to_empty_array(): void
    {
        $repo = $this->make(IdeasRepository::class, [
            'getAllCanvas' => fn () => false,
        ]);

        $this->assertSame([], $this->makeService(ideasRepo: $repo)->getAllBoards(1));
    }

    public function test_get_board_items_normalizes_false_to_empty_array(): void
    {
        $repo = $this->make(IdeasRepository::class, [
            'getCanvasItemsById' => fn () => false,
        ]);

        $this->assertSame([], $this->makeService(ideasRepo: $repo)->getBoardItems(5));
    }

    public function test_get_board_title_returns_empty_when_not_found(): void
    {
        $repo = $this->make(IdeasRepository::class, [
            'getSingleCanvas' => fn () => false,
        ]);

        $this->assertSame('', $this->makeService(ideasRepo: $repo)->getBoardTitle(123));
    }

    public function test_get_board_title_returns_first_row_title(): void
    {
        $repo = $this->make(IdeasRepository::class, [
            'getSingleCanvas' => fn () => [['id' => 1, 'title' => 'My Board']],
        ]);

        $this->assertSame('My Board', $this->makeService(ideasRepo: $repo)->getBoardTitle(1));
    }

    // ---------------------------------------------------------------------
    // JSON-RPC authorization gates (editor + per-item project access).
    // ---------------------------------------------------------------------

    /**
     * Repo stub whose canvas item resolves to project 9.
     */
    private function repoForProject9(array $extra = []): IdeasRepository
    {
        return $this->make(IdeasRepository::class, array_merge([
            'getSingleCanvasItem' => fn () => ['canvasId' => 7],
            'getSingleCanvas' => fn () => ['projectId' => 9],
            'patchCanvasItem' => fn () => true,
            'updateIdeaSorting' => fn () => true,
            'bulkUpdateIdeaStatus' => fn () => true,
        ], $extra));
    }

    public function test_patch_idea_item_denied_for_non_editor(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'readonly']]);

        $service = $this->makeService(ideasRepo: $this->repoForProject9());

        $this->assertFalse($service->patchIdeaItem(5, ['box' => 'done']));
    }

    public function test_patch_idea_item_denied_when_not_assigned_to_project(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'editor']]);

        $projectService = $this->make(ProjectService::class, [
            'isUserAssignedToProject' => fn () => false,
        ]);

        $service = $this->makeService(ideasRepo: $this->repoForProject9(), projectService: $projectService);

        $this->assertFalse($service->patchIdeaItem(5, ['box' => 'done']));
    }

    public function test_patch_idea_item_allowed_for_editor_with_project_access(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'editor']]);

        $projectService = $this->make(ProjectService::class, [
            'isUserAssignedToProject' => fn () => true,
        ]);

        $service = $this->makeService(ideasRepo: $this->repoForProject9(), projectService: $projectService);

        $this->assertTrue($service->patchIdeaItem(5, ['box' => 'done']));
    }

    public function test_reorder_ideas_denied_for_non_editor(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'readonly']]);

        $service = $this->makeService(ideasRepo: $this->repoForProject9());

        $this->assertFalse($service->reorderIdeas([['id' => 5, 'sortIndex' => 1]]));
    }

    public function test_bulk_update_status_denied_for_non_editor(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'readonly']]);

        $service = $this->makeService(ideasRepo: $this->repoForProject9());

        $this->assertFalse($service->bulkUpdateStatus(['done' => 'item[]=5']));
    }
}

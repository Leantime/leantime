<?php

namespace Unit\app\Domain\Blueprints\Services;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Leantime\Domain\Blueprints\Repositories\Blueprints as BlueprintsRepository;
use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;
use Leantime\Domain\Blueprints\Services\TemplateRegistry;
use Unit\TestCase;

/**
 * Unit tests for the Blueprints service: label translation helpers and the
 * board-progress calculation (filled boxes / total boxes, max across boards).
 */
class BlueprintsServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Build the service with a language stub that prefixes keys with "T:" so we
     * can assert translation happened, plus optional repo/registry overrides.
     */
    private function service(?BlueprintsRepository $repo = null, ?TemplateRegistry $registry = null): BlueprintsService
    {
        $language = $this->make(LanguageCore::class, ['__' => fn (string $index) => 'T:'.$index]);

        return new BlueprintsService(
            $repo ?? $this->make(BlueprintsRepository::class),
            $registry ?? new TemplateRegistry,
            $language,
        );
    }

    public function test_translated_boxes_run_titles_through_language(): void
    {
        $template = new CanvasTemplate([
            'slug' => 'swot',
            'boxes' => ['swot_strengths' => ['icon' => 'fa-x', 'title' => 'box.swot.strengths']],
        ]);

        $boxes = $this->service()->getTranslatedBoxes($template);

        $this->assertSame('T:box.swot.strengths', $boxes['swot_strengths']['title']);
        $this->assertSame('fa-x', $boxes['swot_strengths']['icon']);
    }

    public function test_translates_status_relates_and_data_labels(): void
    {
        $service = $this->service();
        $template = new CanvasTemplate(['slug' => 'x']); // base defaults

        $this->assertSame('T:status.draft', $service->getTranslatedStatusLabels($template)['status_draft']['title']);
        $this->assertSame('T:relates.none', $service->getTranslatedRelatesLabels($template)['relates_none']['title']);
        $this->assertSame('T:label.assumptions', $service->getTranslatedDataLabels($template)[1]['title']);
    }

    public function test_disclaimer_is_empty_when_unset_and_translated_otherwise(): void
    {
        $service = $this->service();

        $this->assertSame('', $service->getTranslatedDisclaimer(new CanvasTemplate(['slug' => 'x'])));
        $this->assertSame(
            'T:text.lean.disclaimer',
            $service->getTranslatedDisclaimer(new CanvasTemplate(['slug' => 'lean', 'disclaimer' => 'text.lean.disclaimer']))
        );
    }

    public function test_board_progress_is_fraction_of_filled_boxes(): void
    {
        // SWOT has 4 boxes; board 1 has 2 boxes with items -> 0.5.
        $repo = $this->make(BlueprintsRepository::class, [
            'getCanvasProgressCount' => fn () => [
                ['canvasType' => 'swotcanvas', 'canvasId' => 1, 'box' => 'swot_strengths', 'boxItems' => 3],
                ['canvasType' => 'swotcanvas', 'canvasId' => 1, 'box' => 'swot_threats', 'boxItems' => 1],
                ['canvasType' => 'swotcanvas', 'canvasId' => 1, 'box' => 'swot_weaknesses', 'boxItems' => 0],
            ],
        ]);

        $progress = $this->service($repo)->getBoardProgress('1', ['swotcanvas']);

        $this->assertEqualsWithDelta(0.5, $progress['swotcanvas'], 0.001);
    }

    public function test_board_progress_takes_max_across_boards(): void
    {
        // Board 2 has all 4 SWOT boxes filled -> max progress 1.0.
        $repo = $this->make(BlueprintsRepository::class, [
            'getCanvasProgressCount' => fn () => [
                ['canvasType' => 'swotcanvas', 'canvasId' => 1, 'box' => 'swot_strengths', 'boxItems' => 1],
                ['canvasType' => 'swotcanvas', 'canvasId' => 2, 'box' => 'swot_strengths', 'boxItems' => 1],
                ['canvasType' => 'swotcanvas', 'canvasId' => 2, 'box' => 'swot_weaknesses', 'boxItems' => 1],
                ['canvasType' => 'swotcanvas', 'canvasId' => 2, 'box' => 'swot_opportunities', 'boxItems' => 1],
                ['canvasType' => 'swotcanvas', 'canvasId' => 2, 'box' => 'swot_threats', 'boxItems' => 1],
            ],
        ]);

        $progress = $this->service($repo)->getBoardProgress('1', ['swotcanvas']);

        $this->assertEqualsWithDelta(1.0, $progress['swotcanvas'], 0.001);
    }

    // ---------------------------------------------------------------------
    // Boards overview (absorbed from the former Strategy service).
    // ---------------------------------------------------------------------

    public function test_build_recent_progress_seeds_metadata_and_removes_used_type(): void
    {
        $service = $this->service();

        $metadata = $service->getBoardMetadata();

        $recentlyUpdated = [
            ['type' => 'valuecanvas', 'title' => 'My Value Board', 'modified' => '2026-05-20 10:00:00', 'id' => 11],
        ];

        $result = $service->buildRecentProgressCanvas($recentlyUpdated, $metadata);

        $this->assertArrayHasKey('valuecanvas', $result);
        $this->assertSame(1, $result['valuecanvas']['count']);
        $this->assertSame('My Value Board', $result['valuecanvas']['lastTitle']);
        $this->assertSame('2026-05-20 10:00:00', $result['valuecanvas']['lastUpdate']);
        $this->assertSame(11, $result['valuecanvas']['lastCanvasId']);
        // Board links point at the consolidated Blueprints routes.
        $this->assertSame('blueprints/value', $result['valuecanvas']['module']);

        // The consumed type must be removed from the remaining "other" boards map.
        $this->assertArrayNotHasKey('valuecanvas', $metadata);
        $this->assertArrayHasKey('swotcanvas', $metadata);
    }

    public function test_build_recent_progress_increments_count_for_repeat_type(): void
    {
        $service = $this->service();

        $metadata = $service->getBoardMetadata();

        $recentlyUpdated = [
            ['type' => 'swotcanvas', 'title' => 'First', 'modified' => '2026-05-21 09:00:00', 'id' => 1],
            ['type' => 'swotcanvas', 'title' => 'Second', 'modified' => '2026-05-22 09:00:00', 'id' => 2],
            ['type' => 'swotcanvas', 'title' => 'Third', 'modified' => '2026-05-23 09:00:00', 'id' => 3],
        ];

        $result = $service->buildRecentProgressCanvas($recentlyUpdated, $metadata);

        $this->assertSame(3, $result['swotcanvas']['count']);
        // The seeded values come from the FIRST occurrence only.
        $this->assertSame('First', $result['swotcanvas']['lastTitle']);
        $this->assertSame(1, $result['swotcanvas']['lastCanvasId']);
    }

    public function test_build_recent_progress_with_empty_input_returns_empty(): void
    {
        $service = $this->service();

        $metadata = $service->getBoardMetadata();
        $metadataCountBefore = count($metadata);

        $result = $service->buildRecentProgressCanvas([], $metadata);

        $this->assertSame([], $result);
        // Nothing consumed, so the metadata map is untouched.
        $this->assertCount($metadataCountBefore, $metadata);
    }

    public function test_boards_overview_assembles_render_ready_struct(): void
    {
        $recentlyUpdated = [
            ['type' => 'leancanvas', 'title' => 'Lean A', 'modified' => '2026-05-25 12:00:00', 'id' => 99],
        ];
        $progress = ['leancanvas' => 0.5];

        // getBoardsOverview now self-calls getLastUpdatedCanvas()/getBoardProgress(),
        // so partial-mock just those two and exercise the real assembly logic.
        $service = $this->make(BlueprintsService::class, [
            'getLastUpdatedCanvas' => fn () => $recentlyUpdated,
            'getBoardProgress' => fn () => $progress,
        ]);

        $overview = $service->getBoardsOverview(7);

        $this->assertArrayHasKey('recentProgressCanvas', $overview);
        $this->assertArrayHasKey('otherBoards', $overview);
        $this->assertArrayHasKey('recentlyUpdatedCanvas', $overview);
        $this->assertArrayHasKey('canvasProgress', $overview);

        $this->assertSame($recentlyUpdated, $overview['recentlyUpdatedCanvas']);
        $this->assertSame($progress, $overview['canvasProgress']);

        // leancanvas was recently used, so it lands in recentProgressCanvas
        // and is removed from the remaining "other" boards.
        $this->assertArrayHasKey('leancanvas', $overview['recentProgressCanvas']);
        $this->assertSame('Lean A', $overview['recentProgressCanvas']['leancanvas']['lastTitle']);
        $this->assertArrayNotHasKey('leancanvas', $overview['otherBoards']);
    }

    public function test_boards_overview_passes_project_id_to_self_calls(): void
    {
        $capturedLastUpdatedId = null;
        $capturedProgressId = null;

        $service = $this->make(BlueprintsService::class, [
            'getLastUpdatedCanvas' => function ($projectId) use (&$capturedLastUpdatedId) {
                $capturedLastUpdatedId = $projectId;

                return [];
            },
            'getBoardProgress' => function ($projectId) use (&$capturedProgressId) {
                $capturedProgressId = $projectId;

                return [];
            },
        ]);

        $service->getBoardsOverview(7);

        $this->assertSame(7, $capturedLastUpdatedId);
        $this->assertSame('7', $capturedProgressId, 'getBoardProgress receives the project id cast to string');
    }

    // ---------------------------------------------------------------------
    // Secured by-id board/item CRUD chokepoint.
    //
    // Canvas boards/items live in the shared zp_canvas / zp_canvas_items tables (one id
    // sequence across every variant). Every by-id operation must authorize against the
    // entity's REAL project (resolved by id + canvas type), never the session project. Reads
    // soft-deny (return the neutral "missing" value) so they are not a cross-project existence
    // oracle; writes fail CLOSED with an AuthorizationException and never touch the repo.
    // ---------------------------------------------------------------------

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

    private function securedService(BlueprintsRepository $repo, PermissionService $perms): BlueprintsService
    {
        $service = $this->service($repo);
        $service->setPermissionService($perms);

        return $service;
    }

    public function test_get_canvas_item_returns_false_for_missing_or_foreign_item_without_loading_it(): void
    {
        // Resolver null = missing id OR an id whose board is a different canvas type. Must
        // return false WITHOUT loading the item — no cross-project existence oracle.
        $loaded = 0;
        $repo = $this->make(BlueprintsRepository::class, [
            'getCanvasItemProjectId' => fn () => null,
            'getSingleCanvasItem' => function () use (&$loaded) {
                $loaded++;

                return ['id' => 1];
            },
        ]);
        $service = $this->securedService($repo, $this->allowingPermissions());

        $this->assertFalse($service->getCanvasItem(123, 'swotcanvas'));
        $this->assertSame(0, $loaded, 'A missing/foreign item must not be loaded');
    }

    public function test_get_canvas_item_soft_denies_when_view_not_permitted(): void
    {
        $loaded = 0;
        $repo = $this->make(BlueprintsRepository::class, [
            'getCanvasItemProjectId' => fn () => 9,
            'getSingleCanvasItem' => function () use (&$loaded) {
                $loaded++;

                return ['id' => 1];
            },
        ]);
        $service = $this->securedService($repo, $this->make(PermissionService::class, ['currentUserCan' => fn () => false]));

        $this->assertFalse($service->getCanvasItem(1, 'swotcanvas'));
        $this->assertSame(0, $loaded, 'An unauthorized item returns the same neutral result as a missing one');
    }

    public function test_get_canvas_item_is_type_scoped_and_returns_item_when_authorized(): void
    {
        $resolvedType = null;
        $repo = $this->make(BlueprintsRepository::class, [
            'getCanvasItemProjectId' => function ($id, $type) use (&$resolvedType) {
                $resolvedType = $type;

                return 9;
            },
            'getSingleCanvasItem' => fn () => ['id' => 7, 'canvasId' => 3],
        ]);
        $service = $this->securedService($repo, $this->make(PermissionService::class, ['currentUserCan' => fn () => true]));

        $item = $service->getCanvasItem(7, 'swotcanvas');

        $this->assertSame(7, $item['id']);
        $this->assertSame('swotcanvas', $resolvedType, 'The resolver must be type-scoped so a foreign canvas type cannot match');
    }

    public function test_get_board_items_returns_empty_for_foreign_board(): void
    {
        $loaded = 0;
        $repo = $this->make(BlueprintsRepository::class, [
            'getCanvasProjectId' => fn () => null,
            'getCanvasItemsById' => function () use (&$loaded) {
                $loaded++;

                return [['id' => 1]];
            },
        ]);
        $service = $this->securedService($repo, $this->allowingPermissions());

        $this->assertSame([], $service->getBoardItems(999, 'swotcanvas', 'swotcanvasitem'));
        $this->assertSame(0, $loaded, 'A foreign/unknown board must not have its items read');
    }

    public function test_patch_canvas_item_throws_and_never_writes_for_unresolved_item(): void
    {
        $patched = 0;
        $repo = $this->make(BlueprintsRepository::class, [
            'getCanvasItemProjectId' => fn () => null,
            'patchCanvasItem' => function () use (&$patched) {
                $patched++;

                return true;
            },
        ]);
        // allow-all permissions: the deny must come from the null resolution, not the role.
        $service = $this->securedService($repo, $this->allowingPermissions());

        try {
            $service->patchCanvasItem(5, ['status' => 'x'], 'swotcanvas');
            $this->fail('Expected AuthorizationException for an unresolved item');
        } catch (AuthorizationException) {
            // expected
        }
        $this->assertSame(0, $patched, 'A missing/foreign item must never be patched');
    }

    public function test_patch_canvas_item_throws_when_edit_denied(): void
    {
        $repo = $this->make(BlueprintsRepository::class, [
            'getCanvasItemProjectId' => fn () => 9,
            'patchCanvasItem' => fn () => true,
        ]);
        $service = $this->securedService($repo, $this->denyingPermissions());

        $this->expectException(AuthorizationException::class);
        $service->patchCanvasItem(5, ['status' => 'x'], 'swotcanvas');
    }

    public function test_update_canvas_item_resolves_project_from_item_id_not_payload_canvas_id(): void
    {
        // Relocation fence: the project is resolved from the EXISTING item's id, not from the
        // attacker-supplied canvasId in the payload.
        $resolvedItemId = null;
        $wrote = 0;
        $repo = $this->make(BlueprintsRepository::class, [
            'getCanvasItemProjectId' => function ($id) use (&$resolvedItemId) {
                $resolvedItemId = $id;

                return 9;
            },
            'editCanvasItem' => function () use (&$wrote) {
                $wrote++;
            },
        ]);
        $service = $this->securedService($repo, $this->allowingPermissions());

        $service->updateCanvasItem(['itemId' => 42, 'canvasId' => 9999, 'description' => 'x'], 'swotcanvas');

        $this->assertSame(42, $resolvedItemId, 'Project must be resolved from itemId, not the payload canvasId');
        $this->assertSame(1, $wrote);
    }

    public function test_create_canvas_item_throws_and_never_inserts_for_unknown_board(): void
    {
        $inserted = 0;
        $repo = $this->make(BlueprintsRepository::class, [
            'getCanvasProjectId' => fn () => null,
            'addCanvasItem' => function () use (&$inserted) {
                $inserted++;

                return '1';
            },
        ]);
        $service = $this->securedService($repo, $this->allowingPermissions());

        try {
            $service->createCanvasItem(['canvasId' => 9999, 'box' => 'x'], 'swotcanvas');
            $this->fail('Expected AuthorizationException for an unknown target board');
        } catch (AuthorizationException) {
        }
        $this->assertSame(0, $inserted, 'An item must never be created into an unknown/foreign board');
    }

    public function test_delete_canvas_item_throws_and_never_deletes_for_unresolved_item(): void
    {
        $deleted = 0;
        $repo = $this->make(BlueprintsRepository::class, [
            'getCanvasItemProjectId' => fn () => null,
            'delCanvasItem' => function () use (&$deleted) {
                $deleted++;
            },
        ]);
        $service = $this->securedService($repo, $this->allowingPermissions());

        try {
            $service->deleteCanvasItem(5, 'swotcanvas');
            $this->fail('Expected AuthorizationException for an unresolved item');
        } catch (AuthorizationException) {
        }
        $this->assertSame(0, $deleted, 'A missing/foreign item must never be deleted');
    }

    public function test_delete_board_throws_and_never_deletes_for_unresolved_board(): void
    {
        $deleted = 0;
        $repo = $this->make(BlueprintsRepository::class, [
            'getCanvasProjectId' => fn () => null,
            'deleteCanvas' => function () use (&$deleted) {
                $deleted++;
            },
        ]);
        $service = $this->securedService($repo, $this->allowingPermissions());

        try {
            $service->deleteBoard(5, 'swotcanvas');
            $this->fail('Expected AuthorizationException for an unresolved board');
        } catch (AuthorizationException) {
        }
        $this->assertSame(0, $deleted, 'A missing/foreign board must never be deleted');
    }

    public function test_copy_board_throws_when_source_unresolved_and_never_copies(): void
    {
        $copied = 0;
        $repo = $this->make(BlueprintsRepository::class, [
            'getCanvasProjectId' => fn () => null,
            'copyCanvas' => function () use (&$copied) {
                $copied++;

                return 1;
            },
        ]);
        $service = $this->securedService($repo, $this->allowingPermissions());

        try {
            $service->copyBoard(5, 7, 1, 'Copy', 'swotcanvas');
            $this->fail('Expected AuthorizationException for an unresolved source board');
        } catch (AuthorizationException) {
        }
        $this->assertSame(0, $copied, 'A board must never be copied from an unknown/foreign source');
    }

    public function test_merge_board_requires_both_boards_to_resolve(): void
    {
        // Source (1) resolves but target (2) does not -> deny, never merge.
        $merged = 0;
        $repo = $this->make(BlueprintsRepository::class, [
            'getCanvasProjectId' => fn ($id) => $id === 1 ? 9 : null,
            'mergeCanvas' => function () use (&$merged) {
                $merged++;

                return true;
            },
        ]);
        $service = $this->securedService($repo, $this->allowingPermissions());

        try {
            $service->mergeBoard(2, 1, 'swotcanvas');
            $this->fail('Expected AuthorizationException when a board does not resolve');
        } catch (AuthorizationException) {
        }
        $this->assertSame(0, $merged, 'Merge must not run unless BOTH boards resolve');
    }

    public function test_import_authorizes_create_on_target_project_before_doing_anything(): void
    {
        // import() authorizes CREATE against the passed projectId first — a denial throws
        // before the file/template/repo are ever touched (it is reachable via JSON-RPC with an
        // arbitrary projectId).
        $repo = $this->make(BlueprintsRepository::class, [
            'existCanvas' => function (): bool {
                $this->fail('import must deny before touching the repository');

                return false;
            },
        ]);
        $service = $this->securedService($repo, $this->denyingPermissions());

        $this->expectException(AuthorizationException::class);
        $service->import('/tmp/does-not-matter.xml', 'swot', 55, 1);
    }
}

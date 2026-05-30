<?php

namespace Unit\app\Domain\Blueprints\Services;

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
}

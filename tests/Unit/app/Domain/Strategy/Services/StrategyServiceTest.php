<?php

namespace Unit\app\Domain\Strategy\Services;

use Leantime\Domain\Canvas\Services\Canvas as CanvasService;
use Leantime\Domain\Strategy\Services\Strategy as StrategyService;
use Unit\TestCase;

/**
 * Unit tests for the Strategy service helpers extracted during the
 * thin-controller refactor (buildRecentProgressCanvas, getStrategyBoardsOverview).
 */
class StrategyServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Builds a real Strategy service, allowing the Canvas service dependency
     * to be overridden with a stub.
     */
    private function makeService(?CanvasService $canvasService = null): StrategyService
    {
        return new StrategyService(
            $canvasService ?? $this->make(CanvasService::class),
        );
    }

    public function test_build_recent_progress_seeds_metadata_and_removes_used_type(): void
    {
        $service = $this->makeService();

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
        $this->assertSame('valuecanvas', $result['valuecanvas']['module']);

        // The consumed type must be removed from the remaining "other" boards map.
        $this->assertArrayNotHasKey('valuecanvas', $metadata);
        $this->assertArrayHasKey('swotcanvas', $metadata);
    }

    public function test_build_recent_progress_increments_count_for_repeat_type(): void
    {
        $service = $this->makeService();

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
        $service = $this->makeService();

        $metadata = $service->getBoardMetadata();
        $metadataCountBefore = count($metadata);

        $result = $service->buildRecentProgressCanvas([], $metadata);

        $this->assertSame([], $result);
        // Nothing consumed, so the metadata map is untouched.
        $this->assertCount($metadataCountBefore, $metadata);
    }

    public function test_get_overview_assembles_render_ready_struct(): void
    {
        $recentlyUpdated = [
            ['type' => 'leancanvas', 'title' => 'Lean A', 'modified' => '2026-05-25 12:00:00', 'id' => 99],
        ];
        $progress = ['leancanvas' => 0.5];

        $canvasService = $this->make(CanvasService::class, [
            'getLastUpdatedCanvas' => fn () => $recentlyUpdated,
            'getBoardProgress' => fn () => $progress,
        ]);

        $overview = $this->makeService($canvasService)->getStrategyBoardsOverview(7);

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

    public function test_get_overview_passes_project_id_to_canvas_service(): void
    {
        $capturedLastUpdatedId = null;
        $capturedProgressId = null;

        $canvasService = $this->make(CanvasService::class, [
            'getLastUpdatedCanvas' => function ($projectId) use (&$capturedLastUpdatedId) {
                $capturedLastUpdatedId = $projectId;

                return [];
            },
            'getBoardProgress' => function ($projectId) use (&$capturedProgressId) {
                $capturedProgressId = $projectId;

                return [];
            },
        ]);

        $this->makeService($canvasService)->getStrategyBoardsOverview(7);

        $this->assertSame(7, $capturedLastUpdatedId);
        $this->assertSame('7', $capturedProgressId);
    }
}

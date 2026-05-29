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
}

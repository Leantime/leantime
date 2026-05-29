<?php

namespace Unit\app\Domain\Blueprints\Services;

use Leantime\Domain\Blueprints\Repositories\Blueprints as BlueprintsRepository;
use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;
use Leantime\Domain\Blueprints\Services\BlueprintsExport;
use Leantime\Domain\Blueprints\Services\TemplateRegistry;
use Unit\TestCase;

/**
 * Unit tests for the BlueprintsExport service (XML generation).
 */
class BlueprintsExportTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    public function test_exports_a_canvas_board_to_xml(): void
    {
        $repo = $this->make(BlueprintsRepository::class, [
            'getSingleCanvas' => fn () => [['title' => 'My SWOT', 'projectId' => 1]],
            'getCanvasItemsById' => fn () => [
                [
                    'box' => 'swot_strengths', 'description' => 'Strong brand', 'author' => 5,
                    'status' => '', 'relates' => '', 'assumptions' => '', 'data' => '', 'conclusion' => '',
                    'created' => '2026-01-01 00:00:00', 'modified' => '2026-01-02 00:00:00',
                    'authorFirstname' => 'Jo', 'authorLastname' => 'Doe',
                ],
            ],
        ]);
        $service = $this->make(BlueprintsService::class, [
            'getTranslatedBoxes' => fn () => [
                'swot_strengths' => ['title' => 'Strengths'],
                'swot_weaknesses' => ['title' => 'Weaknesses'],
            ],
        ]);

        $xml = (new BlueprintsExport($repo, $service, new TemplateRegistry))->exportToXml(7, 'swot');

        $this->assertNotNull($xml);
        $this->assertStringContainsString('<canvas key="swotcanvas">', $xml);
        $this->assertStringContainsString('<title>My SWOT</title>', $xml);
        $this->assertStringContainsString('<element key="swot_strengths">', $xml);
        $this->assertStringContainsString('<description>Strong brand</description>', $xml);
        // An empty box still emits its element wrapper.
        $this->assertStringContainsString('<element key="swot_weaknesses">', $xml);
    }

    public function test_returns_null_for_unknown_canvas_type(): void
    {
        $export = new BlueprintsExport(
            $this->make(BlueprintsRepository::class),
            $this->make(BlueprintsService::class),
            new TemplateRegistry,
        );

        $this->assertNull($export->exportToXml(7, 'doesnotexist'));
    }

    public function test_returns_null_when_board_does_not_exist(): void
    {
        $repo = $this->make(BlueprintsRepository::class, ['getSingleCanvas' => fn () => []]);

        $export = new BlueprintsExport($repo, $this->make(BlueprintsService::class), new TemplateRegistry);

        $this->assertNull($export->exportToXml(7, 'swot'));
    }
}

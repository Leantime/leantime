<?php

namespace Unit\app\Domain\Blueprints\Services;

use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;
use Leantime\Domain\Blueprints\Services\BlueprintsExport;
use Leantime\Domain\Blueprints\Services\TemplateRegistry;
use Unit\TestCase;

/**
 * Unit tests for the BlueprintsExport service (XML generation).
 *
 * exportToXml reads the board + items through the Blueprints SERVICE (getBoard / getBoardItems),
 * which authorizes VIEW against the board's real project and returns false / [] for a
 * missing/foreign/unauthorized board — so these stub the service, not the repository.
 */
class BlueprintsExportTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    public function test_exports_a_canvas_board_to_xml(): void
    {
        $service = $this->make(BlueprintsService::class, [
            'getBoard' => fn () => [['title' => 'My SWOT', 'projectId' => 1]],
            'getBoardItems' => fn () => [
                [
                    'box' => 'swot_strengths', 'description' => 'Strong brand', 'author' => 5,
                    'status' => '', 'relates' => '', 'assumptions' => '', 'data' => '', 'conclusion' => '',
                    'created' => '2026-01-01 00:00:00', 'modified' => '2026-01-02 00:00:00',
                    'authorFirstname' => 'Jo', 'authorLastname' => 'Doe',
                ],
            ],
            'getTranslatedBoxes' => fn () => [
                'swot_strengths' => ['title' => 'Strengths'],
                'swot_weaknesses' => ['title' => 'Weaknesses'],
            ],
        ]);

        $xml = (new BlueprintsExport($service, new TemplateRegistry))->exportToXml(7, 'swot');

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
            $this->make(BlueprintsService::class),
            new TemplateRegistry,
        );

        $this->assertNull($export->exportToXml(7, 'doesnotexist'));
    }

    public function test_returns_null_when_board_does_not_exist(): void
    {
        // getBoard returns false for a missing/foreign/unauthorized board.
        $service = $this->make(BlueprintsService::class, ['getBoard' => fn () => false]);

        $export = new BlueprintsExport($service, new TemplateRegistry);

        $this->assertNull($export->exportToXml(7, 'swot'));
    }
}

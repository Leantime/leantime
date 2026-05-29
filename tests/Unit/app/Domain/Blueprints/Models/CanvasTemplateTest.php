<?php

namespace Unit\app\Domain\Blueprints\Models;

use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Unit\TestCase;

/**
 * Unit tests for the CanvasTemplate value object: identifier derivation and the
 * label-resolution rules (omitted/"default"/null fall back to base defaults,
 * an explicit empty array means "no labels", an explicit array is used as-is).
 */
class CanvasTemplateTest extends TestCase
{
    public function test_derives_database_type_comment_module_and_session_key(): void
    {
        $template = new CanvasTemplate(['slug' => 'swot']);

        $this->assertSame('swotcanvas', $template->getDatabaseType());
        $this->assertSame('swotcanvasitem', $template->getCommentModule());
        $this->assertSame('currentSWOTCanvas', $template->getSessionKey());
    }

    public function test_applies_scalar_defaults_when_not_provided(): void
    {
        $template = new CanvasTemplate(['slug' => 'x']);

        $this->assertSame('fa-x', $template->icon);
        $this->assertSame('', $template->disclaimer);
        $this->assertSame(2, $template->minColumns);
        $this->assertSame(0, $template->minWidthOffset);
        $this->assertSame([], $template->boxes);
        $this->assertSame([], $template->layout);
    }

    public function test_omitted_status_labels_fall_back_to_defaults(): void
    {
        $template = new CanvasTemplate(['slug' => 'x']);

        $this->assertArrayHasKey('status_draft', $template->statusLabels);
        $this->assertArrayHasKey('status_valid', $template->statusLabels);
        $this->assertArrayHasKey('relates_none', $template->relatesLabels);
    }

    public function test_default_keyword_falls_back_to_defaults(): void
    {
        $template = new CanvasTemplate(['slug' => 'x', 'statusLabels' => 'default', 'relatesLabels' => 'default']);

        $this->assertArrayHasKey('status_draft', $template->statusLabels);
        $this->assertArrayHasKey('relates_customers', $template->relatesLabels);
    }

    public function test_explicit_empty_array_means_no_labels(): void
    {
        // This is the SWOT case: statusLabels: {} (hide the status dropdown).
        $template = new CanvasTemplate(['slug' => 'swot', 'statusLabels' => []]);

        $this->assertSame([], $template->statusLabels);
        // relatesLabels was omitted, so it still gets the defaults.
        $this->assertArrayHasKey('relates_none', $template->relatesLabels);
    }

    public function test_explicit_labels_are_used_as_is(): void
    {
        $custom = [
            'status_observation' => ['icon' => 'fa-eye', 'color' => 'blue', 'title' => 'status.ea.observation', 'dropdown' => 'info', 'active' => true],
        ];

        $template = new CanvasTemplate(['slug' => 'ea', 'statusLabels' => $custom]);

        $this->assertSame($custom, $template->statusLabels);
        $this->assertArrayNotHasKey('status_draft', $template->statusLabels);
    }

    public function test_data_labels_default_and_override(): void
    {
        $defaulted = new CanvasTemplate(['slug' => 'x']);
        $this->assertArrayHasKey(1, $defaulted->dataLabels);
        $this->assertSame('assumptions', $defaulted->dataLabels[1]['field']);

        $custom = [1 => ['title' => 'label.description', 'field' => 'conclusion', 'active' => true]];
        $overridden = new CanvasTemplate(['slug' => 'swot', 'dataLabels' => $custom]);
        $this->assertSame($custom, $overridden->dataLabels);
    }
}

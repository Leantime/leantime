<?php

namespace Unit\app\Domain\Blueprints\Models;

use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Unit\TestCase;

/**
 * Phase 4 of the content-templates rollout: blueprint YAMLs gain an
 * optional `startContent:` field that references a ContentTemplates key.
 * This locks in the field's parse rules.
 */
class CanvasTemplateStartContentTest extends TestCase
{
    public function test_start_content_is_null_when_absent(): void
    {
        $tpl = new CanvasTemplate([
            'slug' => 'swot',
            'icon' => 'fa-x',
            'boxes' => [],
        ]);

        $this->assertNull($tpl->startContent);
    }

    public function test_start_content_is_null_when_empty_string(): void
    {
        $tpl = new CanvasTemplate([
            'slug' => 'swot',
            'icon' => 'fa-x',
            'boxes' => [],
            'startContent' => '',
        ]);

        $this->assertNull($tpl->startContent);
    }

    public function test_start_content_carries_through_when_set(): void
    {
        $tpl = new CanvasTemplate([
            'slug' => 'leancanvas',
            'icon' => 'fa-x',
            'boxes' => [],
            'startContent' => 'lean-starter-saas',
        ]);

        $this->assertSame('lean-starter-saas', $tpl->startContent);
    }
}

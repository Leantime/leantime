<?php

namespace Unit\app\Domain\Blueprints\Services;

use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Leantime\Domain\Blueprints\Services\TemplateRegistry;
use Unit\TestCase;

/**
 * Unit tests for TemplateRegistry, which loads the canvas YAML definitions from
 * app/Domain/Blueprints/Templates/definitions into CanvasTemplate objects.
 */
class TemplateRegistryTest extends TestCase
{
    private function registry(): TemplateRegistry
    {
        return new TemplateRegistry;
    }

    public function test_loads_a_known_definition(): void
    {
        $template = $this->registry()->get('swot');

        $this->assertInstanceOf(CanvasTemplate::class, $template);
        $this->assertSame('swot', $template->slug);
        $this->assertSame('swotcanvas', $template->getDatabaseType());
        // SWOT has four boxes.
        $this->assertCount(4, $template->boxes);
        $this->assertArrayHasKey('swot_strengths', $template->boxes);
    }

    public function test_unknown_slug_returns_null(): void
    {
        $this->assertNull($this->registry()->get('doesnotexist'));
    }

    public function test_slug_lookup_is_case_insensitive_and_trimmed(): void
    {
        $this->assertInstanceOf(CanvasTemplate::class, $this->registry()->get('  SWOT '));
    }

    public function test_get_by_database_type_strips_canvas_suffix(): void
    {
        $template = $this->registry()->getByDatabaseType('leancanvas');

        $this->assertInstanceOf(CanvasTemplate::class, $template);
        $this->assertSame('lean', $template->slug);
    }

    public function test_caches_and_returns_same_instance(): void
    {
        $registry = $this->registry();

        $this->assertSame($registry->get('swot'), $registry->get('swot'));
    }

    public function test_all_loads_every_definition(): void
    {
        $slugs = $this->registry()->slugs();

        // The 16 consolidated variants all have a YAML definition.
        $expected = ['cp', 'dbm', 'ea', 'em', 'insights', 'lbm', 'lean', 'minempathy', 'obm', 'retros', 'risks', 'sb', 'sm', 'sq', 'swot', 'value'];
        foreach ($expected as $slug) {
            $this->assertContains($slug, $slugs, "Missing definition for '$slug'");
        }
    }

    public function test_obm_carries_min_width_offset(): void
    {
        // OBM is the one layout that needed an extra +50px min-width offset.
        $this->assertSame(50, $this->registry()->get('obm')->minWidthOffset);
    }
}

<?php

namespace Unit\app\Domain\ContentTemplates\Services\Appliers;

use Leantime\Core\Db\Db as DbCore;
use Leantime\Domain\ContentTemplates\Models\ContentTemplate;
use Leantime\Domain\ContentTemplates\Services\Appliers\CanvasItemsApplier;
use Leantime\Domain\ContentTemplates\Services\Appliers\WikiApplier;
use Unit\TestCase;

/**
 * Unit tests for the appliers' supports() routing logic and early-return
 * safety. Actual DB writes are exercised in integration tests once Phase 2
 * wires real templates; here we lock in the routing contract.
 */
class AppliersTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    public function test_wiki_applier_supports_only_wiki(): void
    {
        $applier = new WikiApplier($this->makeDbCore());

        $this->assertTrue($applier->supports('wiki'));
        $this->assertFalse($applier->supports('logicmodel'));
        $this->assertFalse($applier->supports('goal'));
        $this->assertFalse($applier->supports(''));
    }

    public function test_canvas_applier_supports_any_non_wiki_non_empty_applies_to(): void
    {
        $applier = new CanvasItemsApplier($this->makeDbCore());

        $this->assertTrue($applier->supports('logicmodel'));
        $this->assertTrue($applier->supports('goal'));
        $this->assertTrue($applier->supports('leancanvas'));
        $this->assertTrue($applier->supports('swot'));
        $this->assertTrue($applier->supports('any-future-canvas-type'));

        $this->assertFalse($applier->supports('wiki'));
        $this->assertFalse($applier->supports(''));
    }

    public function test_canvas_applier_returns_zero_for_invalid_target_id(): void
    {
        $applier = new CanvasItemsApplier($this->makeDbCore());

        $this->assertSame(0, $applier->apply(0, $this->makeCanvasTemplate()));
        $this->assertSame(0, $applier->apply(-1, $this->makeCanvasTemplate()));
    }

    public function test_canvas_applier_returns_zero_for_unusable_template(): void
    {
        $applier = new CanvasItemsApplier($this->makeDbCore());

        $unusable = ContentTemplate::fromArray([
            'key' => '',
            'title' => 'X',
            'description' => '',
            'appliesTo' => 'logicmodel',
        ]);

        $this->assertSame(0, $applier->apply(42, $unusable));
    }

    public function test_canvas_applier_returns_zero_for_empty_items_payload(): void
    {
        $applier = new CanvasItemsApplier($this->makeDbCore());

        $emptyItems = ContentTemplate::fromArray([
            'key' => 'empty',
            'title' => 'Empty',
            'description' => '',
            'appliesTo' => 'logicmodel',
            'logicmodel' => ['items' => []],
        ]);

        $this->assertSame(0, $applier->apply(42, $emptyItems));
    }

    public function test_wiki_applier_returns_zero_for_invalid_target_id(): void
    {
        $applier = new WikiApplier($this->makeDbCore());

        $this->assertSame(0, $applier->apply(0, $this->makeWikiTemplate()));
    }

    public function test_wiki_applier_returns_zero_for_empty_articles_payload(): void
    {
        $applier = new WikiApplier($this->makeDbCore());

        $emptyArticles = ContentTemplate::fromArray([
            'key' => 'empty',
            'title' => 'Empty',
            'description' => '',
            'appliesTo' => 'wiki',
            'wiki' => ['articles' => []],
        ]);

        $this->assertSame(0, $applier->apply(42, $emptyArticles));
    }

    private function makeDbCore(): DbCore
    {
        // The supports() / early-return paths never call the connection, so a
        // bare stub is enough. Methods that DO write are exercised in
        // integration tests (Phase 2+).
        return $this->make(DbCore::class);
    }

    private function makeCanvasTemplate(): ContentTemplate
    {
        return ContentTemplate::fromArray([
            'key' => 'k',
            'title' => 'T',
            'description' => '',
            'appliesTo' => 'logicmodel',
            'logicmodel' => [
                'items' => [
                    ['box' => 'lm_inputs', 'title' => 'A', 'description' => 'aa'],
                ],
            ],
        ]);
    }

    private function makeWikiTemplate(): ContentTemplate
    {
        return ContentTemplate::fromArray([
            'key' => 'k',
            'title' => 'T',
            'description' => '',
            'appliesTo' => 'wiki',
            'wiki' => [
                'articles' => [
                    ['title' => 'A', 'content' => '<p>aa</p>'],
                ],
            ],
        ]);
    }
}

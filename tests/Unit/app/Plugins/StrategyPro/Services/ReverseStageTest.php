<?php

declare(strict_types=1);

namespace Unit\app\Plugins\StrategyPro\Services;

use Leantime\Plugins\StrategyPro\Services\ReverseStage;
use PHPUnit\Framework\TestCase;

/**
 * Contract tests for {@see ReverseStage}. The enum is what prevents the
 * "copy the Outcomes step four times" trap — behavior lives here, not in
 * per-stage controller branching. If these break, the whole flow's
 * confidence + mode discipline breaks with them.
 *
 * The Impact-never-seeded invariant is the most load-bearing test in the
 * whole feature: a canvas with a generated Impact is a lie the report
 * renders to a funder (§4 rev.2). Locking it in at the enum level means
 * no future template can accidentally propose one.
 */
class ReverseStageTest extends TestCase
{
    public function test_impact_can_never_be_seeded(): void
    {
        $this->assertFalse(
            ReverseStage::Impact->canBeSeeded(),
            'Impact must never be seeded — a canvas with a generated Impact is a lie the report renders to a funder.'
        );
        $this->assertSame('none', ReverseStage::Impact->confidence());
    }

    public function test_every_other_stage_can_be_seeded(): void
    {
        foreach (ReverseStage::cases() as $stage) {
            if ($stage === ReverseStage::Impact) {
                continue;
            }
            $this->assertTrue(
                $stage->canBeSeeded(),
                "Stage {$stage->value} must be seedable — Impact is the only exception."
            );
        }
    }

    public function test_high_confidence_stages_propose_selected(): void
    {
        // Highest = Inputs (a resource IS an input).
        // High    = Outcomes (goals w/ linkAndReport are outcomes by construction).
        $this->assertTrue(ReverseStage::Inputs->proposesSelected());
        $this->assertTrue(ReverseStage::Outcomes->proposesSelected());
    }

    public function test_medium_and_low_confidence_stages_propose_unselected(): void
    {
        // Medium = Outputs (milestones CAN be internal work — user opts in).
        // Low    = Activities (unit mismatch — user decides which projects group into an activity).
        $this->assertFalse(ReverseStage::Outputs->proposesSelected());
        $this->assertFalse(ReverseStage::Activities->proposesSelected());
    }

    public function test_only_low_confidence_stages_collapse_by_default(): void
    {
        $this->assertTrue(ReverseStage::Activities->isCollapsedByDefault());
        // Everything else stays uncollapsed — even seeded-with-lots-of-candidates
        // stages like Outputs render open (they get the +N-more tail instead).
        foreach ([ReverseStage::Inputs, ReverseStage::Outputs, ReverseStage::Outcomes, ReverseStage::Impact] as $stage) {
            $this->assertFalse($stage->isCollapsedByDefault(), $stage->value.' must render open by default');
        }
    }

    public function test_canvas_order_is_inputs_to_impact(): void
    {
        // Locks the review-screen section order and the stage rail order.
        // A regression here would render the arc backwards for the reader.
        $this->assertSame([
            ReverseStage::Inputs,
            ReverseStage::Activities,
            ReverseStage::Outputs,
            ReverseStage::Outcomes,
            ReverseStage::Impact,
        ], ReverseStage::canvasOrder());
    }

    public function test_boxes_match_logicmodelcanvas_convention(): void
    {
        // The box() values are what get written into zp_canvas_items.box.
        // A mismatch would silently misplace items on the canvas grid.
        $this->assertSame('lm_inputs', ReverseStage::Inputs->box());
        $this->assertSame('lm_activities', ReverseStage::Activities->box());
        $this->assertSame('lm_outputs', ReverseStage::Outputs->box());
        $this->assertSame('lm_outcomes', ReverseStage::Outcomes->box());
        $this->assertSame('lm_impact', ReverseStage::Impact->box());
    }

    public function test_every_stage_has_a_guide_prompt(): void
    {
        // Guide mode isn't a fallback — every stage must have a real prompt.
        // §3 rev.2: neither mode is a failure state.
        foreach (ReverseStage::cases() as $stage) {
            $prompt = $stage->guidePrompt();
            $this->assertArrayHasKey('title', $prompt);
            $this->assertArrayHasKey('hint', $prompt);
            $this->assertNotSame('', trim($prompt['title']), $stage->value.' guide prompt title is empty');
            $this->assertNotSame('', trim($prompt['hint']), $stage->value.' guide prompt hint is empty');
        }
    }

    public function test_impact_guide_prompt_matches_spec(): void
    {
        // Spec §6 rev.2 dictates the exact phrasing — "the change in the world
        // you're working toward. Not the number — the change." Test locks it.
        $prompt = ReverseStage::Impact->guidePrompt();
        $this->assertStringContainsString("What's this all for", $prompt['title']);
        $this->assertStringContainsString('change in the world', $prompt['hint']);
        $this->assertStringContainsString('Not the number', $prompt['hint']);
    }
}

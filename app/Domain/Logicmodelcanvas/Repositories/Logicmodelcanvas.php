<?php

/**
 * Logic Model Canvas Repository
 */

namespace Leantime\Domain\Logicmodelcanvas\Repositories;

use Leantime\Domain\Canvas\Repositories\Canvas;

class Logicmodelcanvas extends Canvas
{
    /**
     * Canvas identifier used for DB type and URL routing.
     */
    protected const CANVAS_NAME = 'logicmodel';

    /**
     * Stage definitions for the five-stage causal chain.
     * Referenced by the StrategyPro plugin via Logicmodelcanvas::STAGES.
     */
    public const STAGES = [
        1 => [
            'key' => 'inputs',
            'title' => 'box.logicmodel.inputs',
            'subtitle' => 'box.logicmodel.inputs_subtitle',
            'icon' => 'fa-arrow-right-to-bracket',
            'color' => '#4A85B5',
            'bg' => '#EDF3F8',
        ],
        2 => [
            'key' => 'activities',
            'title' => 'box.logicmodel.activities',
            'subtitle' => 'box.logicmodel.activities_subtitle',
            'icon' => 'fa-gears',
            'color' => '#3E937A',
            'bg' => '#ECF6F2',
        ],
        3 => [
            'key' => 'outputs',
            'title' => 'box.logicmodel.outputs',
            'subtitle' => 'box.logicmodel.outputs_subtitle',
            'icon' => 'fa-boxes-stacked',
            'color' => '#C09035',
            'bg' => '#FBF5EA',
        ],
        4 => [
            'key' => 'outcomes',
            'title' => 'box.logicmodel.outcomes',
            'subtitle' => 'box.logicmodel.outcomes_subtitle',
            'icon' => 'fa-chart-line',
            'color' => '#8E6AAD',
            'bg' => '#F2EDF8',
        ],
        5 => [
            'key' => 'impact',
            'title' => 'box.logicmodel.impact',
            'subtitle' => 'box.logicmodel.impact_subtitle',
            'icon' => 'fa-bullseye',
            'color' => '#2D7D5E',
            'bg' => '#EAF5F0',
        ],
    ];

    /**
     * Board framework templates.
     * Referenced by the StrategyPro plugin via Logicmodelcanvas::TEMPLATES.
     */
    public const TEMPLATES = [
        'standard' => [
            'key' => 'standard',
            'title' => 'Standard Logic Model',
            'description' => 'Classic five-column logic model for program planning.',
        ],
        'toc' => [
            'key' => 'toc',
            'title' => 'Theory of Change',
            'description' => 'Backwards-mapping from impact to inputs.',
        ],
        'results' => [
            'key' => 'results',
            'title' => 'Results Framework',
            'description' => 'Focus on outputs, outcomes, and impact measurement.',
        ],
        'pathway' => [
            'key' => 'pathway',
            'title' => 'Impact Pathway',
            'description' => 'Causal chain emphasising linkages between stages.',
        ],
        'program' => [
            'key' => 'program',
            'title' => 'Program Logic',
            'description' => 'Program-level view for multi-project portfolios.',
        ],
    ];

    /**
     * Icon associated with canvas.
     */
    protected string $icon = 'fa-diagram-project';

    /**
     * Canvas element box types (one per stage).
     *
     * @var array<string, array{icon: string, title: string}>
     */
    protected array $canvasTypes = [
        'lm_inputs' => ['icon' => 'fa-arrow-right-to-bracket', 'title' => 'box.logicmodel.inputs'],
        'lm_activities' => ['icon' => 'fa-gears', 'title' => 'box.logicmodel.activities'],
        'lm_outputs' => ['icon' => 'fa-boxes-stacked', 'title' => 'box.logicmodel.outputs'],
        'lm_outcomes' => ['icon' => 'fa-chart-line', 'title' => 'box.logicmodel.outcomes'],
        'lm_impact' => ['icon' => 'fa-bullseye', 'title' => 'box.logicmodel.impact'],
    ];

    /**
     * Hypothesis status labels (same keys as Canvas base, custom titles).
     *
     * @var array<string, array{icon: string, color: string, title: string, dropdown: string, active: bool}>
     */
    protected array $statusLabels = [
        'status_draft' => ['icon' => 'fa-circle-question', 'color' => 'blue', 'title' => 'logicmodel.status.draft', 'dropdown' => 'info', 'active' => true],
        'status_review' => ['icon' => 'fa-circle-exclamation', 'color' => 'orange', 'title' => 'logicmodel.status.review', 'dropdown' => 'warning', 'active' => true],
        'status_valid' => ['icon' => 'fa-circle-check', 'color' => 'green', 'title' => 'logicmodel.status.validated', 'dropdown' => 'success', 'active' => true],
        'status_hold' => ['icon' => 'fa-circle-pause', 'color' => 'red', 'title' => 'logicmodel.status.paused', 'dropdown' => 'danger', 'active' => true],
        'status_invalid' => ['icon' => 'fa-circle-xmark', 'color' => 'red', 'title' => 'logicmodel.status.invalid', 'dropdown' => 'danger', 'active' => true],
    ];

    /**
     * Relates labels (not used for logic model).
     *
     * @var array<string, mixed>
     */
    protected array $relatesLabels = [];

    /**
     * Data labels for the canvas item dialog.
     *
     * @var array<int, array{title: string, field: string, active: bool}>
     */
    protected array $dataLabels = [
        1 => ['title' => 'label.description', 'field' => 'conclusion', 'active' => true],
        2 => ['title' => 'label.assumptions', 'field' => 'assumptions', 'active' => true],
        3 => ['title' => 'label.data', 'field' => 'data', 'active' => true],
    ];

    /**
     * Map box field values to stage keys.
     */
    private const BOX_TO_STAGE = [
        'lm_inputs' => 'inputs',
        'lm_activities' => 'activities',
        'lm_outputs' => 'outputs',
        'lm_outcomes' => 'outcomes',
        'lm_impact' => 'impact',
    ];

    /**
     * Get canvas items grouped by stage key.
     *
     * Returns an associative array keyed by stage name (inputs, activities, etc.)
     * with each value being an array of item rows.
     * Required by the StrategyPro plugin service.
     *
     * @param  int  $canvasId  Canvas board ID
     * @return array<string, array<int, array<string, mixed>>>
     *
     * @api
     */
    public function getItemsByStage(int $canvasId): array
    {
        $items = $this->getCanvasItemsById($canvasId);
        $grouped = [];

        foreach (self::BOX_TO_STAGE as $box => $stageKey) {
            $grouped[$stageKey] = [];
        }

        if (is_array($items)) {
            foreach ($items as $item) {
                $box = $item['box'] ?? '';
                if (isset(self::BOX_TO_STAGE[$box])) {
                    $grouped[self::BOX_TO_STAGE[$box]][] = $item;
                }
            }
        }

        return $grouped;
    }
}

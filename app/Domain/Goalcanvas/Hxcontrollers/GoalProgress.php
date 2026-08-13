<?php

namespace Leantime\Domain\Goalcanvas\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas as GoalcanvasService;

/**
 * Inline progress updates for the goal dialog — the RA budget/hours pattern:
 * the readout's number IS the input; blur-when-changed posts here and the
 * re-rendered readout (recomputed bar) swaps in. No Save ceremony for the
 * recurring monitoring action.
 */
class GoalProgress extends HtmxController
{
    protected static string $view = 'goalcanvas::partials.progressReadout';

    private GoalcanvasService $goalService;

    /**
     * init - DI via init(), not __construct (HtmxController contract).
     */
    public function init(GoalcanvasService $goalService): void
    {
        $this->goalService = $goalService;
    }

    /**
     * Persist an inline current-value edit and re-render the readout.
     * Authorization (EDIT against the goal's real project) and the
     * zp_goal_history record both happen inside patchGoalItem.
     */
    public function updateValue(): void
    {
        $itemId = (int) ($_POST['itemId'] ?? 0);
        $value = $_POST['currentValue'] ?? null;

        if ($itemId > 0 && is_numeric($value)) {
            $goal = $this->goalService->getGoalItem($itemId);

            // linkAndReport goals compute their current value from children —
            // an inline write would be silently overridden, so refuse it.
            if (is_array($goal) && ($goal['setting'] ?? '') !== 'linkAndReport') {
                $this->goalService->patchGoalItem($itemId, ['currentValue' => (float) $value]);
            }
        }

        $goal = $this->goalService->getGoalItem($itemId);
        if (! is_array($goal)) {
            // Unknown/foreign goal: render an empty readout rather than leak.
            $goal = ['id' => $itemId, 'metricType' => 'number', 'startValue' => 0, 'currentValue' => 0, 'endValue' => 0, 'setting' => ''];
        }

        $this->tpl->assign('canvasItem', $goal);
    }
}

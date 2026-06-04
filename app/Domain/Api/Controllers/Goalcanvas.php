<?php

/**
 * Goalcanvas class - Controller API
 */

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Domain\Goalcanvas\Permissions\GoalcanvasPermissions;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas as GoalcanvaService;
use Symfony\Component\HttpFoundation\Response;

class Goalcanvas extends Canvas
{
    protected const CANVAS_NAME = 'goal';

    /**
     * patch - inline goal-item update.
     *
     * Overrides the generic Canvas base so goal items are authorized with goals.* (the Goals
     * vocabulary) rather than the generic blueprints.* — the Goalcanvas service resolves the
     * item's real project and authorizes goals.edit before patching (throws 403 for a
     * missing/foreign item or insufficient role; false = no allowlisted columns).
     */
    #[RequiresPermission(GoalcanvasPermissions::EDIT, entityScoped: true)]
    public function patch(array $params): Response
    {
        if (! isset($params['id'])) {
            return $this->tpl->displayJson(['status' => 'failure'], 400);
        }

        if (app()->make(GoalcanvaService::class)->patchGoalItem((int) $params['id'], $params) === false) {
            return $this->tpl->displayJson(['status' => 'no valid fields to update'], 400);
        }

        return $this->tpl->displayJson(['status' => 'ok']);
    }
}

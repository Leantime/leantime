<?php

namespace Leantime\Domain\Goalcanvas\Controllers;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Goalcanvas\Permissions\GoalcanvasPermissions;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas as GoalcanvaService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles deletion of a goal canvas item.
 */
class DelCanvasItem extends Controller
{
    /**
     * Constant that must be redefined.
     */
    protected const CANVAS_NAME = 'goal';

    private GoalcanvaService $goalService;

    private PermissionService $permissionService;

    /**
     * Initializes dependencies.
     */
    public function init(GoalcanvaService $goalService, PermissionService $permissionService): void
    {
        $this->goalService = $goalService;
        $this->permissionService = $permissionService;
    }

    /**
     * Displays the delete goal item confirmation, authorized against the goal's own project
     * (the dialog offers Delete by that project too).
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(GoalcanvasPermissions::DELETE, entityScoped: true)]
    public function get(array $params): Response
    {
        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);

        // getGoalItem() returns the goal with its real project, or false when it is missing or
        // not visible to the caller.
        $goalItem = $this->goalService->getGoalItem($id);
        if (! $goalItem) {
            return $this->tpl->displayPartial('errors.error404', responseCode: 404);
        }

        $this->permissionService->authorize(GoalcanvasPermissions::DELETE, (int) $goalItem['projectId']);

        $this->tpl->assign('id', $id);

        return $this->tpl->displayPartial(static::CANVAS_NAME.'canvas.delCanvasItem');
    }

    /**
     * Handles goal item deletion.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(GoalcanvasPermissions::DELETE, entityScoped: true)]
    public function post(array $params): Response
    {
        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);

        if (isset($_POST['del']) && $id > 0) {
            // The service resolves the item's REAL project and authorizes DELETE against it
            // (throwing for a missing/foreign item) — closing the by-id item-delete IDOR.
            $this->goalService->deleteGoalItem($id);

            $this->tpl->setNotification($this->language->__('notification.element_deleted'), 'success', strtoupper(static::CANVAS_NAME).'canvasitem_deleted');

            return Frontcontroller::redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas/showCanvas');
        }

        $this->tpl->assign('id', $id);

        return $this->tpl->displayPartial(static::CANVAS_NAME.'canvas.delCanvasItem');
    }
}

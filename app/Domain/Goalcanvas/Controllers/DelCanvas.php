<?php

namespace Leantime\Domain\Goalcanvas\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Goalcanvas\Permissions\GoalcanvasPermissions;
use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas as GoalcanvaRepository;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas as GoalcanvaService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles deletion of a goal canvas board.
 */
class DelCanvas extends Controller
{
    /**
     * Constant that must be redefined.
     */
    protected const CANVAS_NAME = 'goal';

    private GoalcanvaRepository $canvasRepo;

    private GoalcanvaService $goalService;

    /**
     * Initializes dependencies.
     */
    public function init(GoalcanvaRepository $canvasRepo, GoalcanvaService $goalService): void
    {
        $this->canvasRepo = $canvasRepo;
        $this->goalService = $goalService;
    }

    /**
     * Displays the delete goal canvas confirmation.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(GoalcanvasPermissions::DELETE)]
    public function get(array $params): Response
    {
        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);
        $this->tpl->assign('id', $id);

        return $this->tpl->displayPartial(static::CANVAS_NAME.'canvas.delCanvas');
    }

    /**
     * Handles goal canvas board deletion.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(GoalcanvasPermissions::DELETE, entityScoped: true)]
    public function post(array $params): Response
    {
        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);

        if (isset($_POST['del']) && $id > 0) {
            // The service resolves the board's REAL project and authorizes DELETE against it
            // (throwing for a missing/foreign board) — closing the by-id board-delete IDOR the
            // previous role-only Auth::authOrRedirect left open.
            $this->goalService->deleteGoalBoard($id);

            $allCanvas = $this->canvasRepo->getAllCanvas(session('currentProject'));
            session(['current'.strtoupper(static::CANVAS_NAME).'Canvas' => $allCanvas[0]['id'] ?? -1]);

            $this->tpl->setNotification($this->language->__('notification.board_deleted'), 'success', strtoupper(static::CANVAS_NAME).'canvas_deleted');

            if (! $allCanvas || count($allCanvas) == 0) {
                return Frontcontroller::redirect(BASE_URL.'/blueprints/showBoards');
            }

            return Frontcontroller::redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas/showCanvas');
        }

        $this->tpl->assign('id', $id);

        return $this->tpl->displayPartial(static::CANVAS_NAME.'canvas.delCanvas');
    }
}

<?php

namespace Leantime\Domain\Canvas\Controllers;

use Illuminate\Support\Str;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Blueprints\Permissions\BlueprintsPermissions;
use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles deletion of a canvas board.
 */
class DelCanvas extends Controller
{
    /**
     * Constant that must be redefined by subclasses.
     */
    protected const CANVAS_NAME = '??';

    private mixed $canvasRepo;

    private BlueprintsService $blueprintsService;

    /**
     * Initializes dependencies.
     *
     * Note: no `void` return type so plugin subclasses that override init()
     * without a return type (this class is the deprecated plugin shim) stay
     * signature-compatible.
     */
    public function init()
    {
        $this->blueprintsService = app()->make(BlueprintsService::class);
        $canvasName = Str::studly(static::CANVAS_NAME).'canvas';
        $repoName = app()->getNamespace()."Domain\\$canvasName\\Repositories\\$canvasName";
        $this->canvasRepo = app()->make($repoName);
    }

    /**
     * Displays the delete canvas confirmation.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(BlueprintsPermissions::DELETE)]
    public function get(array $params): Response
    {
        return $this->tpl->displayPartial(static::CANVAS_NAME.'canvas.delCanvas');
    }

    /**
     * Handles canvas board deletion.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(BlueprintsPermissions::DELETE, entityScoped: true)]
    public function post(array $params): Response
    {
        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);

        if (isset($_POST['del']) && $id > 0) {
            // The service resolves the board's REAL project and authorizes DELETE against it
            // (throwing for a missing/foreign board) — closing the by-id board-delete IDOR the
            // previous role-only Auth::authOrRedirect left open.
            $this->blueprintsService->deleteBoard($id, static::CANVAS_NAME.'canvas');

            $allCanvas = $this->canvasRepo->getAllCanvas(session('currentProject'));
            session(['current'.strtoupper(static::CANVAS_NAME).'Canvas' => $allCanvas[0]['id'] ?? -1]);

            $this->tpl->setNotification($this->language->__('notification.board_deleted'), 'success', strtoupper(static::CANVAS_NAME).'canvas_deleted');

            if (! $allCanvas || count($allCanvas) == 0) {
                return Frontcontroller::redirect(BASE_URL.'/blueprints/showBoards');
            }

            return Frontcontroller::redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas/showCanvas');
        }

        return $this->tpl->displayPartial(static::CANVAS_NAME.'canvas.delCanvas');
    }
}

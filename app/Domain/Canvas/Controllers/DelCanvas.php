<?php

namespace Leantime\Domain\Canvas\Controllers;

use Illuminate\Support\Str;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
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

    /**
     * Initializes dependencies.
     *
     * Note: no `void` return type so plugin subclasses that override init()
     * without a return type (this class is the deprecated plugin shim) stay
     * signature-compatible.
     */
    public function init()
    {
        $canvasName = Str::studly(static::CANVAS_NAME).'canvas';
        $repoName = app()->getNamespace()."Domain\\$canvasName\\Repositories\\$canvasName";
        $this->canvasRepo = app()->make($repoName);
    }

    /**
     * Displays the delete canvas confirmation.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        return $this->tpl->displayPartial(static::CANVAS_NAME.'canvas.delCanvas');
    }

    /**
     * Handles canvas board deletion.
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);

        if (isset($_POST['del']) && $id > 0) {
            $this->canvasRepo->deleteCanvas($id);

            $allCanvas = $this->canvasRepo->getAllCanvas(session('currentProject'));
            session(['current'.strtoupper(static::CANVAS_NAME).'Canvas' => $allCanvas[0]['id'] ?? -1]);

            $this->tpl->setNotification($this->language->__('notification.board_deleted'), 'success', strtoupper(static::CANVAS_NAME).'canvas_deleted');

            $allCanvas = $this->canvasRepo->getAllCanvas(session('currentProject'));

            if (! $allCanvas || count($allCanvas) == 0) {
                return Frontcontroller::redirect(BASE_URL.'/strategy/showBoards');
            }

            return Frontcontroller::redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas/showCanvas');
        }

        return $this->tpl->displayPartial(static::CANVAS_NAME.'canvas.delCanvas');
    }
}

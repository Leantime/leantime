<?php

namespace Leantime\Domain\Goalcanvas\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
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

    private mixed $canvasRepo;

    /**
     * Initializes dependencies.
     */
    public function init(): void
    {
        $repoName = app()->getNamespace().'Domain\\Goalcanvas\\Repositories\\Goalcanvas';
        $this->canvasRepo = app()->make($repoName);
    }

    /**
     * Displays the delete goal item confirmation.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);
        $this->tpl->assign('id', $id);

        return $this->tpl->displayPartial(static::CANVAS_NAME.'canvas.delCanvasItem');
    }

    /**
     * Handles goal item deletion.
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);

        if (isset($_POST['del']) && $id > 0) {
            $this->canvasRepo->delCanvasItem($id);

            $this->tpl->setNotification($this->language->__('notification.element_deleted'), 'success', strtoupper(static::CANVAS_NAME).'canvasitem_deleted');

            return Frontcontroller::redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas/showCanvas');
        }

        $this->tpl->assign('id', $id);

        return $this->tpl->displayPartial(static::CANVAS_NAME.'canvas.delCanvasItem');
    }
}

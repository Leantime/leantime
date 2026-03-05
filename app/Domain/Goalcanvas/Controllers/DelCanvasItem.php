<?php

/**
 * delCanvasItem class - Generic canvas controller / Delete Canvas Item
 */

namespace Leantime\Domain\Goalcanvas\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;

class DelCanvasItem extends Controller
{
    /**
     * Constant that must be redefined
     */
    protected const CANVAS_NAME = 'goal';

    private mixed $canvasRepo;

    /**
     * init - initialize private variables
     */
    public function init(): void
    {
        $repoName = app()->getNamespace().'Domain\\Goalcanvas\\Repositories\\Goalcanvas';
        $this->canvasRepo = app()->make($repoName);
    }

    /**
     * get - display delete confirmation
     *
     * @param  array  $params  Request parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(array $params)
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $id = (int) ($params['id'] ?? 0);

        $this->tpl->assign('id', $id);

        return $this->tpl->displayPartial(static::CANVAS_NAME.'canvas.delCanvasItem');
    }

    /**
     * post - handle delete action
     *
     * @param  array  $params  Request parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function post(array $params)
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $id = (int) (request()->get('id') ?? $params['id'] ?? 0);

        if (isset($params['del']) && $id > 0) {
            $this->canvasRepo->delCanvasItem($id);

            $this->tpl->setNotification(
                $this->language->__('notification.element_deleted'),
                'success',
                strtoupper(static::CANVAS_NAME).'canvasitem_deleted'
            );

            if (request()->header('is-modal')) {
                return response('', 200, ['HX-Trigger' => 'HTMX.closemodal, HTMX.ShowNotification']);
            }

            return Frontcontroller::redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas/showCanvas');
        }

        $this->tpl->assign('id', $id);

        return $this->tpl->displayPartial(static::CANVAS_NAME.'canvas.delCanvasItem');
    }
}

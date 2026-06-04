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
 * Handles deletion of a canvas item.
 */
class DelCanvasItem extends Controller
{
    /**
     * Constant that must be redefined by subclasses.
     */
    protected const CANVAS_NAME = '??';

    private mixed $canvasRepo;

    private BlueprintsService $blueprintsService;

    /**
     * Initializes dependencies.
     */
    public function init(): void
    {
        $this->blueprintsService = app()->make(BlueprintsService::class);
        $canvasName = Str::studly(static::CANVAS_NAME).'canvas';
        $repoName = app()->getNamespace()."Domain\\$canvasName\\Repositories\\$canvasName";
        $this->canvasRepo = app()->make($repoName);
    }

    /**
     * Displays the delete canvas item confirmation.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(BlueprintsPermissions::DELETE)]
    public function get(array $params): Response
    {
        return $this->tpl->displayPartial(static::CANVAS_NAME.'canvas.delCanvasItem');
    }

    /**
     * Handles canvas item deletion.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(BlueprintsPermissions::DELETE, entityScoped: true)]
    public function post(array $params): Response
    {
        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);

        if (isset($_POST['del']) && $id > 0) {
            // The service resolves the item's REAL project and authorizes DELETE against it
            // (throwing for a missing/foreign item) — closing the by-id item-delete IDOR.
            $this->blueprintsService->deleteCanvasItem($id, static::CANVAS_NAME.'canvas');

            $this->tpl->setNotification($this->language->__('notification.element_deleted'), 'success', strtoupper(static::CANVAS_NAME).'canvasitem_deleted');

            return Frontcontroller::redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas/showCanvas');
        }

        return $this->tpl->displayPartial(static::CANVAS_NAME.'canvas.delCanvasItem');
    }
}

<?php

/**
 * canvas class - Generic canvas API controller
 */

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Blueprints\Permissions\BlueprintsPermissions;
use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;
use Symfony\Component\HttpFoundation\Response;

/**
 * @TODO: Could this class be change to abstract? As it is a generic class that should never be initiated!
 */
class Canvas extends Controller
{
    /**
     * Constant that must be redefined
     */
    protected const CANVAS_NAME = '??';

    private BlueprintsService $blueprintsService;

    /**
     * init - initialize private variables
     */
    public function init(): void
    {
        $this->blueprintsService = app()->make(BlueprintsService::class);
    }

    /**
     * get - handle get requests
     */
    public function get(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }

    /**
     * post - handle post requests
     */
    public function post(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }

    /**
     * patch - handle patch requests with authorization check
     */
    #[RequiresPermission(BlueprintsPermissions::EDIT, entityScoped: true)]
    public function patch(array $params): Response
    {
        if (! isset($params['id'])) {
            return $this->tpl->displayJson(['status' => 'failure'], 400);
        }

        // The service resolves the item's REAL project and authorizes EDIT against it (throwing
        // 403 for a missing/foreign item or an insufficient role) before patching — replacing
        // the previous membership-only check with the permission framework. A false return means
        // no allowlisted columns were present (a client error, not a denial).
        if ($this->blueprintsService->patchCanvasItem((int) $params['id'], $params, static::CANVAS_NAME.'canvas') === false) {
            return $this->tpl->displayJson(['status' => 'no valid fields to update'], 400);
        }

        return $this->tpl->displayJson(['status' => 'ok']);
    }

    /**
     * delete - handle delete requests
     */
    public function delete(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }
}

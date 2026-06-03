<?php

declare(strict_types=1);

namespace Leantime\Domain\Blueprints\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Leantime\Domain\Blueprints\Permissions\BlueprintsPermissions;
use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;
use Leantime\Domain\Blueprints\Services\TemplateRegistry;
use Symfony\Component\HttpFoundation\Response;

/**
 * DelCanvasItem controller - handles canvas item deletion.
 *
 * Native Laravel controller: route-bound actions, the {canvasSlug}/{id} path segments
 * arrive via the route (canvasSlug resolved in the constructor, id as a typed action arg),
 * and request input is read from the injected IncomingRequest instead of the legacy
 * merged-$params argument and superglobals.
 */
class DelCanvasItem
{
    private string $canvasSlug;

    private ?CanvasTemplate $template;

    public function __construct(
        private IncomingRequest $request,
        private Template $tpl,
        private Language $language,
        private BlueprintsService $blueprintsService,
        TemplateRegistry $templateRegistry,
    ) {
        $this->canvasSlug = strip_tags((string) ($request->route('canvasSlug') ?? ''));
        $this->template = $templateRegistry->get($this->canvasSlug);
    }

    #[RequiresPermission(BlueprintsPermissions::DELETE)]
    public function get(?string $id = null): Response
    {
        if ($this->template === null) {
            return $this->tpl->displayPartial('errors.error404');
        }

        $this->tpl->assign('canvasSlug', $this->canvasSlug);

        return $this->tpl->displayPartial('blueprints.delCanvasItem');
    }

    #[RequiresPermission(BlueprintsPermissions::DELETE, entityScoped: true)]
    public function post(?string $id = null): Response
    {
        if ($this->template === null) {
            return $this->tpl->displayPartial('errors.error404');
        }

        if ($this->request->has('del') && (int) $id > 0) {
            // The service resolves the item's REAL project and authorizes DELETE against it
            // (throwing 403 for a missing/foreign item) — closing the by-id delete IDOR that
            // the previous role-only Auth::authOrRedirect left open.
            $this->blueprintsService->deleteCanvasItem((int) $id, $this->template->getDatabaseType());

            $this->tpl->setNotification(
                $this->language->__('notification.element_deleted'),
                'success',
                strtoupper($this->canvasSlug).'canvasitem_deleted'
            );

            return Frontcontroller::redirect(BASE_URL.'/blueprints/'.$this->canvasSlug.'/showCanvas');
        }

        $this->tpl->assign('canvasSlug', $this->canvasSlug);

        return $this->tpl->displayPartial('blueprints.delCanvasItem');
    }
}

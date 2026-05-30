<?php

declare(strict_types=1);

namespace Leantime\Domain\Blueprints\Controllers;

use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Leantime\Domain\Blueprints\Repositories\Blueprints as BlueprintsRepository;
use Leantime\Domain\Blueprints\Services\TemplateRegistry;
use Symfony\Component\HttpFoundation\Response;

/**
 * DelCanvas controller - handles canvas board deletion.
 *
 * Native Laravel controller: route-bound actions, the {canvasSlug}/{id} path segments
 * arrive via the route (canvasSlug resolved in the constructor, id as a typed action arg),
 * and request input is read from the injected IncomingRequest instead of the legacy
 * merged-$params argument and superglobals.
 */
class DelCanvas
{
    private string $canvasSlug;

    private ?CanvasTemplate $template;

    public function __construct(
        private IncomingRequest $request,
        private Template $tpl,
        private Language $language,
        private BlueprintsRepository $blueprintsRepo,
        TemplateRegistry $templateRegistry,
    ) {
        $this->canvasSlug = strip_tags((string) ($request->route('canvasSlug') ?? ''));
        $this->template = $templateRegistry->get($this->canvasSlug);
    }

    /**
     * get - display the delete confirmation dialog.
     *
     * @param  string|null  $id  Board id from the route
     */
    public function get(?string $id = null): Response
    {
        if ($this->template === null) {
            return $this->tpl->displayPartial('errors.error404');
        }

        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $this->tpl->assign('canvasSlug', $this->canvasSlug);

        return $this->tpl->displayPartial('blueprints.delCanvas');
    }

    /**
     * post - process the board deletion.
     *
     * @param  string|null  $id  Board id from the route
     */
    public function post(?string $id = null): Response
    {
        if ($this->template === null) {
            return $this->tpl->displayPartial('errors.error404');
        }

        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $canvasType = $this->template->getDatabaseType();
        $sessionKey = $this->template->getSessionKey();

        if ($this->request->has('del') && (int) $id > 0) {
            $id = (int) $id;
            $this->blueprintsRepo->deleteCanvas($id);

            $allCanvas = $this->blueprintsRepo->getAllCanvas(session('currentProject'), $canvasType);
            session([$sessionKey => $allCanvas[0]['id'] ?? -1]);

            $this->tpl->setNotification(
                $this->language->__('notification.board_deleted'),
                'success',
                strtoupper($this->canvasSlug).'canvas_deleted'
            );

            if (! $allCanvas || count($allCanvas) == 0) {
                return Frontcontroller::redirect(BASE_URL.'/strategy/showBoards');
            }

            return Frontcontroller::redirect(BASE_URL.'/blueprints/'.$this->canvasSlug.'/showCanvas');
        }

        $this->tpl->assign('canvasSlug', $this->canvasSlug);

        return $this->tpl->displayPartial('blueprints.delCanvas');
    }
}

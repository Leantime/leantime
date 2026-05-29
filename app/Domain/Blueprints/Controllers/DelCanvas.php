<?php

declare(strict_types=1);

namespace Leantime\Domain\Blueprints\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Leantime\Domain\Blueprints\Repositories\Blueprints as BlueprintsRepository;
use Leantime\Domain\Blueprints\Services\TemplateRegistry;
use Symfony\Component\HttpFoundation\Response;

/**
 * DelCanvas controller - handles canvas board deletion.
 *
 * Replaces the old per-variant Canvas\Controllers\DelCanvas subclasses.
 * The canvas type slug comes from a GET parameter instead of a class constant.
 */
class DelCanvas extends Controller
{
    private BlueprintsRepository $blueprintsRepo;

    private TemplateRegistry $templateRegistry;

    private string $canvasSlug = '';

    private ?CanvasTemplate $template = null;

    /**
     * init - resolve dependencies and determine the canvas slug from request.
     *
     * @param  BlueprintsRepository  $blueprintsRepo  Blueprints repository
     * @param  TemplateRegistry  $templateRegistry  Template registry
     */
    public function init(
        BlueprintsRepository $blueprintsRepo,
        TemplateRegistry $templateRegistry
    ): void {
        $this->blueprintsRepo = $blueprintsRepo;
        $this->templateRegistry = $templateRegistry;

        $this->canvasSlug = strip_tags(request()->route('canvasSlug') ?? ($_GET['canvasSlug'] ?? ''));
        $this->template = $this->templateRegistry->get($this->canvasSlug);
    }

    /**
     * get - display the delete confirmation dialog.
     *
     * @param  array<string, mixed>  $params  Request parameters
     */
    public function get(array $params): Response
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
     * @param  array<string, mixed>  $params  Request parameters (expects 'id')
     */
    public function post(array $params): Response
    {
        if ($this->template === null) {
            return $this->tpl->displayPartial('errors.error404');
        }

        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        $canvasType = $this->template->getDatabaseType();
        $sessionKey = $this->template->getSessionKey();

        if (isset($params['del']) && isset($params['id']) && (int) $params['id'] > 0) {
            $id = (int) $params['id'];
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

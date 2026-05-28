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
 * DelCanvasItem controller - handles canvas item deletion.
 *
 * Replaces the old per-variant Canvas\Controllers\DelCanvasItem subclasses.
 * The canvas type slug comes from a GET parameter instead of a class constant.
 */
class DelCanvasItem extends Controller
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
     * run - display delete confirmation or process the item deletion.
     *
     * @return Response|void
     */
    public function run()
    {
        if ($this->template === null) {
            return $this->tpl->displayPartial('errors.error404');
        }

        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

        if (isset($_POST['del']) && isset($_GET['id'])) {
            $id = (int) ($_GET['id']);
            $this->blueprintsRepo->delCanvasItem($id);

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

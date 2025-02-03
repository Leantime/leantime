<?php

namespace Leantime\Domain\Ideas\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
use Leantime\Domain\Ideas\Services\Ideas as IdeaService;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Symfony\Component\HttpFoundation\Response;
use Leantime\Core\Controller\Frontcontroller;

class IdeaDialog extends HtmxController
{
    protected static string $view = 'ideas::components.idea-item';

    private IdeaService $ideaService;

    private Projects $projectService;

    private IdeaRepository $ideaRepo;

    /**
     * Controller constructor
     */
    public function init(IdeaService $ideaService, IdeaRepository $ideaRepo, Projects $projectService): void
    {
        $this->ideaService = $ideaService;
        $this->projectService = $projectService;
        $this->ideaRepo = $ideaRepo;
    }

    public function patch($params): Response
    {

        $id = (int) ($params['id']);

        if (! AuthService::userIsAtLeast(Roles::$editor)) {
            return $this->tpl->displayJson(['error' => 'Not Authorized'], 403);
        }

        if (! isset($params['id'])) {
            return $this->tpl->displayJson(['error' => 'ID not set'], 400);
        }

        if (! $this->ideaRepo->patchCanvasItem($id, $params)) {
            return $this->tpl->displayJson(['error' => 'Could not update status'], 500);
        }

        return $this->tpl->emptyResponse();
    }
}

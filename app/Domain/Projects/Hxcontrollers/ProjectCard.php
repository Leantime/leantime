<?php

namespace Leantime\Domain\Projects\Hxcontrollers;

use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Menu\Services\Menu;
use Leantime\Domain\Projects\Services\Projects as ProjectService;

class ProjectCard extends HtmxController
{
    protected static string $view = 'projects::partials.projectCard';

    private ProjectService $projectsService;

    private Menu $menuService;

    /**
     * Controller constructor
     *
     * @param  \Leantime\Domain\Projects\Services\Projects  $projectsService  The projects domain service.
     * @param  \Leantime\Domain\Menu\Services\Menu  $menuService  The menu domain service.
     */
    public function init(
        ProjectService $projectsService,
        Menu $menuService
    ): void {
        $this->projectsService = $projectsService;
        $this->menuService = $menuService;
    }

    public function get() {}

    /**
     * Toggles the current user's favorite reaction for a project and re-renders the card.
     */
    public function toggleFavorite(): void
    {
        $projectData = $this->incomingRequest->request->all();

        $projectId = (int) ($projectData['projectId'] ?? 0);
        $isFavorite = $projectData['isFavorite'] ?? false;

        // Both act as the session user, require access to the project and tell the project lists.
        if ($isFavorite) {
            $this->projectsService->unfavorite($projectId);
        } else {
            $this->projectsService->favorite($projectId);
        }

        $this->tpl->assign('project', $this->projectsService->getProject($projectId));
    }

    /**
     * Renders the project card for a project.
     */
    public function getProgress(): void
    {
        $projectId = $_GET['projectId'];

        $projectTypeAvatars = $this->menuService->getProjectTypeAvatars();

        $currentUrlPath = BASE_URL.'/'.str_replace('.', '/', Frontcontroller::getCurrentRoute());

        $this->tpl->assign('projectTypeAvatars', $projectTypeAvatars);
        $this->tpl->assign('currentUrlPath', $currentUrlPath);
        $this->tpl->assign('project', $this->projectsService->getProject($projectId));
        $this->tpl->assign('type', 'full');
    }
}

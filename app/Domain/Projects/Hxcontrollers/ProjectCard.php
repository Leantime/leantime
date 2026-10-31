<?php

namespace Leantime\Domain\Projects\Hxcontrollers;

use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Menu\Services\Menu;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Reactions\Services\Reactions;

class ProjectCard extends HtmxController
{
    protected static string $view = 'projects::partials.projectCard';

    private ProjectService $projectsService;

    private Menu $menuService;

    private Reactions $reactionService;

    /**
     * Controller constructor
     *
     * @param  \Leantime\Domain\Projects\Services\Projects  $projectsService  The projects domain service.
     * @param  \Leantime\Domain\Menu\Services\Menu  $menuService  The menu domain service.
     * @param  \Leantime\Domain\Reactions\Services\Reactions  $reactionService  The reactions domain service.
     */
    public function init(
        ProjectService $projectsService,
        Menu $menuService,
        Reactions $reactionService
    ): void {
        $this->projectsService = $projectsService;
        $this->menuService = $menuService;
        $this->reactionService = $reactionService;
    }

    public function get() {}

    /**
     * Toggles the current user's favorite reaction for a project and re-renders the card.
     */
    public function toggleFavorite(): void
    {
        $projectData = $this->incomingRequest->request->all();

        $projectId = (int) ($projectData['projectId'] ?? 0);

        // Toggle the stored state, not the one the card posted: a card that went stale (favorited
        // in another tab) would otherwise repeat a change that can't apply, and nothing refreshes.
        $project = $this->projectsService->getProject($projectId);
        $isFavorite = is_array($project) && ! empty($project['isFavorite']);

        // react()/unreact() act as the session user, require access to the project and report
        // ProjectFavoriteAdded/Removed, which refresh the project lists.
        if ($isFavorite) {
            $this->reactionService->unreact('project', $projectId, 'favorite');
        } else {
            $this->reactionService->react('project', $projectId, 'favorite');
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

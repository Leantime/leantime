<?php

namespace Leantime\Domain\Projects\Hxcontrollers;

use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Menu\Services\Menu;
use Leantime\Domain\Projects\Services\Projects as ProjectService;

class ProjectHubProjects extends HtmxController
{
    protected static string $view = 'projects::partials.projectHubProjects';

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

    /**
     * Renders the project hub project list partial.
     */
    public function get(): void
    {
        $clientId = (isset($_GET['client']) === true && $_GET['client'] != '') ? (int) $_GET['client'] : null;

        $hubData = $this->projectsService->getProjectHubData(session('userdata.id'), $clientId);

        $currentUrlPath = BASE_URL.'/'.str_replace('.', '/', Frontcontroller::getCurrentRoute());

        $this->tpl->assign('projectTypeAvatars', $this->menuService->getProjectTypeAvatars());
        $this->tpl->assign('currentUrlPath', $currentUrlPath);
        $this->tpl->assign('currentClientName', $hubData['currentClientName']);
        $this->tpl->assign('currentClient', $hubData['currentClient']);
        $this->tpl->assign('clients', $hubData['clients']);
        $this->tpl->assign('allProjects', $hubData['allProjects']);
    }
}

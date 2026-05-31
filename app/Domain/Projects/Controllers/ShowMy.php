<?php

namespace Leantime\Domain\Projects\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Menu\Services\Menu;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Symfony\Component\HttpFoundation\Response;

class ShowMy extends Controller
{
    private ProjectService $projectService;

    private Menu $menuService;

    public function init(
        ProjectService $projectService,
        Menu $menuService
    ): void {
        $this->projectService = $projectService;
        $this->menuService = $menuService;
    }

    /**
     * Displays the project hub for the current user.
     */
    public function get(): Response
    {
        $clientId = (isset($_GET['client']) === true && $_GET['client'] != '') ? (int) $_GET['client'] : null;

        $hubData = $this->projectService->getProjectHubData(session('userdata.id'), $clientId);

        $this->tpl->assign('projectTypeAvatars', $this->menuService->getProjectTypeAvatars());
        $this->tpl->assign('currentClientName', $hubData['currentClientName']);
        $this->tpl->assign('currentClient', $hubData['currentClient']);
        $this->tpl->assign('clients', $hubData['clients']);
        $this->tpl->assign('allProjects', $hubData['allProjects']);

        return $this->tpl->display('projects.projectHub');
    }
}

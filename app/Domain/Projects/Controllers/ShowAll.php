<?php

namespace Leantime\Domain\Projects\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Symfony\Component\HttpFoundation\Response;

class ShowAll extends Controller
{
    private ProjectService $projectService;

    private MenuRepository $menuRepo;

    /**
     * Initializes dependencies.
     */
    public function init(
        ProjectService $projectService,
        MenuRepository $menuRepo
    ): void {
        $this->projectService = $projectService;
        $this->menuRepo = $menuRepo;
    }

    /**
     * Displays the list of all projects.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

        if (! Auth::userIsAtLeast(Roles::$manager)) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        if (! session()->exists('showClosedProjects')) {
            session(['showClosedProjects' => false]);
        }

        $this->tpl->assign('role', session('userdata.role'));

        if (Auth::userIsAtLeast(Roles::$admin)) {
            $this->tpl->assign('allProjects', $this->projectService->getAll(session('showClosedProjects')));
        } else {
            $this->tpl->assign('allProjects', $this->projectService->getClientManagerProjects(session('userdata.id'), session('userdata.clientId')));
        }

        $this->tpl->assign('menuTypes', $this->menuRepo->getMenuTypes());
        $this->tpl->assign('showClosedProjects', session('showClosedProjects'));

        return $this->tpl->display('projects.showAll');
    }

    /**
     * Handles filter changes (show/hide closed projects).
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

        if (isset($_POST['hideClosedProjects'])) {
            session(['showClosedProjects' => false]);
        }

        if (isset($_POST['showClosedProjects'])) {
            session(['showClosedProjects' => true]);
        }

        return $this->get($params);
    }
}

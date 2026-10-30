<?php

namespace Leantime\Domain\Projects\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Projects\Permissions\ProjectsPermissions;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Symfony\Component\HttpFoundation\Response;

class ShowAll extends Controller
{
    private ProjectService $projectService;

    /**
     * Initializes dependencies.
     */
    public function init(ProjectService $projectService): void
    {
        $this->projectService = $projectService;
    }

    /**
     * Displays the list of all projects.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(ProjectsPermissions::EDIT, global: true)]
    public function get(array $params): Response
    {
        if (! session()->exists('showClosedProjects')) {
            session(['showClosedProjects' => false]);
        }

        $this->tpl->assign('role', session('userdata.role'));

        // getAll() returns only the projects this user may see (all of them with projects.viewAll),
        // so it is safe for any admin-level role and for callers without a client. Others see their
        // client's projects.
        $role = session('userdata.role');
        $clientId = (int) session('userdata.clientId');
        if (Roles::isAtLeast($role, Roles::$admin) || Roles::seesAllProjects($role) || $clientId <= 0) {
            $this->tpl->assign('allProjects', $this->projectService->getAll(session('showClosedProjects')));
        } else {
            $this->tpl->assign('allProjects', $this->projectService->getClientManagerProjects((int) session('userdata.id'), $clientId));
        }

        $this->tpl->assign('showClosedProjects', session('showClosedProjects'));

        return $this->tpl->display('projects.showAll');
    }

    /**
     * Handles filter changes (show/hide closed projects).
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(ProjectsPermissions::EDIT, global: true)]
    public function post(array $params): Response
    {
        if (isset($_POST['hideClosedProjects'])) {
            session(['showClosedProjects' => false]);
        }

        if (isset($_POST['showClosedProjects'])) {
            session(['showClosedProjects' => true]);
        }

        return $this->get($params);
    }
}

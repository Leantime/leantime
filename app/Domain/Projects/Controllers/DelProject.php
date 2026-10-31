<?php

namespace Leantime\Domain\Projects\Controllers;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Projects\Permissions\ProjectsPermissions;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Symfony\Component\HttpFoundation\Response;

class DelProject extends Controller
{
    private ProjectService $projectService;

    private PermissionService $permissionService;

    /**
     * Initializes dependencies.
     */
    public function init(ProjectService $projectService, PermissionService $permissionService): void
    {
        $this->projectService = $projectService;
        $this->permissionService = $permissionService;
    }

    /**
     * Displays the delete project confirmation page.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(ProjectsPermissions::DELETE, global: true)]
    public function get(array $params): Response
    {
        if (! isset($params['id'])) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        $id = (int) $params['id'];

        $project = $this->projectService->getProject($id);
        if ($project === false) {
            return Frontcontroller::redirect(BASE_URL.'/errors/error404');
        }

        if ($this->projectService->hasTickets($id)) {
            $this->tpl->setNotification($this->language->__('notification.project_has_tasks'), 'info');
        }

        $this->tpl->assign('project', $project);

        return $this->tpl->display('projects.delProject');
    }

    /**
     * Handles project deletion.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(ProjectsPermissions::DELETE, global: true)]
    public function post(array $params): Response
    {
        if (! isset($params['id'])) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        $id = (int) $params['id'];

        $result = $this->projectService->deleteProject($id);

        if ($result === false) {
            $this->tpl->setNotification($this->language->__('notification.no_permission'), 'error');

            return Frontcontroller::redirect(BASE_URL.$this->projectListPath());
        }

        $this->projectService->resetCurrentProject();
        $this->projectService->setCurrentProject();

        $this->tpl->setNotification($this->language->__('notification.project_deleted'), 'success');

        return Frontcontroller::redirect(BASE_URL.$this->projectListPath());
    }

    /**
     * Where to go after deleting: All Projects needs projects.edit, so delete-only callers go to their own projects.
     */
    private function projectListPath(): string
    {
        return $this->permissionService->currentUserCan(ProjectsPermissions::EDIT) ? '/projects/showAll' : '/projects/showMy';
    }
}

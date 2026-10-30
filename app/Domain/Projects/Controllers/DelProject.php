<?php

namespace Leantime\Domain\Projects\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Projects\Permissions\ProjectsPermissions;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Symfony\Component\HttpFoundation\Response;

class DelProject extends Controller
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

            return Frontcontroller::redirect(BASE_URL.'/projects/showAll');
        }

        $this->projectService->resetCurrentProject();
        $this->projectService->setCurrentProject();

        $this->tpl->setNotification($this->language->__('notification.project_deleted'), 'success');

        return Frontcontroller::redirect(BASE_URL.'/projects/showAll');
    }
}

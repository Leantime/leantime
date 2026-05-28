<?php

namespace Leantime\Domain\Projects\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
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
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

        if (! Auth::userIsAtLeast(Roles::$manager)) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

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
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

        if (! Auth::userIsAtLeast(Roles::$manager)) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        if (! isset($params['id'])) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        $id = (int) $params['id'];

        $this->projectService->deleteProject($id);
        $this->projectService->resetCurrentProject();
        $this->projectService->setCurrentProject();

        $this->tpl->setNotification($this->language->__('notification.project_deleted'), 'success');

        return Frontcontroller::redirect(BASE_URL.'/projects/showAll');
    }
}

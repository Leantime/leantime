<?php

namespace Leantime\Domain\Projects\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Projects\Permissions\ProjectsPermissions;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Symfony\Component\HttpFoundation\Response;

class DuplicateProject extends Controller
{
    private ProjectService $projectService;

    private ClientService $clientService;

    /**
     * Initializes dependencies.
     */
    public function init(
        ProjectService $projectService,
        ClientService $clientService
    ): void {
        $this->projectService = $projectService;
        $this->clientService = $clientService;
    }

    /**
     * Displays the duplicate-project form.
     *
     * @param  array  $params  Request parameters
     *
     * @throws \Exception
     */
    #[RequiresPermission(ProjectsPermissions::CREATE, global: true)]
    public function get(array $params): Response
    {
        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);

        if ($id <= 0) {
            return $this->tpl->displayPartial('errors.error403', responseCode: 403);
        }

        $this->tpl->assign('allClients', $this->clientService->getAll());
        $this->tpl->assign('project', $this->projectService->getProject($id));

        return $this->tpl->displayPartial('projects.duplicateProject');
    }

    /**
     * Handles the project duplication request.
     *
     * @param  array  $params  Request parameters
     *
     * @throws BindingResolutionException
     */
    #[RequiresPermission(ProjectsPermissions::CREATE, global: true)]
    public function post(array $params): Response
    {
        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);
        $assignSameUsers = isset($params['assignSameUsers']);

        $result = $this->projectService->duplicateProject(
            $id,
            (int) $params['clientId'],
            $params['projectName'],
            $params['startDate'] ?? '',
            $assignSameUsers
        );

        $this->tpl->setNotification(
            sprintf($this->language->__('notifications.project_copied_successfully'), BASE_URL.'/projects/changeCurrentProject/'.$result),
            'success'
        );

        return Frontcontroller::redirect(BASE_URL.'/projects/duplicateProject/'.$id);
    }
}

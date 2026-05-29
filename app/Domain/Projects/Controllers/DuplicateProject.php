<?php

namespace Leantime\Domain\Projects\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Clients\Services\Clients as ClientService;
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
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

        $id = (int) ($params['id'] ?? $_GET['id'] ?? 0);

        if (! Auth::userIsAtLeast(Roles::$manager) || $id <= 0) {
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
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

        if (! Auth::userIsAtLeast(Roles::$manager)) {
            return $this->tpl->displayPartial('errors.error403', responseCode: 403);
        }

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

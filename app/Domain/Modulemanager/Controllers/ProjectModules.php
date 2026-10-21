<?php

namespace Leantime\Domain\Modulemanager\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Domain\Modulemanager\Services\Modulemanager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Saves the "Modules" tab of the project settings (see the ProjectModules HxController).
 */
class ProjectModules extends Controller
{
    private Modulemanager $moduleManager;

    /**
     * Initializes dependencies.
     */
    public function init(Modulemanager $moduleManager): void
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Save a project's module switches and go back to the Modules tab.
     *
     * Each switch posts `modules[id]=0` from a hidden field and `modules[id]=1` when checked, so
     * switched-off modules arrive too.
     *
     * @param  array  $params  Request parameters; `id` is the project id.
     *
     * @throws AuthorizationException When the user may not change the project's settings.
     */
    public function post(array $params): Response
    {
        $projectId = (int) ($params['id'] ?? 0);

        if ($projectId <= 0) {
            return Frontcontroller::redirect(BASE_URL.'/errors/error404');
        }

        $modules = is_array($params['modules'] ?? null) ? $params['modules'] : [];

        $this->moduleManager->saveProjectModules($projectId, $modules);
        $this->tpl->setNotification($this->language->__('notification.project_modules_saved'), 'success');

        return Frontcontroller::redirect(BASE_URL.'/projects/showProject/'.$projectId.'#modules');
    }
}

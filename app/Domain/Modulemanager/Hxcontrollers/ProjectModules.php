<?php

namespace Leantime\Domain\Modulemanager\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Modulemanager\Services\Modulemanager;

/**
 * The "Modules" tab of the project settings: one switch per module.
 *
 * Mounted in projects::showProject with <x-global::hx endpoint="modulemanager/projectModules/get">.
 * The form posts to {@see \Leantime\Domain\Modulemanager\Controllers\ProjectModules}, which saves
 * and reloads the settings page so the project menu reflects the change straight away.
 */
class ProjectModules extends HtmxController
{
    protected static string $view = 'modulemanager::partials.projectModules';

    private Modulemanager $moduleManager;

    /**
     * Initializes dependencies.
     */
    public function init(Modulemanager $moduleManager): void
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Render the module switches of a project.
     *
     * @param  array  $params  Request parameters; `id` is the project id.
     */
    public function get(array $params): void
    {
        $projectId = (int) ($params['id'] ?? 0);

        $this->tpl->assign('projectId', $projectId);
        $this->tpl->assign('modules', $projectId > 0 ? $this->moduleManager->getProjectModules($projectId) : []);
    }
}

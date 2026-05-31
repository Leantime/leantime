<?php

namespace Leantime\Domain\Projects\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Modulemanager\Services\Modulemanager;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Symfony\Component\HttpFoundation\Response;

class Createnew extends Controller
{
    private ProjectService $projectService;

    private Modulemanager $modulemanager;

    /**
     * Initializes dependencies.
     */
    public function init(
        Modulemanager $modulemanager,
        ProjectService $projectService
    ): void {
        $this->modulemanager = $modulemanager;
        $this->projectService = $projectService;
    }

    /**
     * Displays the create new project type selection.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

        $projectTypes = [
            'strategy' => [
                'label' => 'label.set_direction',
                'btnLabel' => 'label.create_strategy',
                'description' => 'description.strategy',
                'url' => 'strategyPro/newStrategy',
                'image' => 'undraw_thought_process_re_om58.svg',
                'active' => $this->modulemanager->isModuleAvailable('strategyPro'),
            ],
            'plan' => [
                'label' => 'label.map_steps',
                'btnLabel' => 'label.create_plan',
                'description' => 'description.plan',
                'url' => 'pgmPro/newProgram',
                'image' => 'undraw_join_re_w1lh.svg',
                'active' => $this->modulemanager->isModuleAvailable('pgmPro'),
            ],
            'project' => [
                'label' => 'label.launch_endeavour',
                'btnLabel' => 'label.create_project',
                'description' => 'description.project',
                'url' => 'projects/newProject',
                'image' => 'undraw_complete_task_u2c3.svg',
                'active' => true,
            ],
        ];

        $this->tpl->assign('projectTypes', $projectTypes);

        return $this->tpl->displayPartial('projects.createnew');
    }
}

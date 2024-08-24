<?php

namespace Leantime\Domain\Projects\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Modulemanager\Services\Modulemanager;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;

    /**
     *
     */
    class Createnew extends Controller
    {
        private ProjectService $projectService;
        private Modulemanager $modulemanager;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(
            Modulemanager $modulemanager,
            ProjectService $projectService
        ) {

            $this->modulemanager = $modulemanager;
            $this->projectService = $projectService;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

            $projectTypes = array(
                "strategy" => array(
                    "label" => "label.set_direction",
                    "btnLabel" => "label.create_strategy",
                    "description" => "description.strategy",
                    "url" => "strategyPro/newStrategy",
                    "image" => "undraw_thought_process_re_om58.svg",
                    "active" =>  $this->modulemanager->isModuleAvailable("strategyPro"),
                ),
                "plan" => array(
                    "label" => "label.map_steps",
                    "btnLabel" => "label.create_plan",
                    "description" => "description.plan",
                    "url" => "pgmPro/newProgram",
                    "image" => "undraw_join_re_w1lh.svg",
                    "active" => $this->modulemanager->isModuleAvailable("pgmPro"),
            ),
                "project" => array(
                    "label" => "label.launch_endeavour",
                    "btnLabel" => "label.create_project",
                    "description" => "description.project",
                    "url" => "projects/newProject",
                    "image" => "undraw_complete_task_u2c3.svg",
                    "active" => true,
            ),
            );

            $this->tpl->assign("projectTypes", $projectTypes);


            return $this->tpl->displayPartial('projects.createnew');
        }
    }

}

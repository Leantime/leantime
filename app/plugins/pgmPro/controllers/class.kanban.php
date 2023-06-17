<?php

namespace leantime\plugins\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\plugins\services\pgmPro\programs;

    class kanban extends controller
    {
        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init()
        {

            $this->programService = new programs();

        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $allProjects = $this->programService->getAllProgramProjects($_SESSION['currentProject']);



            $projectStatusLabels = $this->programService->getStatusLabels($_SESSION['currentProject']);

            $this->tpl->assign('allProjects', $allProjects);
            $this->tpl->assign('projectStatusLabels', $projectStatusLabels);

            $this->tpl->display('pgmPro.kanban');

        }
    }

}

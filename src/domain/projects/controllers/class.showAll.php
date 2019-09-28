<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class showAll
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */

        public function run()
        {

            $tpl = new core\template();
            $projectRepo = new repositories\projects();

            $tpl->assign('role', $_SESSION['userdata']['role']);
            $tpl->assign('allProjects', $projectRepo->getUserProjects());

            $tpl->display('projects.showAll');

        }

    }

}

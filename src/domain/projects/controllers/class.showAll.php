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

            if($_SESSION['userdata']['role'] == "manager" || $_SESSION['userdata']['role'] == "admin") {

                $projectRepo = new repositories\projects();

                $tpl->assign('role', $_SESSION['userdata']['role']);
                $tpl->assign('allProjects', $projectRepo->getAll());

                $tpl->display('projects.showAll');
            }else{

                $tpl->display('general.error');

            }

        }

    }

}

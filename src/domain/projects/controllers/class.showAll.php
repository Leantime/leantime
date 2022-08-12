<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;

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

            if(services\auth::userIsAtLeast("clientManager")) {

                $projectRepo = new repositories\projects();

                $tpl->assign('role', $_SESSION['userdata']['role']);

                if(services\auth::userIsAtLeast("manager")) {
                    $tpl->assign('allProjects', $projectRepo->getAll());
                }else{
                    $tpl->assign('allProjects', $projectRepo->getClientProjects(services\auth::getUserClientId()));
                }

                $tpl->display('projects.showAll');
            }else{

                $tpl->display('general.error');

            }

        }

    }

}

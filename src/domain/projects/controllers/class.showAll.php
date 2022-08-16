<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class showAll
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */

        public function run()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager], true);

            $tpl = new core\template();

            if(auth::userIsAtLeast(roles::$manager)) {

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

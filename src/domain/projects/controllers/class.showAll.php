<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\base\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class showAll extends controller
    {

        private $projectRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init() {

            $this->projectRepo = new repositories\projects();

        }

        /**
         * run - display template and edit data
         *
         * @access public
         */

        public function run()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager], true);

            if (auth::userIsAtLeast(roles::$manager)) {

                $this->tpl->assign('role', $_SESSION['userdata']['role']);

                $this->tpl->assign('allProjects', $this->projectRepo->getAll());

                $this->tpl->display('projects.showAll');

            } else {

                $this->tpl->display('general.error');

            }

        }

    }

}

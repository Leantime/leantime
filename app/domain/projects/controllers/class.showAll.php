<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class showAll extends controller
    {
        private repositories\projects $projectRepo;
        private repositories\menu $menuRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(
            repositories\projects $projectRepo,
            repositories\menu $menuRepo
        ) {
            $this->projectRepo = $projectRepo;
            $this->menuRepo = $menuRepo;
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
                if (!isset($_SESSION['showClosedProjects'])) {
                    $_SESSION['showClosedProjects'] = false;
                }

                if (isset($_POST['hideClosedProjects'])) {
                    $_SESSION['showClosedProjects'] = false;
                }

                if (isset($_POST['showClosedProjects'])) {
                    $_SESSION['showClosedProjects'] = true;
                }

                $this->tpl->assign('role', $_SESSION['userdata']['role']);
                $this->tpl->assign('allProjects', $this->projectRepo->getAll($_SESSION['showClosedProjects']));
                $this->tpl->assign('menuTypes', $this->menuRepo->getMenuTypes());

                $this->tpl->assign('showClosedProjects', $_SESSION['showClosedProjects']);

                $this->tpl->display('projects.showAll');
            } else {
                $this->tpl->display('errors.error403');
            }
        }
    }

}

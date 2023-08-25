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
        private services\projects $projectService;


        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(
            repositories\projects $projectRepo,
            repositories\menu $menuRepo,
            services\projects $projectService
        ) {
            $this->projectRepo = $projectRepo;
            $this->projectService = $projectService;
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

                if(auth::userIsAtLeast(roles::$admin)) {
                    $this->tpl->assign('allProjects', $this->projectRepo->getAll($_SESSION['showClosedProjects']));
                }else{
                    $this->tpl->assign('allProjects', $this->projectService->getClientManagerProjects($_SESSION['userdata']['id'], $_SESSION['userdata']['clientId']));
                }
                $this->tpl->assign('menuTypes', $this->menuRepo->getMenuTypes());

                $this->tpl->assign('showClosedProjects', $_SESSION['showClosedProjects']);

                $this->tpl->display('projects.showAll');
            } else {
                $this->tpl->display('errors.error403');
            }
        }
    }

}

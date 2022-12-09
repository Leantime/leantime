<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class delProject extends controller
    {

        private $projectRepo;
        private $projectService;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init()
        {

            $this->projectRepo = new repositories\projects();
            $this->projectService = new services\projects();

        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager], true);

            //Only admins
            if(auth::userIsAtLeast(roles::$manager)) {

                if (isset($_GET['id']) === true) {

                    $id = (int)($_GET['id']);

                    if ($this->projectRepo->hasTickets($id)) {

                        $this->tpl->setNotification($this->language->__("notification.project_has_tasks"), "info");

                    }

                    if (isset($_POST['del']) === true) {

                        $this->projectRepo->deleteProject($id);
                        $this->projectRepo->deleteAllUserRelations($id);

                        $this->projectService->resetCurrentProject();
                        $this->projectService->setCurrentProject();

                        $this->tpl->setNotification($this->language->__("notification.project_deleted"), "success");
                        $this->tpl->redirect(BASE_URL . "/projects/showAll");

                    }

                    //Assign vars
                    $this->tpl->assign('project', $this->projectRepo->getProject($id));

                    $this->tpl->display('projects.delProject');

                } else {

                    $this->tpl->display('errors.error403');

                }

            }else{

                $this->tpl->display('errors.error403');

            }

        }

    }

}

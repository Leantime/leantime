<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class delProject
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
            $projectService = new services\projects();
            $language = new core\language();

            //Only admins
            if(core\login::userIsAtLeast("clientManager")) {

                if (isset($_GET['id']) === true) {

                    $id = (int)($_GET['id']);

                    if ($projectRepo->hasTickets($id)) {

                        $tpl->setNotification($language->__("notification.project_has_tasks"), "info");

                    }

                    if (isset($_POST['del']) === true) {

                        $projectRepo->deleteProject($id);
                        $projectRepo->deleteAllUserRelations($id);

                        $projectService->resetCurrentProject();
                        $projectService->setCurrentProject();

                        $tpl->setNotification($language->__("notification.project_deleted"), "success");
                        $tpl->redirect(BASE_URL . "/projects/showAll");

                    }

                    //Assign vars
                    $tpl->assign('project', $projectRepo->getProject($id));

                    $tpl->display('projects.delProject');

                } else {

                    $tpl->display('general.error');

                }

            }else{

                $tpl->display('general.error');

            }

        }

    }

}

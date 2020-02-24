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

            //Only admins
            if ($_SESSION['userdata']['role'] == 'admin') {

                if (isset($_GET['id']) === true) {

                    $id = (int)($_GET['id']);

                    $msgKey = '';

                    if ($projectRepo->hasTickets($id)) {

                        $msgKey = 'PROJECT_HAS_TICKETS';

                    } else {

                        if (isset($_POST['del']) === true) {

                            $projectRepo->deleteProject($id);
                            $projectRepo->deleteAllUserRelations($id);

                            $projectService->resetCurrentProject();
                            $projectService->setCurrentProject();

                            $msgKey = 'PROJECT_DELETED';

                        }

                    }

                    //Assign vars
                    $tpl->assign('msg', $msgKey);
                    $tpl->assign('project', $projectRepo->getProject($id));

                    $tpl->display('projects.delProject');

                } else {

                    $tpl->display('general.error');

                }

            } else {

                $tpl->display('general.error');

            }

        }

    }

}

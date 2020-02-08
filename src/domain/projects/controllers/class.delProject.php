<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

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
            $language = new core\language();

            //Only admins
            if ($_SESSION['userdata']['role'] == 'admin') {

                if (isset($_GET['id']) === true) {

                    $id = (int)($_GET['id']);

                    $msgKey = '';

                    if ($projectRepo->hasTickets($id)) {

                       $tpl->setNotification($language->__("notification.project_has_tasks"), "error");

                    } else {

                        if (isset($_POST['del']) === true) {

                            $projectRepo->deleteProject($id);
                            $projectRepo->deleteAllUserRelations($id);

                            $tpl->setNotification($language->__("notification.project_deleted"), "success");
                            $tpl->redirect("/projects/showAll");

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

<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class delSprint
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $sprintRepo = new repositories\sprints();
            $language = new core\language();

            //Only admins
            if(core\login::userIsAtLeast("clientManager")) {

                if (isset($_GET['id'])) {
                    $id = (int)($_GET['id']);
                }

                if (isset($_POST['del'])) {

                    $sprintRepo->delSprint($id);

                    $tpl->setNotification($language->__('notifications.sprint_deleted_successfully'), "success");

                    if(isset($_SESSION['lastPage'])) {
                        $tpl->redirect($_SESSION['lastPage']);
                    }else{
                        $tpl->redirect(BASE_URL."/tickets/showKanban");
                    }

                }

                $tpl->assign('id', $id);
                $tpl->displayPartial('sprints.delSprint');

            } else {

                $tpl->displayPartial('general.error');

            }

        }

    }

}

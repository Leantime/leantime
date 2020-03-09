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

            //Only admins
            if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager') {

                if (isset($_GET['id'])) {
                    $id = (int)($_GET['id']);
                }

                $msgKey = '';

                if (isset($_POST['del'])) {

                    $sprintRepo->delSprint($id);

                    $tpl->setNotification("Sprint successfully deleted", "success");
                    $tpl->redirect(BASE_URL.$_SESSION['lastPage']);

                }

                $tpl->assign('info', $msgKey);
                $tpl->assign('id', $id);
                $tpl->displayPartial('sprints.delSprint');

            } else {

                $tpl->displayPartial('general.error');

            }

        }

    }

}

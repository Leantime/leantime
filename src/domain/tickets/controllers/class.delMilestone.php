<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class delMilestone
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $ticketRepo = new repositories\tickets();

            //Only admins
            if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager') {

                if (isset($_GET['id'])) {
                    $id = (int)($_GET['id']);
                }

                $msgKey = '';

                if (isset($_POST['del'])) {

                    $ticketRepo->delMilestone($id);

                    $tpl->setNotification("Milestone successfully deleted", "success");
                    $tpl->redirect(BASE_URL."/tickets/roadmap");

                }

                $tpl->assign('info', $msgKey);
                $tpl->assign('id', $id);
                $tpl->displayPartial('tickets.delMilestone');

            } else {

                $tpl->displayPartial('general.error');

            }

        }

    }

}

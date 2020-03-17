<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class delCanvas
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $leancanvasRepo = new repositories\leancanvas();
            $language = new core\language();

            if (isset($_GET['id'])) {
                $id = (int)($_GET['id']);
            }

            if (isset($_POST['del']) && isset($id)) {

                $leancanvasRepo->deleteCanvas($id);

                $tpl->setNotification($language->__("notification.research_board_deleted"), "success");
                $tpl->redirect(BASE_URL."/leancanvas/simpleCanvas");

            }

            $tpl->display('leancanvas.delCanvas');

        }

    }
}

<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class delCanvas
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);

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

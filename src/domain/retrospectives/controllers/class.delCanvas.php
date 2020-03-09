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
            $retroRepo = new repositories\retrospectives();
            $language = new core\language();

            if (isset($_GET['id'])) {
                $id = (int)($_GET['id']);
            }

            if (isset($_POST['del']) && isset($id)) {

                $retroRepo->deleteCanvas($id);

                $tpl->setNotification($language->__("notification.research_board_deleted"), "success");
                $tpl->redirect(BASE_URL."/retrospectives/showBoards");

            }

            $tpl->display('retrospectives.delCanvas');

        }

    }
}

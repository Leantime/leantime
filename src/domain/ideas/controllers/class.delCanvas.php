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
            $ideaRepo = new repositories\ideas();
            $language = new core\language();

            if (isset($_GET['id'])) {
                $id = (int)($_GET['id']);
            }

            if (isset($_POST['del']) && isset($id)) {

                $ideaRepo->deleteCanvas($id);

                $tpl->setNotification($language->__("notification.idea_board_deleted"), "success");
                $tpl->redirect(BASE_URL."/ideas/showBoards");

            }

            $tpl->display('ideas.delCanvas');

        }

    }
}

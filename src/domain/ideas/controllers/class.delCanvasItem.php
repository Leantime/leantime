<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class delCanvasItem
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $ideasRepo = new repositories\ideas();
            $language = new core\language();

            if (isset($_GET['id'])) {
                $id = (int)($_GET['id']);
            }

            if (isset($_POST['del']) && isset($id)) {

                $ideasRepo->delCanvasItem($id);

                $tpl->setNotification($language->__("notification.idea_board_item_deleted"), "success");

                $tpl->redirect(BASE_URL."/ideas/showBoards");

            }

            $tpl->displayPartial('ideas.delCanvasItem');

        }

    }

}

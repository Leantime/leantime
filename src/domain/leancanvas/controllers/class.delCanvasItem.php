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
            $leancanvasRepo = new repositories\leancanvas();
            $language = new core\language();


            if (isset($_GET['id'])) {
                $id = (int)($_GET['id']);
            }

            if (isset($_POST['del']) && isset($id)) {

                $leancanvasRepo->delCanvasItem($id);

                $tpl->setNotification($language->__("notification.research_board_item_deleted"), "success");
                $tpl->redirect(BASE_URL."/leancanvas/simpleCanvas");

            }

            $tpl->displayPartial('leancanvas.delCanvasItem');

        }

    }

}

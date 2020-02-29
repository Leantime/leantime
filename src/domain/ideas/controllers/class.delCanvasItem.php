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


            if (isset($_GET['id'])) {
                $id = (int)($_GET['id']);
            }

            $msgKey = '';

            if (isset($_POST['del']) && isset($id)) {

                $leancanvasRepo->delCanvasItem($id);

                $msgKey = 'TICKET_DELETED';

                $_SESSION["msg"] = "CANVAS_ITEM_DELETED";
                $_SESSION["msgT"] = "success";
                header("Location:".BASE_URL."/ideas/showBoards/");

            }

            $tpl->displayPartial('ideas.delCanvasItem');

        }

    }

}

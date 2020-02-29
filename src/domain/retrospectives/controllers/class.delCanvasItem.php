<?php

/**
 * delTicket Class - Delete tickets
 *
 * @author     Marcel Folaron <marcel.folaron@gmail.com>
 * @version    1.0
 * @package    modules
 * @subpackage tickets
 * @license    GNU/GPL, see license.txt
 */

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
                header("Location:".BASE_URL."/retrospectives/showBoards/");

            }

            $tpl->displayPartial('retrospectives.delCanvasItem');

        }

    }

}

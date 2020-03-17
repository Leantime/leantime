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
            $retroRepo = new repositories\retrospectives();
            $language = new core\language();


            if (isset($_GET['id'])) {
                $id = (int)($_GET['id']);
            }

            if (isset($_POST['del']) && isset($id)) {

                $retroRepo->delCanvasItem($id);

                $tpl->setNotification($language->__("notification.retrospective_item_deleted"), "success");
                $tpl->redirect(BASE_URL."/retrospectives/showBoards");

            }

            $tpl->displayPartial('retrospectives.delCanvasItem');
        }
    }
}
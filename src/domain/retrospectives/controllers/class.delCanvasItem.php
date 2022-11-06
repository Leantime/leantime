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
    use leantime\base\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class delCanvasItem extends controller
    {

        private $retroRepo;

        /**
         * init - initialize private variables
         */
        private function init()
        {

            $this->retroRepo = new repositories\retrospectives();

        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);

            if (isset($_GET['id'])) {
                $id = (int)($_GET['id']);
            }

            if (isset($_POST['del']) && isset($id)) {

                $this->retroRepo->delCanvasItem($id);

                $this->tpl->setNotification($this->language->__("notification.retrospective_item_deleted"), "success");
                $this->tpl->redirect(BASE_URL."/retrospectives/showBoards");

            }

            $this->tpl->displayPartial('retrospectives.delCanvasItem');

        }

    }

}

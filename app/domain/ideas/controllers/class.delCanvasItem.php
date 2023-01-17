<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class delCanvasItem extends controller
    {
        private $ideasRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init()
        {
            $this->ideasRepo = new repositories\ideas();
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
                $this->ideasRepo->delCanvasItem($id);

                $this->tpl->setNotification($this->language->__("notification.idea_board_item_deleted"), "success");

                $this->tpl->redirect(BASE_URL . "/ideas/showBoards");
            }

            $this->tpl->displayPartial('ideas.delCanvasItem');
        }
    }

}

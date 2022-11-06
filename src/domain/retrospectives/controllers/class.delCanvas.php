<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\base\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class delCanvas extends controller
    {

        private $retroRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init()
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

                $this->retroRepo->deleteCanvas($id);

                $this->tpl->setNotification($this->language->__("notification.research_board_deleted"), "success");
                $this->tpl->redirect(BASE_URL."/retrospectives/showBoards");

            }

            $this->tpl->display('retrospectives.delCanvas');

        }

    }
}

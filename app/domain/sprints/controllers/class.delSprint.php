<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class delSprint extends controller
    {
        private $sprintRepo;

        /**
         * init - initialize private variables
         *
         * @access private
         */
        public function init()
        {

            $this->sprintRepo = new repositories\sprints();
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);

            //Only admins
            if (auth::userIsAtLeast(roles::$editor)) {
                if (isset($_GET['id'])) {
                    $id = (int)($_GET['id']);
                }

                if (isset($_POST['del'])) {
                    $this->sprintRepo->delSprint($id);

                    $this->tpl->setNotification($this->language->__('notifications.sprint_deleted_successfully'), "success");

                    $_SESSION["currentSprint"] = "";

                    if (isset($_SESSION['lastPage'])) {
                        $this->tpl->redirect($_SESSION['lastPage']);
                    } else {
                        $this->tpl->redirect(BASE_URL . "/tickets/showKanban");
                    }
                }

                $this->tpl->assign('id', $id);
                $this->tpl->displayPartial('sprints.delSprint');
            } else {
                $this->tpl->displayPartial('errors.error403');
            }
        }
    }

}

<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class delTime extends controller
    {
        private repositories\timesheets $timesheetsRepo;

        /**
         * init - initialize private variable
         *
         * @access public
         */
        public function init(repositories\timesheets $timesheetsRepo)
        {
            $this->timesheetsRepo = $timesheetsRepo;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor], true);

            $msgKey = '';

            if (isset($_GET['id']) === true) {
                $id = (int)($_GET['id']);

                //Delete User
                if (isset($_POST['del']) === true) {
                    $this->timesheetsRepo->deleteTime($id);

                    $this->tpl->setNotification("notifications.time_deleted_successfully", "success");

                    if (isset($_SESSION['lastPage'])) {
                        $this->tpl->redirect($_SESSION['lastPage']);
                    } else {
                        $this->tpl->redirect(BASE_URL . "/timsheets/showMyList");
                    }
                }

                $this->tpl->assign("id", $id);
                $this->tpl->displayPartial('timesheets.delTime');
            } else {
                $this->tpl->displayPartial('errors.error403');
            }
        }
    }
}

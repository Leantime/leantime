<?php

/**
 * delClient Class - Deleting clients
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\base\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class delEvent extends controller
    {

        private $calendarRepo;

        /**
         * init - initialize private variables
         */
        public function init()
        {

            $this->calendarRepo = new repositories\calendar();

        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {
            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);

            if (isset($_GET['id']) === true) {

                $id = (int)($_GET['id']);

                $msgKey = '';

                if (isset($_POST['del']) === true) {

                    if($this->calendarRepo->delPersonalEvent($id) == true) {

                        $this->tpl->setNotification('notification.event_removed_successfully', 'success');

                    }else{

                        $this->tpl->setNotification('notification.could_not_delete_event', 'success');

                    }

                }

                $this->tpl->display('calendar.delEvent');

            } else {

                $this->tpl->display('general.error');

            }

        }

    }

}

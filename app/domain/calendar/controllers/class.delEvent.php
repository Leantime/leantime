<?php

/**
 * delClient Class - Deleting clients
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class delEvent extends controller
    {
        private services\calendar $calendarService;

        /**
         * init - initialize private variables
         */
        public function init(services\calendar $calendarService)
        {
            $this->calendarService = $calendarService;
            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);
        }

        /**
         * retrieves delete calendar event page data
         *
         * @access public
         *
         */
        public function get() {
            $this->tpl->displayPartial('calendar.delEvent');
        }

        /**
         * sets, creates, and updates edit calendar event page data
         *
         * @access public
         *
         */
        public function post($params) {

            if(isset($_GET['id']) === false){
                $this->tpl->redirect(BASE_URL."/calendar/showMyCalendar/");
            }

            $id = (int)$_GET['id'];

            $result = $this->calendarService->delEvent($id);

            if(is_numeric($result)=== true){
                $this->tpl->setNotification('notification.event_removed_successfully', 'success');
                $this->tpl->redirect(BASE_URL."/calendar/showMyCalendar/");
            }else{
                $this->tpl->setNotification('notification.could_not_delete_event', 'error');
                $this->tpl->displayPartial('calendar.delEvent');
            }
        }

    }

}

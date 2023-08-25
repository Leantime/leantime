<?php

/**
 * delClient Class - Deleting clients
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class delEvent extends controller
    {
        private \leantime\domain\services\calendar $calendarService;

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
            var_dump($params);
            exit();
            $result = $this->calendarService->delEvent($params['id']);

            if(is_numeric($result)=== true){
                $this->tpl->setNotification('notification.event_removed_successfully', 'success');
                $this->tpl->redirect(BASE_URL."/calendar/showMyCalendar/".$result);
            }else{
                $this->tpl->setNotification('notification.could_not_delete_event', 'error');
                $this->tpl->displayPartial('calendar.delEvent');
            }
        }

    }

}

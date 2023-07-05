<?php

/**
 * newClient Class - Add a new client
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class addEvent extends controller
    {
        private \leantime\domain\services\calendar $calendarService;

        /**
         * init - initialize private variables
         */
        public function init()
        {

            $this->calendarService = new \leantime\domain\services\calendar();
            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);

        }

        public function get() {

            $values = array(
                'description' => '',
                'dateFrom' => '',
                'dateTo' => '',
                'allDay' => ''
            );

            $this->tpl->assign('values', $values);
            $this->tpl->displayPartial('calendar.addEvent');
        }

        public function post($params) {


            $result = $this->calendarService->addEvent($params);

            if(is_numeric($result)=== true){
                $this->tpl->setNotification('notification.event_created_successfully', 'success');
                $this->tpl->redirect(BASE_URL."/calendar/editEvent/".$result);

            }else{
                $this->tpl->setNotification('notification.please_enter_title', 'error');
                $this->tpl->assign('values', $params);
                $this->tpl->displayPartial('calendar.addEvent');

           }
        }

    }
}

<?php

/**
 * editEvent Class - Add a new client
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class editEvent extends controller
    {
        private \leantime\domain\services\calendar $calendarService;

        /**
         * init - initialize private variables
         */
        public function init(repositories\calendar $calendarRepo)
        {
            $this->calendarService = new \leantime\domain\services\calendar();
            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);
        }


        /**
         * retrieves edit calendar event page data
         *
         * @access public
         *
         */
        public function get($params) {

            $values = array(
                'description' => '',
                'dateFrom' => '',
                'dateTo' => '',
                'allDay' => '',
                'id' => ''
            );

            $values = $this->calendarService->getEvent($params['id']);

            $this->tpl->assign('values', $values);
            $this->tpl->displayPartial('calendar.editEvent');

        }

        /**
         * sets, creates, and updates edit calendar event page data
         *
         * @access public
         *
         */
        public function post($params) {

            $result = $this->calendarService->editEvent($params);

            if (is_numeric($result) === true) {
                $this->tpl->setNotification('notification.event_edited_successfully', 'success');
                $this->tpl->redirect(BASE_URL . "/calendar/showMyCalendar/".$result);
            } else {
                $this->tpl->setNotification('notification.please_enter_title', 'error');
                $this->tpl->assign('values', $params);
                $this->tpl->displayPartial('calendar.editEvent');
            }
        }
    }
}

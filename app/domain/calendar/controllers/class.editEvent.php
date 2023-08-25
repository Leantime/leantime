<?php

/**
 * editEvent Class - Add a new client
 *
 */

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\services;
    use leantime\domain\services\auth;
    use leantime\domain\services\calendar;

    class editEvent extends controller
    {
        private services\calendar $calendarService;

        /**
         * init - initialize private variables
         */
        public function init(services\calendar $calendarService)
        {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);
            $this->calendarService = $calendarService;
        }


        /**
         * retrieves edit calendar event page data
         *
         * @access public
         *
         */
        public function get($params)
        {

            $values = array(
                'description' => '',
                'dateFrom' => '',
                'dateTo' => '',
                'allDay' => '',
                'id' => '',
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
        public function post($params)
        {

            $params['id'] = $_GET['id'] ?? null;
            $result = $this->calendarService->editEvent($params);

            if ($result === true) {
                $this->tpl->setNotification('notification.event_edited_successfully', 'success');
            } else {
                $this->tpl->setNotification('notification.please_enter_title', 'error');
            }

            $this->tpl->redirect(BASE_URL . '/calendar/editEvent/' . $params['id']);
        }
    }
}

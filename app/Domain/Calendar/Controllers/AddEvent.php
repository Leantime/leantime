<?php

/**
 * newClient Class - Add a new client
 *
 */

namespace Leantime\Domain\Calendar\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;

    class AddEvent extends Controller
    {
        private \Leantime\Domain\Calendar\Services\Calendar $calendarService;

        /**
         * init - initialize private variables
         */
        public function init(\Leantime\Domain\Calendar\Services\Calendar $calendarService)
        {
            $this->calendarService = $calendarService;
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);
        }

        public function get()
        {
            $values = array(
                'description' => '',
                'dateFrom' => '',
                'dateTo' => '',
                'allDay' => '',
            );

            $this->tpl->assign('values', $values);
            $this->tpl->displayPartial('calendar.addEvent');
        }

        public function post($params)
        {
            $result = $this->calendarService->addEvent($params);

            if (is_numeric($result) === true) {
                $this->tpl->setNotification('notification.event_created_successfully', 'success');
                $this->tpl->redirect(BASE_URL . "/calendar/editEvent/" . $result);
            } else {
                $this->tpl->setNotification('notification.please_enter_title', 'error');
                $this->tpl->assign('values', $params);
                $this->tpl->displayPartial('calendar.addEvent');
            }
        }
    }
}

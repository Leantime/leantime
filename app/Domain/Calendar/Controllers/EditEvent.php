<?php

/**
 * editEvent Class - Add a new client
 *
 */

namespace Leantime\Domain\Calendar\Controllers {


    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Calendar\Services\Calendar;
    use Leantime\Core\Frontcontroller;

    /**
     *
     */
    class EditEvent extends Controller
    {
        private CalendarService $calendarService;

        /**
         * init - initialize private variables
         */
        public function init(CalendarService $calendarService)
        {

            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);
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
            return $this->tpl->displayPartial('calendar.editEvent');
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

            return Frontcontroller::redirect(BASE_URL . '/calendar/editEvent/' . $params['id']);
        }
    }
}

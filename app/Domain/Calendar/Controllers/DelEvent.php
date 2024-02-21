<?php

/**
 * delClient Class - Deleting clients
 *
 */

namespace Leantime\Domain\Calendar\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Core\Frontcontroller;

    /**
     *
     */
    class DelEvent extends Controller
    {
        private CalendarService $calendarService;

        /**
         * init - initialize private variables
         */
        public function init(CalendarService $calendarService)
        {
            $this->calendarService = $calendarService;
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);
        }

        /**
         * retrieves delete calendar event page data
         *
         * @access public
         *
         */
        public function get()
        {
            return $this->tpl->displayPartial('calendar.delEvent');
        }

        /**
         * sets, creates, and updates edit calendar event page data
         *
         * @access public
         *
         */
        public function post($params)
        {

            if (isset($_GET['id']) === false) {
                return Frontcontroller::redirect(BASE_URL . "/calendar/showMyCalendar/");
            }

            $id = (int)$_GET['id'];

            $result = $this->calendarService->delEvent($id);

            if (is_numeric($result) === true) {
                $this->tpl->setNotification('notification.event_removed_successfully', 'success');
                return Frontcontroller::redirect(BASE_URL . "/calendar/showMyCalendar/");
            } else {
                $this->tpl->setNotification('notification.could_not_delete_event', 'error');
                return $this->tpl->displayPartial('calendar.delEvent');
            }
        }
    }

}

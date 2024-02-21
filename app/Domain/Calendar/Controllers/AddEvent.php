<?php

/**
 * newClient Class - Add a new client
 *
 */

namespace Leantime\Domain\Calendar\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Calendar\Services\Calendar;
    use Symfony\Component\HttpFoundation\Response;
    use Leantime\Core\Frontcontroller;

    /**
     *
     */
    class AddEvent extends Controller
    {
        private Calendar $calendarService;

        /**
         * init - initialize private variables
         */
        public function init(Calendar $calendarService)
        {
            $this->calendarService = $calendarService;
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);
        }

        /**
         * @return Response
         * @throws \Exception
         */
        public function get(): Response
        {
            $values = array(
                'description' => '',
                'dateFrom' => '',
                'dateTo' => '',
                'allDay' => '',
            );

            $this->tpl->assign('values', $values);
            return $this->tpl->displayPartial('calendar.addEvent');
        }

        /**
         * @param $params
         * @return Response
         * @throws \Exception
         */
        public function post($params): Response
        {
            $result = $this->calendarService->addEvent($params);

            if (is_numeric($result) === true) {
                $this->tpl->setNotification('notification.event_created_successfully', 'success');
                return Frontcontroller::redirect(BASE_URL . "/calendar/editEvent/" . $result);
            } else {
                $this->tpl->setNotification('notification.please_enter_title', 'error');
                $this->tpl->assign('values', $params);
                return $this->tpl->displayPartial('calendar.addEvent');
            }
        }
    }
}

<?php

namespace Leantime\Domain\Api\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth as AuthService;
    use Leantime\Domain\Calendar\Services\Calendar as CalendarService;

    /**
     *
     */
    class Calendar extends Controller
    {
        private CalendarService $calendarSvc;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(CalendarService $calendarSvc)
        {
            $this->calendarSvc = $calendarSvc;
        }

        /**
         * get - handle get requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {
            return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
        }

        /**
         * post - handle post requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function post($params)
        {
            return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
        }

        /**
         * patch - handle patch requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function patch($params)
        {
            if (! AuthService::userIsAtLeast(Roles::$editor)) {
                return $this->tpl->displayJson(['status' => 'failure', 'message' => 'Not authorized'], 401);
            }

            if (! isset($params['id'])) {
                return $this->tpl->displayJson(['status' => 'failure', 'message' => 'ID not set'], 400);
            }

            if (! $this->calendarSvc->patch($params['id'], $params)) {
                return $this->tpl->displayJson(['status' => 'failure'], 500);
            }

            return $this->tpl->displayJson(['status' => 'ok']);
        }

        /**
         * delete - handle delete requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function delete($params)
        {
            return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
        }
    }

}

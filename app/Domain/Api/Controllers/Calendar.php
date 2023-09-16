<?php

namespace Leantime\Domain\Api\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Calendar\Services\Calendar as CalendarService;
    use Leantime\Domain\Auth\Services\Auth as AuthService;
    use Leantime\Domain\Auth\Models\Roles;

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
            echo "{status:'Not implemented'}";
        }

        /**
         * post - handle post requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function post($params)
        {
            echo "{status:'Not implemented'}";
        }

        /**
         * patch - handle patch requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function patch($params)
        {
            if (AuthService::userIsAtLeast(Roles::$editor)) {
                $results = false;

                if (isset($params['id'])) {
                    $results = $this->calendarSvc->patch($params['id'], $params);
                } else {
                    echo "{status:failure, message: 'ID not set'}";
                }

                if ($results === true) {
                    echo "{status:ok}";
                } else {
                    echo "{status:failure}";
                }
            } else {
                echo "{status:failure}";
            }
        }

        /**
         * delete - handle delete requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function delete($params)
        {
            echo "{status:'Not implemented'}";
        }
    }

}

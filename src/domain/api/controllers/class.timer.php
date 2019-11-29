<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\services;

    class timer
    {


        /**
         * constructor - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function __construct()
        {

            $this->timesheetService = new services\timesheets();

        }


        /**
         *
         *
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {

        }


        /**
         *
         *
         *
         * @access public
         * @params parameters or body of the request
         */
        public function post($params)
        {

            if (isset($params["action"]) === true && $params["action"] == "start") {
                $ticketId = filter_var($params["ticketId"], FILTER_SANITIZE_NUMBER_INT);
                $this->timesheetService->punchIn($ticketId);
                echo "{status:ok}";
                return;
            }

            if (isset($params["action"]) === true && $params["action"] == "stop") {
                $ticketId = filter_var($params["ticketId"], FILTER_SANITIZE_NUMBER_INT);
                $hoursBooked = $this->timesheetService->punchOut($ticketId);

                if($hoursBooked) {
                    echo $hoursBooked;
                    return;
                }else{
                    return "{status:failure}";
                }
                return;
            }

            echo "{status:failure}";
            return;

        }

    }

}

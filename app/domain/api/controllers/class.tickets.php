<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;
    use leantime\domain\models\auth\roles;

    class tickets extends controller
    {
        private repositories\projects $projects;
        private services\tickets $ticketsApiService;
        private services\api $apiService;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(
            repositories\projects $projects,
            services\tickets $ticketsApiService,
            services\api $apiService
        ) {
            $this->projects = $projects;
            $this->ticketsApiService = $ticketsApiService;
            $this->apiService = $apiService;
        }

        /**
         * get - handle get requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {
        }

        /**
         * post - handle post requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function post($params)
        {

            ob_start();

            if (services\auth::userIsAtLeast(roles::$editor)) {

                if (isset($params['action']) && $params['action'] == "kanbanSort" && isset($params["payload"]) === true) {
                    $handler = null;
                    if (isset($params["handler"]) == true) {
                        $handler = $params["handler"];
                    }

                    $results = $this->ticketsApiService->updateTicketStatusAndSorting($params["payload"], $handler);

                    if ($results === false) {
                        $this->apiService->setError(-32000, "Could not update status", "");
                    }

                }

                if (isset($params['action']) && $params['action'] == "ganttSort") {

                    $results = $this->ticketsApiService->updateTicketSorting($params["payload"]);

                    if ($results === false) {
                        $this->apiService->setError(-32000, "Could not update status", "");
                    }

                }

            } else {
                $this->apiService->setError(-32000, "Not authorized", "");
            }

            $htmlOutput = ob_get_clean();

            $result = array("html"=>$htmlOutput);
            $this->apiService->jsonResponse(1, $result);
        }

        /**
         * put - handle put requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function patch($params)
        {
            ob_start();

            if (services\auth::userIsAtLeast(roles::$editor)) {
                $results = false;
                if (isset($params['id'])) {
                    $results = $this->ticketsApiService->patchTicket($params['id'], $params);
                } else {
                    $this->apiService->setError(-32000, "ID not set", "");
                }

                if ($results === false) {
                    $this->apiService->setError(-32000, "Could not update status", "");
                }

            } else {
                $this->apiService->setError(-32000, "Not authorized", "");
            }

            $htmlOutput = ob_get_clean();

            $result = array("html"=>$htmlOutput);
            $this->apiService->jsonResponse(1, $result);
        }

        /**
         * delete - handle delete requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function delete($params)
        {
        }
    }

}

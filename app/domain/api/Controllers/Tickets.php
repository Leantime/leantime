<?php

namespace Leantime\Domain\Api\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Leantime\Domain\Api\Services\Api as ApiService;
    use Leantime\Domain\Auth\Services\Auth as AuthService;
    use Leantime\Domain\Auth\Models\Roles;

    class Tickets extends Controller
    {
        private ProjectRepository $projects;
        private TicketService $ticketsApiService;
        private ApiService $apiService;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(
            ProjectRepository $projects,
            TicketService $ticketsApiService,
            ApiService $apiService
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
            if (isset($params['search'])) {
                $searchCriteria = $this->ticketsApiService->prepareTicketSearchArray($params);

                $results = $this->ticketsApiService->getAll($searchCriteria);

                $this->apiService->jsonResponse(1, $results);
            }
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

            if (AuthService::userIsAtLeast(Roles::$editor)) {
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

            $result = array("html" => $htmlOutput);
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

            if (AuthService::userIsAtLeast(Roles::$editor)) {
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

            $result = array("html" => $htmlOutput);
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

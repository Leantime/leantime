<?php

namespace Leantime\Domain\Api\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Api\Services\Api as ApiService;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth as AuthService;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;

    /**
     *
     */
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

                /**
                 * @todo remove this jsonResponse call and instead use Response class.
                 * @see ../Services/Api.php
                 **/
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
            if (! AuthService::userIsAtLeast(Roles::$editor)) {
                return $this->tpl->displayJson(['Error' => 'Not Authorized'], 403);
            }

            if (! isset($params['action'])) {
                return $this->tpl->displayJson(['Error' => 'Action not set'], 400);
            }

            ob_start();

            if (
                $params['action'] == "kanbanSort"
                && isset($params['payload'])
                && ! $this->ticketsApiService->updateTicketStatusAndSorting($params["payload"], $params['handler'] ?? null)
            ) {
                ob_end_clean();
                return $this->tpl->displayJson(['error' => 'Could not update the status'], 500);
            }

            if (
                $params['action'] == 'ganttSort'
                && ! $this->ticketsApiService->updateTicketSorting($params["payload"])
            ) {
                ob_end_clean();
                return $this->tpl->displayJson(['Error' => 'Could not update status'], 500);
            }

            $htmlOutput = ob_get_clean();

            $result = array("html" => $htmlOutput);

            return $this->tpl->displayJson(['result' => $result]);
        }

        /**
         * put - handle put requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function patch($params)
        {
            if (! AuthService::userIsAtLeast(Roles::$editor)) {
                return $this->tpl->displayJson(['error' => 'Not Authorized'], 403);
            }

            if (! isset($params['id'])) {
                return $this->tpl->displayJson(['error' => 'ID not set'], 400);
            }

            ob_start();

            if (! $this->ticketsApiService->patch($params['id'], $params)) {
                ob_end_clean();
                return $this->tpl->displayJson(['error' => 'Could not update status'], 500);
            }

            $htmlOutput = ob_get_clean();

            $result = array("html" => $htmlOutput);

            return $this->tpl->displayJson(['result' => $result]);
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

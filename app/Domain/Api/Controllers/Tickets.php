<?php

namespace Leantime\Domain\Api\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Api\Services\Api as ApiService;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Symfony\Component\HttpFoundation\Response;

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
     *
     * @param ProjectRepository $projects
     * @param TicketService     $ticketsApiService
     * @param ApiService        $apiService
     *
     * @return void
     */
    public function init(
        ProjectRepository $projects,
        TicketService $ticketsApiService,
        ApiService $apiService
    ): void {
        $this->projects = $projects;
        $this->ticketsApiService = $ticketsApiService;
        $this->apiService = $apiService;
    }

    /**
     * get - handle get requests
     *
     * @access public
     *
     * @param array $params parameters or body of the request
     *
     * @return Response
     */
    public function get(array $params): Response
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

        return new Response();
    }

    /**
     * post - handle post requests
     *
     * @access public
     *
     * @param array $params parameters or body of the request
     *
     * @return Response
     *
     * @throws BindingResolutionException
     */
    public function post(array $params): Response
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
            && !$this->ticketsApiService->updateTicketStatusAndSorting($params["payload"], $params['handler'] ?? null)
        ) {
            ob_end_clean();

            return $this->tpl->displayJson(['error' => 'Could not update the status'], 500);
        }

        if (
            $params['action'] == 'ganttSort'
            && !$this->ticketsApiService->updateTicketSorting($params["payload"])
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
     *
     * @param array $params parameters or body of the request
     *
     * @return Response
     */
    public function patch(array $params): Response
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
     *
     * @param array $params parameters or body of the request
     *
     * @return Response
     */
    public function delete(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }
}

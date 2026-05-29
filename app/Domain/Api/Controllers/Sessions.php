<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Api\Services\Api as ApiService;
use Symfony\Component\HttpFoundation\Response;

class Sessions extends Controller
{
    private ApiService $apiService;

    /**
     * init - initialize private variables
     */
    public function init(ApiService $apiService): void
    {
        $this->apiService = $apiService;
    }

    /**
     * @param  array  $params  parameters or body of the request
     */
    public function get(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }

    /**
     * post - handle post requests
     *
     *
     * @param  array  $params  parameters or body of the request
     */
    public function post(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }

    /**
     * put - Special handling for settings
     *
     *
     * @param  array  $params  parameters or body of the request
     */
    public function patch(array $params): Response
    {
        if (isset($params['tourActive'])) {
            $this->apiService->setTourActive($params['tourActive']);

            return $this->tpl->displayJson(['status' => 'ok']);
        }

        if (isset($params['menuState'])) {
            $this->apiService->setMainMenuState($params['menuState']);

            return $this->tpl->displayJson(['status' => 'ok']);
        }

        return $this->tpl->displayJson(['status' => 'failure'], 400);
    }

    /**
     * delete - handle delete requests
     *
     *
     * @param  array  $params  parameters or body of the request
     */
    public function delete(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }
}

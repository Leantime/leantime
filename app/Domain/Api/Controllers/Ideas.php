<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Api\Services\Ideas as IdeasService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Ideas
 *
 * This class represents a controller for handling ideas related requests.
 */
class Ideas extends Controller
{
    private IdeasService $ideasService;

    /**
     * init - initialize private variables
     */
    public function init(IdeasService $ideasService): void
    {
        $this->ideasService = $ideasService;
    }

    /**
     * get - handle get requests
     */
    public function get(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }

    /**
     * post - handle post requests
     */
    public function post(array $params): Response
    {
        if (isset($params['action']) && $params['action'] == 'ideaSort' && isset($params['payload']) === true) {
            if (! $this->ideasService->updateIdeaSorting($params['payload'])) {
                return $this->tpl->displayJson(['status' => 'failure'], 500);
            }

            return $this->tpl->displayJson(['status' => 'ok']);
        }

        if (isset($params['action']) && $params['action'] == 'statusUpdate' && isset($params['payload']) === true) {
            if (! $this->ideasService->bulkUpdateIdeaStatus($params['payload'])) {
                return $this->tpl->displayJson(['status' => 'failure'], 500);
            }

            return $this->tpl->displayJson(['status' => 'ok']);
        }

        return $this->tpl->displayJson(['status' => 'failure'], 500);
    }

    /**
     * put - handle put requests
     */
    public function patch(array $params): Response
    {
        if (! isset($params['id']) || ! $this->ideasService->patchCanvasItem((int) $params['id'], $params)) {
            return $this->tpl->displayJson(['status' => 'failure'], 500);
        }

        return $this->tpl->displayJson(['status' => 'ok']);
    }

    /**
     * delete - handle delete requests
     */
    public function delete(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }
}

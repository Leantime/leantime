<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Api\Services\Ideas as IdeasService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Ideation
 */
class Ideation extends Controller
{
    private IdeasService $ideasService;

    /**
     * constructor - initialize private variables
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
        if (
            ! isset($params['action'], $params['payload'])
            || ! in_array($params['action'], ['ideationSort', 'statusUpdate'])
        ) {
            return $this->tpl->displayJson(['status' => 'failure'], 400);
        }

        $success = match ($params['action']) {
            'ideationSort' => $this->ideasService->updateIdeationSorting($params['payload']),
            'statusUpdate' => $this->ideasService->bulkUpdateIdeationStatus($params['payload']),
        };

        if (! $success) {
            return $this->tpl->displayJson(['status' => 'failure'], 500);
        }

        return $this->tpl->displayJson(['status' => 'ok']);
    }

    /**
     * put - handle put requests
     */
    public function patch(array $params): Response
    {
        if (! $this->ideasService->patchCanvasItem((int) $params['id'], $params)) {
            return $this->tpl->displayJson(['status', 'failure'], 500);
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

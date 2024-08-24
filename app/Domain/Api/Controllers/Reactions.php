<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Reactions\Services\Reactions as ReactionService;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class Reactions extends Controller
{
    private ReactionService $reactionService;

    /**
     * init - initialize private variables
     *
     * @access public
     *
     * @param ReactionService $reactionService
     *
     * @return void
     */
    public function init(ReactionService $reactionService): void
    {
        $this->reactionService = $reactionService;
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
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }

    /**
     * post - handle post requests
     *
     * @access public
     *
     * @param array $params parameters or body of the request
     *
     * @return Response
     */
    public function post(array $params): Response
    {
        if ($params["action"] == "add") {
            if (! $this->reactionService->addReaction(session("userdata.id"), $params['module'], $params['moduleId'], $params['reaction'])) {
                return $this->tpl->displayJson(['status' => 'failure'], 500);
            }

            return $this->tpl->displayJson(['status' => 'ok']);
        }

        if ($params["action"] == "remove") {
            if (! $this->reactionService->removeReaction(session("userdata.id"), $params['module'], $params['moduleId'], $params['reaction'])) {
                return $this->tpl->displayJson(['status' => 'failure'], 500);
            }

            return $this->tpl->displayJson(['status' => 'ok']);
        }

        return $this->tpl->displayJson(['error' => 'Bad Request'], 400);
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
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
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

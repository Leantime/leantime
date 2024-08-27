<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Ideas
 *
 * This class represents a controller for handling ideas related requests.
 */
class Ideas extends Controller
{
    private ProjectRepository $projects;
    private IdeaRepository $ideaAPIRepo;

    /**
     * init - initialize private variables
     *
     * @access public
     *
     * @param ProjectRepository $projects
     * @param IdeaRepository    $ideaAPIRepo
     *
     * @return void
     */
    public function init(ProjectRepository $projects, IdeaRepository $ideaAPIRepo): void
    {
        // @TODO: projects is never used in this class?
        $this->projects = $projects;
        $this->ideaAPIRepo = $ideaAPIRepo;
    }

    /**
     * get - handle get requests
     *
     * @access public
     *
     * @param array $params
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
     * @param array $params
     *
     * @return Response
     */
    public function post(array $params): Response
    {
        if (isset($params['action']) && $params['action'] == "ideaSort" && isset($params["payload"]) === true) {
            if (! $this->ideaAPIRepo->updateIdeaSorting($params['payload'])) {
                return $this->tpl->displayJson(['status' => 'failure'], 500);
            }

            return $this->tpl->displayJson(['status' => 'ok']);
        }

        if (isset($params['action']) && $params['action'] == "statusUpdate" && isset($params["payload"]) === true) {
            if (! $this->ideaAPIRepo->bulkUpdateIdeaStatus($params["payload"])) {
                return $this->tpl->displayJson(['status' => 'failure'], 500);
            }

            return $this->tpl->displayJson(['status' => 'ok']);
        }

        return $this->tpl->displayJson(['status' => 'failure'], 500);
    }

    /**
     * put - handle put requests
     *
     * @access public
     *
     * @param array $params
     *
     * @return Response
     */
    public function patch(array $params): Response
    {
        if (!isset($params['id']) || !$this->ideaAPIRepo->patchCanvasItem($params['id'], $params)) {
            return $this->tpl->displayJson(['status' => 'failure'], 500);
        }

        return $this->tpl->displayJson(['status' => 'ok']);
    }

    /**
     * delete - handle delete requests
     *
     * @access public
     *
     * @param array $params
     *
     * @return Response
     */
    public function delete(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }
}

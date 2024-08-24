<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Ideas\Repositories\Ideas as IdeationRepository;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Ideation
 */
class Ideation extends Controller
{
    private ProjectRepository $projects;
    private IdeationRepository $ideationAPIRepo;

    /**
     * constructor - initialize private variables
     *
     * @access public
     *
     * @param ProjectRepository  $projects
     * @param IdeationRepository $ideationAPIRepo
     *
     * @return void
     */
    public function init(ProjectRepository $projects, IdeationRepository $ideationAPIRepo): void
    {
        // @TODO: projects is never used in this class?
        $this->projects = $projects;
        $this->ideationAPIRepo = $ideationAPIRepo;
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
        if (
            !isset($params['action'], $params['payload'])
            || !in_array($params['action'], ['ideationSort', 'statusUpdate'])
        ) {
            return $this->tpl->displayJson(['status' => 'failure'], 400);
        }

        // @TODO: The two update functions do not seam to exists in ideas repository class?
        foreach (
            [
            'ideationSort' => fn () => $this->ideationAPIRepo->updateIdeationSorting($params['payload']),
            'statusUpdate' => fn () => $this->ideationAPIRepo->bulkUpdateIdeationStatus($params["payload"]),
            ] as $param => $callback
        ) {
            if ($param !== $params['action']) {
                continue;
            }

            if (! $callback()) {
                return $this->tpl->displayJson(['status' => 'failure'], 500);
            }

            break;
        }

        return $this->tpl->displayJson(['status' => 'ok']);
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
        if (! $this->ideationAPIRepo->patchCanvasItem($params['id'], $params)) {
            return $this->tpl->displayJson(['status', 'failure'], 500);
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

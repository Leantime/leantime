<?php

/**
 * canvas class - Generic canvas API controller
 */

namespace Leantime\Domain\Api\Controllers;

use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * @TODO: Could this class be change to abstract? As it is a generic class that should never be initiated!
 */
class Canvas extends Controller
{
    /**
     * Constant that must be redefined
     */
    protected const CANVAS_NAME = '??';

    private ProjectRepository $projects;

    /**
     * @var Closure|mixed|object|null
     */
    private mixed $canvasRepo;

    /**
     * constructor - initialize private variables
     *
     *
     *
     * @throws BindingResolutionException
     */
    public function init(ProjectRepository $projects): void
    {
        // @TODO: project are never used in this class?
        $this->projects = $projects;
        $canvasName = Str::studly(static::CANVAS_NAME).'canvas';
        $repoName = app()->getNamespace()."Domain\\$canvasName\\Repositories\\$canvasName";
        $this->canvasRepo = app()->make($repoName);
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
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }

    /**
     * patch - handle patch requests with authorization check
     */
    public function patch(array $params): Response
    {
        if (! isset($params['id'])) {
            return $this->tpl->displayJson(['status' => 'failure'], 400);
        }

        // Verify the canvas item exists and user has access to its project
        $canvasItem = $this->canvasRepo->getSingleCanvasItem($params['id']);
        if ($canvasItem === false) {
            return $this->tpl->displayJson(['status' => 'not found'], 404);
        }

        $canvas = $this->canvasRepo->getSingleCanvas($canvasItem['canvasId']);
        if ($canvas === false || empty($canvas)) {
            return $this->tpl->displayJson(['status' => 'not found'], 404);
        }

        $projectId = $canvas[0]['projectId'] ?? null;
        if ($projectId === null || ! $this->projects->isUserAssignedToProject(session('userdata.id'), $projectId)) {
            return $this->tpl->displayJson(['status' => 'unauthorized'], 403);
        }

        if (! $this->canvasRepo->patchCanvasItem($params['id'], $params)) {
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

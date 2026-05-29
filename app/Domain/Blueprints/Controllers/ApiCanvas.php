<?php

declare(strict_types=1);

namespace Leantime\Domain\Blueprints\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Leantime\Domain\Blueprints\Repositories\Blueprints as BlueprintsRepository;
use Leantime\Domain\Blueprints\Services\TemplateRegistry;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * ApiCanvas controller - handles PATCH requests for inline canvas item updates.
 *
 * Provides the API endpoint used by the blueprintsController.js for inline
 * status, relates, and user dropdown updates on the canvas board.
 */
class ApiCanvas extends Controller
{
    private BlueprintsRepository $blueprintsRepo;

    private TemplateRegistry $templateRegistry;

    private ProjectRepository $projects;

    private string $canvasSlug = '';

    private ?CanvasTemplate $template = null;

    /**
     * init - resolve dependencies and determine the canvas slug from request.
     *
     * @param  BlueprintsRepository  $blueprintsRepo  Blueprints repository
     * @param  TemplateRegistry  $templateRegistry  Template registry
     * @param  ProjectRepository  $projects  Project repository (for access checks)
     */
    public function init(
        BlueprintsRepository $blueprintsRepo,
        TemplateRegistry $templateRegistry,
        ProjectRepository $projects
    ): void {
        $this->blueprintsRepo = $blueprintsRepo;
        $this->templateRegistry = $templateRegistry;
        $this->projects = $projects;

        $this->canvasSlug = strip_tags(request()->route('canvasSlug') ?? ($_GET['canvasSlug'] ?? ''));
        $this->template = $this->templateRegistry->get($this->canvasSlug);
    }

    /**
     * get - handle GET requests (not implemented).
     *
     * @param  array<string, mixed>  $params  Request parameters
     */
    public function get(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }

    /**
     * post - handle POST requests (not implemented).
     *
     * @param  array<string, mixed>  $params  Request parameters
     */
    public function post(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }

    /**
     * patch - handle PATCH requests for inline canvas item updates.
     *
     * Supports updating status, relates, and author fields on individual
     * canvas items via AJAX calls from the board view dropdowns.
     *
     * @param  array<string, mixed>  $params  Request parameters (must include 'id')
     */
    public function patch(array $params): Response
    {
        if ($this->template === null) {
            return $this->tpl->displayJson(['status' => 'Unknown canvas type'], 404);
        }

        if (! isset($params['id'])) {
            return $this->tpl->displayJson(['status' => 'failure'], 400);
        }

        // Verify the item exists and the user has access to its project before patching.
        $canvasItem = $this->blueprintsRepo->getSingleCanvasItem((int) $params['id']);
        if ($canvasItem === false) {
            return $this->tpl->displayJson(['status' => 'not found'], 404);
        }

        $canvas = $this->blueprintsRepo->getSingleCanvas((int) $canvasItem['canvasId'], $this->template->getDatabaseType());
        if ($canvas === false || empty($canvas)) {
            return $this->tpl->displayJson(['status' => 'not found'], 404);
        }

        $projectId = $canvas[0]['projectId'] ?? null;
        if ($projectId === null || ! $this->projects->isUserAssignedToProject(session('userdata.id'), $projectId)) {
            return $this->tpl->displayJson(['status' => 'unauthorized'], 403);
        }

        // patchCanvasItem returns false when no allowlisted columns are present — a client error.
        if (! $this->blueprintsRepo->patchCanvasItem((int) $params['id'], $params)) {
            return $this->tpl->displayJson(['status' => 'no valid fields to update'], 400);
        }

        return $this->tpl->displayJson(['status' => 'ok']);
    }

    /**
     * delete - handle DELETE requests (not implemented).
     *
     * @param  array<string, mixed>  $params  Request parameters
     */
    public function delete(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }
}

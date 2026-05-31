<?php

declare(strict_types=1);

namespace Leantime\Domain\Blueprints\Controllers;

use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
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
class ApiCanvas
{
    private string $canvasSlug;

    private ?CanvasTemplate $template;

    /**
     * __construct - resolve dependencies and determine the canvas slug from request.
     *
     * @param  IncomingRequest  $request  Incoming request
     * @param  Template  $tpl  Template handler
     * @param  Language  $language  Language handler
     * @param  BlueprintsRepository  $blueprintsRepo  Blueprints repository
     * @param  ProjectRepository  $projects  Project repository (for access checks)
     * @param  TemplateRegistry  $templateRegistry  Template registry
     */
    public function __construct(
        private IncomingRequest $request,
        private Template $tpl,
        private Language $language,
        private BlueprintsRepository $blueprintsRepo,
        private ProjectRepository $projects,
        TemplateRegistry $templateRegistry,
    ) {
        $this->canvasSlug = strip_tags((string) ($request->route('canvasSlug') ?? ''));
        $this->template = $templateRegistry->get($this->canvasSlug);
    }

    /**
     * patch - handle PATCH requests for inline canvas item updates.
     *
     * Supports updating status, relates, and author fields on individual
     * canvas items via AJAX calls from the board view dropdowns.
     */
    public function patch(): Response
    {
        $data = $this->request->getRequestParams();

        if ($this->template === null) {
            return $this->tpl->displayJson(['status' => 'Unknown canvas type'], 404);
        }

        if (! isset($data['id'])) {
            return $this->tpl->displayJson(['status' => 'failure'], 400);
        }

        // Verify the item exists and the user has access to its project before patching.
        $canvasItem = $this->blueprintsRepo->getSingleCanvasItem((int) $data['id']);
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
        if (! $this->blueprintsRepo->patchCanvasItem((int) $data['id'], $data)) {
            return $this->tpl->displayJson(['status' => 'no valid fields to update'], 400);
        }

        return $this->tpl->displayJson(['status' => 'ok']);
    }
}

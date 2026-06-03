<?php

declare(strict_types=1);

namespace Leantime\Domain\Blueprints\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Leantime\Domain\Blueprints\Permissions\BlueprintsPermissions;
use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;
use Leantime\Domain\Blueprints\Services\TemplateRegistry;
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
     * @param  BlueprintsService  $blueprintsService  Blueprints service (project-authorized item CRUD)
     * @param  TemplateRegistry  $templateRegistry  Template registry
     */
    public function __construct(
        private IncomingRequest $request,
        private Template $tpl,
        private Language $language,
        private BlueprintsService $blueprintsService,
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
    #[RequiresPermission(BlueprintsPermissions::EDIT, entityScoped: true)]
    public function patch(): Response
    {
        $data = $this->request->getRequestParams();

        if ($this->template === null) {
            return $this->tpl->displayJson(['status' => 'Unknown canvas type'], 404);
        }

        if (! isset($data['id'])) {
            return $this->tpl->displayJson(['status' => 'failure'], 400);
        }

        // The service resolves the item's REAL project and authorizes EDIT against it before
        // patching — closing the by-id cross-project mutation IDOR (a missing/foreign item or
        // an insufficient role throws AuthorizationException -> 403). A false return means no
        // allowlisted columns were present, which is a client error, not a denial.
        if (! $this->blueprintsService->patchCanvasItem((int) $data['id'], $data, $this->template->getDatabaseType())) {
            return $this->tpl->displayJson(['status' => 'no valid fields to update'], 400);
        }

        return $this->tpl->displayJson(['status' => 'ok']);
    }
}

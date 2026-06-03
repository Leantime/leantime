<?php

declare(strict_types=1);

namespace Leantime\Domain\Blueprints\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Leantime\Domain\Blueprints\Permissions\BlueprintsPermissions;
use Leantime\Domain\Blueprints\Services\BlueprintsExport;
use Leantime\Domain\Blueprints\Services\TemplateRegistry;
use Symfony\Component\HttpFoundation\Response;

/**
 * Export controller - exports a blueprint canvas board as an XML file.
 *
 * Thin controller: resolves the board id and delegates XML generation to the
 * BlueprintsExport service. The canvas type slug comes from the route.
 */
class Export
{
    private string $canvasSlug;

    private ?CanvasTemplate $template;

    /**
     * __construct - resolve dependencies and determine the canvas slug from the request.
     *
     * @param  IncomingRequest  $request  Incoming request
     * @param  Template  $tpl  Template engine
     * @param  Language  $language  Language service
     * @param  BlueprintsExport  $exportService  Blueprints export service
     * @param  TemplateRegistry  $templateRegistry  Template registry
     */
    public function __construct(
        private IncomingRequest $request,
        private Template $tpl,
        private Language $language,
        private BlueprintsExport $exportService,
        TemplateRegistry $templateRegistry,
    ) {
        $this->canvasSlug = strip_tags((string) ($request->route('canvasSlug') ?? ''));
        $this->template = $templateRegistry->get($this->canvasSlug);
    }

    /**
     * get - generate and return the XML export file.
     *
     * @param  string|null  $id  Board id from the route
     */
    #[RequiresPermission(BlueprintsPermissions::VIEW, entityScoped: true)]
    public function get(?string $id = null): Response
    {
        if ($this->template === null) {
            return new Response('Unknown canvas type', 404);
        }

        // Resolve the board id from the route/query, falling back to the session.
        $sessionKey = $this->template->getSessionKey();
        if ($id !== null && $id !== '') {
            $canvasId = (int) $id;
        } elseif (session()->exists($sessionKey)) {
            $canvasId = (int) session($sessionKey);
        } else {
            return new Response('', 204);
        }

        $exportData = $this->exportService->exportToXml($canvasId, $this->canvasSlug);
        if ($exportData === null) {
            return new Response('Canvas not found', 404);
        }

        clearstatcache();
        $response = new Response($exportData);
        $response->headers->set('Content-type', 'application/xml');
        $response->headers->set(
            'Content-Disposition',
            'attachment; filename="'.$this->template->getDatabaseType().'-'.$canvasId.'.xml"'
        );
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }
}

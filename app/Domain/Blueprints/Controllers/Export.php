<?php

declare(strict_types=1);

namespace Leantime\Domain\Blueprints\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Leantime\Domain\Blueprints\Services\BlueprintsExport;
use Leantime\Domain\Blueprints\Services\TemplateRegistry;
use Symfony\Component\HttpFoundation\Response;

/**
 * Export controller - exports a blueprint canvas board as an XML file.
 *
 * Thin controller: resolves the board id and delegates XML generation to the
 * BlueprintsExport service. The canvas type slug comes from the route.
 */
class Export extends Controller
{
    private BlueprintsExport $exportService;

    private TemplateRegistry $templateRegistry;

    private string $canvasSlug = '';

    private ?CanvasTemplate $template = null;

    /**
     * init - resolve dependencies and determine the canvas slug from the request.
     *
     * @param  BlueprintsExport  $exportService  Blueprints export service
     * @param  TemplateRegistry  $templateRegistry  Template registry
     */
    public function init(
        BlueprintsExport $exportService,
        TemplateRegistry $templateRegistry
    ): void {
        $this->exportService = $exportService;
        $this->templateRegistry = $templateRegistry;

        $this->canvasSlug = strip_tags(request()->route('canvasSlug') ?? ($_GET['canvasSlug'] ?? ''));
        $this->template = $this->templateRegistry->get($this->canvasSlug);
    }

    /**
     * get - generate and return the XML export file.
     *
     * @param  array<string, mixed>  $params  Request parameters
     */
    public function get(array $params): Response
    {
        if ($this->template === null) {
            return new Response('Unknown canvas type', 404);
        }

        // Resolve the board id from the route/query, falling back to the session.
        $sessionKey = $this->template->getSessionKey();
        if (isset($params['id']) && $params['id'] !== '') {
            $canvasId = (int) $params['id'];
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

<?php

declare(strict_types=1);

namespace Leantime\Domain\Blueprints\Controllers;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Leantime\Domain\Blueprints\Repositories\Blueprints as BlueprintsRepository;
use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;
use Leantime\Domain\Blueprints\Services\TemplateRegistry;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * Export controller - exports a blueprint canvas board as an XML file.
 *
 * Replaces the old per-variant Canvas\Controllers\Export subclasses.
 * The canvas type slug comes from a GET parameter instead of a class constant.
 */
class Export extends Controller
{
    protected EnvironmentCore $config;

    protected LanguageCore $language;

    private BlueprintsRepository $blueprintsRepo;

    private BlueprintsService $blueprintsService;

    private TemplateRegistry $templateRegistry;

    private string $canvasSlug = '';

    private ?CanvasTemplate $template = null;

    /** @var array<string, array<string, mixed>> */
    private array $canvasTypes = [];

    /** @var array<string, array<string, mixed>> */
    private array $statusLabels = [];

    /** @var array<string, array<string, mixed>> */
    private array $relatesLabels = [];

    /** @var array<int, array<string, mixed>> */
    private array $dataLabels = [];

    /**
     * init - resolve dependencies, determine canvas slug, and pre-translate labels.
     *
     * @param  EnvironmentCore  $config  Environment configuration
     * @param  LanguageCore  $language  Language service
     * @param  BlueprintsRepository  $blueprintsRepo  Blueprints repository
     * @param  BlueprintsService  $blueprintsService  Blueprints service
     * @param  TemplateRegistry  $templateRegistry  Template registry
     */
    public function init(
        EnvironmentCore $config,
        LanguageCore $language,
        BlueprintsRepository $blueprintsRepo,
        BlueprintsService $blueprintsService,
        TemplateRegistry $templateRegistry
    ): void {
        $this->config = $config;
        $this->language = $language;
        $this->blueprintsRepo = $blueprintsRepo;
        $this->blueprintsService = $blueprintsService;
        $this->templateRegistry = $templateRegistry;

        $this->canvasSlug = strip_tags(request()->route('canvasSlug') ?? ($_GET['canvasSlug'] ?? ''));
        $this->template = $this->templateRegistry->get($this->canvasSlug);

        if ($this->template !== null) {
            $this->canvasTypes = $this->blueprintsService->getTranslatedBoxes($this->template);
            $this->statusLabels = $this->blueprintsService->getTranslatedStatusLabels($this->template);
            $this->relatesLabels = $this->blueprintsService->getTranslatedRelatesLabels($this->template);
            $this->dataLabels = $this->blueprintsService->getTranslatedDataLabels($this->template);
        }
    }

    /**
     * run - generate and return the XML export file.
     */
    public function run(): Response
    {
        if ($this->template === null) {
            return new Response('Unknown canvas type', 404);
        }

        $sessionKey = $this->template->getSessionKey();
        $canvasType = $this->template->getDatabaseType();
        $commentModule = $this->template->getCommentModule();

        // Retrieve id of canvas to export
        if (isset($_GET['id']) === true) {
            $canvasId = (int) $_GET['id'];
        } elseif (session()->exists($sessionKey)) {
            $canvasId = session($sessionKey);
        } else {
            return new Response;
        }

        $exportData = $this->export($canvasId, $canvasType, $commentModule);

        clearstatcache();
        $response = new Response($exportData);
        $response->headers->set('Content-type', 'application/xml');
        $response->headers->set(
            'Content-Disposition',
            'attachment; filename="'.$canvasType.'-'.$canvasId.'.xml"'
        );
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }

    /**
     * export - generate XML data for a canvas board.
     *
     * @param  int  $id  Canvas board identifier
     * @param  string  $canvasType  Database canvas type
     * @param  string  $commentModule  Comment module identifier
     * @return string XML data
     *
     * @throws BindingResolutionException
     * @throws Exception
     */
    protected function export(int $id, string $canvasType, string $commentModule): string
    {
        $canvasAry = $this->blueprintsRepo->getSingleCanvas($id, $canvasType);
        ! empty($canvasAry) || throw new Exception("Cannot find canvas with id '$id'");
        $projectId = $canvasAry[0]['projectId'];
        $recordsAry = $this->blueprintsRepo->getCanvasItemsById($id, $commentModule);
        $projectsRepo = app()->make(ProjectRepository::class);
        $projectAry = $projectsRepo->getProject($projectId);
        ! empty($projectAry) || throw new Exception("Cannot retrieve project id '$projectId'");

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>'.PHP_EOL.PHP_EOL;
        $xml .= $this->xmlExport($canvasType, $canvasAry[0]['title'], $recordsAry);

        return $xml;
    }

    /**
     * xmlExport - generate XML markup for canvas data.
     *
     * @param  string  $canvasKey  Encoded canvas name (database type)
     * @param  string  $canvasTitle  Canvas board title
     * @param  array<int, array<string, mixed>>  $recordsAry  Array of canvas item records
     * @param  int  $indent  Indent level to use
     * @return string XML data
     */
    protected function xmlExport(string $canvasKey, string $canvasTitle, array $recordsAry, int $indent = 0): string
    {
        $is = str_repeat(' ', 4 * $indent);
        $tab = str_repeat(' ', 4);
        $xml = $is.'<canvas key="'.$canvasKey.'">'.PHP_EOL;
        $xml .= $is.$tab.'<title>'.$canvasTitle.'</title>'.PHP_EOL;
        $xml .= $is.$tab.'<content>'.PHP_EOL;

        foreach ($this->canvasTypes as $key => $data) {
            $xml .= $is.$tab.$tab.'<element key="'.$key.'">'.PHP_EOL;

            foreach ($recordsAry as $record) {
                if ($record['box'] === $key) {
                    $xml .= $is.$tab.$tab.$tab.'<item>'.PHP_EOL;
                    $xml .= $is.$tab.$tab.$tab.$tab.'<created>'.($record['created'] ?? '').'</created>'.PHP_EOL;
                    $xml .= $is.$tab.$tab.$tab.$tab.'<modified>'.($record['modified'] ?? '').'</modified>'.PHP_EOL;
                    $xml .= $is.$tab.$tab.$tab.$tab.'<author id="'.$record['author'].'" firstname="'.($record['authorFirstname'] ?? '').'" '.
                        'lastname="'.($record['authorLastname'] ?? '').'"/>'.PHP_EOL;

                    $xml .= $is.$tab.$tab.$tab.$tab.'<description>'.($record['description'] ?? '').'</description>'.PHP_EOL;
                    $xml .= $is.$tab.$tab.$tab.$tab.'<status key="'.($record['status'] ?? '').'" />'.PHP_EOL;
                    $xml .= $is.$tab.$tab.$tab.$tab.'<relates key="'.($record['relates'] ?? '').'" />'.PHP_EOL;
                    $xml .= $is.$tab.$tab.$tab.$tab.'<assumptions>'.($record['assumptions'] ?? '').'</assumptions>'.PHP_EOL;
                    $xml .= $is.$tab.$tab.$tab.$tab.'<data>'.($record['data'] ?? '').'</data>'.PHP_EOL;
                    $xml .= $is.$tab.$tab.$tab.$tab.'<conclusion>'.($record['conclusion'] ?? '').'</conclusion>'.PHP_EOL;
                    $xml .= $is.$tab.$tab.$tab.$tab.'</item>'.PHP_EOL;
                }
            }

            $xml .= $is.$tab.$tab.'</element>'.PHP_EOL;
        }
        $xml .= $is.$tab.'</content>'.PHP_EOL;
        $xml .= $is.'</canvas>'.PHP_EOL;

        return $xml;
    }
}

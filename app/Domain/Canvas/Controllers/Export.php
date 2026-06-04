<?php

namespace Leantime\Domain\Canvas\Controllers;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Blueprints\Permissions\BlueprintsPermissions;
use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exports a canvas board as an XML file.
 */
class Export extends Controller
{
    /**
     * Constant that must be redefined by subclasses.
     */
    protected const CANVAS_NAME = '??';

    protected const CANVAS_TYPE = 'canvas';

    protected EnvironmentCore $config;

    protected LanguageCore $language;

    protected mixed $canvasRepo;

    protected BlueprintsService $blueprintsService;

    protected array $canvasTypes;

    protected array $statusLabels;

    protected array $relatesLabels;

    protected array $dataLabels;

    /**
     * Initializes dependencies.
     */
    public function init(
        EnvironmentCore $config,
        LanguageCore $language,
    ): void {
        $this->config = $config;
        $this->language = $language;
        $this->blueprintsService = app()->make(BlueprintsService::class);

        $canvasName = Str::studly(static::CANVAS_NAME).static::CANVAS_TYPE;
        $repoName = app()->getNamespace()."Domain\\$canvasName\\Repositories\\$canvasName";
        $this->canvasRepo = app()->make($repoName);

        $this->canvasTypes = $this->canvasRepo->getCanvasTypes();
        $this->statusLabels = $this->canvasRepo->getStatusLabels();
        $this->relatesLabels = $this->canvasRepo->getRelatesLabels();
        $this->dataLabels = $this->canvasRepo->getDataLabels();
    }

    /**
     * Generates and serves an XML export of the canvas.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(BlueprintsPermissions::VIEW, entityScoped: true)]
    public function get(array $params): Response
    {
        $canvasId = (int) ($params['id'] ?? $_GET['id'] ?? 0);

        if ($canvasId <= 0 && session()->exists('current'.strtoupper(static::CANVAS_NAME).'Canvas')) {
            $canvasId = (int) session('current'.strtoupper(static::CANVAS_NAME).'Canvas');
        }

        if ($canvasId <= 0) {
            return new Response;
        }

        $exportData = $this->export($canvasId);

        clearstatcache();

        $response = new Response($exportData);
        $response->headers->set('Content-type', 'application/xml');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.static::CANVAS_NAME.static::CANVAS_TYPE.'-'.$canvasId.'.xml"');
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }

    /**
     * Generates XML data for the given canvas.
     *
     * @param  int  $id  Canvas identifier
     * @return string XML data
     *
     * @throws BindingResolutionException
     * @throws Exception
     */
    protected function export(int $id): string
    {
        // getBoard authorizes VIEW against the board's real project and returns false for a
        // missing/foreign/unauthorized board (export is reachable by arbitrary board id).
        $canvasAry = $this->blueprintsService->getBoard($id, static::CANVAS_NAME.static::CANVAS_TYPE);
        ! empty($canvasAry) || throw new Exception("Cannot find canvas with id '$id'");

        $projectId = $canvasAry[0]['projectId'];
        $recordsAry = $this->blueprintsService->getBoardItems($id, static::CANVAS_NAME.static::CANVAS_TYPE, static::CANVAS_NAME.'canvasitem');

        $projectService = app()->make(ProjectService::class);
        $projectAry = $projectService->getProject($projectId);
        ! empty($projectAry) || throw new Exception("Cannot retrieve project id '$projectId'");

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>'.PHP_EOL.PHP_EOL;
        $xml .= $this->xmlExport(static::CANVAS_NAME.static::CANVAS_TYPE, $canvasAry[0]['title'], $recordsAry);

        return $xml;
    }

    /**
     * Generates XML markup for canvas data.
     *
     * @param  string  $canvasKey  Encoded canvas name
     * @param  string  $canvasTitle  Canvas title
     * @param  array  $recordsAry  Array of canvas entry records
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
                    $xml .= $is.$tab.$tab.$tab.'</item>'.PHP_EOL;
                }
            }

            $xml .= $is.$tab.$tab.'</element>'.PHP_EOL;
        }
        $xml .= $is.$tab.'</content>'.PHP_EOL;
        $xml .= $is.'</canvas>'.PHP_EOL;

        return $xml;
    }
}

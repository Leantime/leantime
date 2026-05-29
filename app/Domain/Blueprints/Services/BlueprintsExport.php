<?php

declare(strict_types=1);

namespace Leantime\Domain\Blueprints\Services;

use Leantime\Domain\Blueprints\Repositories\Blueprints as BlueprintsRepository;

/**
 * BlueprintsExport service - builds the XML export for a blueprint canvas board.
 *
 * The XML generation used to live in the Export controller; it is business logic
 * and belongs in the service layer so the controller stays thin.
 *
 * @api
 */
class BlueprintsExport
{
    /**
     * @param  BlueprintsRepository  $blueprintsRepo  Blueprints repository
     * @param  Blueprints  $blueprintsService  Blueprints service (label translation)
     * @param  TemplateRegistry  $templateRegistry  Canvas template registry
     */
    public function __construct(
        private BlueprintsRepository $blueprintsRepo,
        private Blueprints $blueprintsService,
        private TemplateRegistry $templateRegistry,
    ) {}

    /**
     * exportToXml - generate the XML document for a canvas board.
     *
     * @param  int  $canvasId  Canvas board identifier
     * @param  string  $canvasSlug  Canvas type slug (e.g. "swot")
     * @return string|null XML document, or null if the canvas type or board does not exist
     *
     * @api
     */
    public function exportToXml(int $canvasId, string $canvasSlug): ?string
    {
        $template = $this->templateRegistry->get($canvasSlug);
        if ($template === null) {
            return null;
        }

        $canvasType = $template->getDatabaseType();
        $canvasAry = $this->blueprintsRepo->getSingleCanvas($canvasId, $canvasType);
        if (empty($canvasAry)) {
            return null;
        }

        $records = $this->blueprintsRepo->getCanvasItemsById($canvasId, $template->getCommentModule());
        $canvasTypes = $this->blueprintsService->getTranslatedBoxes($template);

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>'.PHP_EOL.PHP_EOL;
        $xml .= $this->buildXml($canvasType, $canvasAry[0]['title'], $records, $canvasTypes);

        return $xml;
    }

    /**
     * buildXml - generate XML markup for canvas data.
     *
     * @param  string  $canvasKey  Database canvas type (e.g. "swotcanvas")
     * @param  string  $canvasTitle  Canvas board title
     * @param  array<int, array<string, mixed>>  $records  Canvas item records
     * @param  array<string, array<string, mixed>>  $canvasTypes  Translated box definitions
     * @param  int  $indent  Indent level
     * @return string XML data
     */
    private function buildXml(string $canvasKey, string $canvasTitle, array $records, array $canvasTypes, int $indent = 0): string
    {
        $is = str_repeat(' ', 4 * $indent);
        $tab = str_repeat(' ', 4);
        $xml = $is.'<canvas key="'.$canvasKey.'">'.PHP_EOL;
        $xml .= $is.$tab.'<title>'.$canvasTitle.'</title>'.PHP_EOL;
        $xml .= $is.$tab.'<content>'.PHP_EOL;

        foreach ($canvasTypes as $key => $data) {
            $xml .= $is.$tab.$tab.'<element key="'.$key.'">'.PHP_EOL;

            foreach ($records as $record) {
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

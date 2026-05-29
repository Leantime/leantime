<?php

declare(strict_types=1);

namespace Leantime\Domain\Goalcanvas\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas as GoalcanvaRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * Goal canvas XML export. Standalone, independent of the canvas domain.
 */
class Export extends Controller
{
    private const CANVAS_TYPE = 'goalcanvas';

    private const COMMENT_MODULE = 'goalcanvasitem';

    private const SESSION_KEY = 'currentGOALCanvas';

    private GoalcanvaRepository $canvasRepo;

    /**
     * init - resolve dependencies.
     *
     * @param  GoalcanvaRepository  $canvasRepo  Goal canvas repository
     */
    public function init(GoalcanvaRepository $canvasRepo): void
    {
        $this->canvasRepo = $canvasRepo;
    }

    /**
     * get - generate and return the goal canvas as an XML file.
     *
     * @param  array<string, mixed>  $params  Request parameters
     */
    public function get(array $params): Response
    {
        if (isset($params['id']) && $params['id'] !== '') {
            $canvasId = (int) $params['id'];
        } elseif (session()->exists(self::SESSION_KEY)) {
            $canvasId = (int) session(self::SESSION_KEY);
        } else {
            return new Response('', 204);
        }

        $canvas = $this->canvasRepo->getSingleCanvas($canvasId);
        if (! $canvas) {
            return new Response('Canvas not found', 404);
        }

        $records = $this->canvasRepo->getCanvasItemsById($canvasId, self::COMMENT_MODULE);
        $canvasTypes = $this->canvasRepo->getCanvasTypes();

        $exportData = $this->buildXml($canvas['title'] ?? '', $records, $canvasTypes);

        clearstatcache();
        $response = new Response($exportData);
        $response->headers->set('Content-type', 'application/xml');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.self::CANVAS_TYPE.'-'.$canvasId.'.xml"');
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }

    /**
     * buildXml - render the canvas board and its items as XML.
     *
     * @param  string  $canvasTitle  Board title
     * @param  array<int, array<string, mixed>>  $records  Canvas item records
     * @param  array<string, array<string, mixed>>  $canvasTypes  Translated box definitions
     */
    private function buildXml(string $canvasTitle, array $records, array $canvasTypes): string
    {
        $tab = str_repeat(' ', 4);
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>'.PHP_EOL.PHP_EOL;
        $xml .= '<canvas key="'.self::CANVAS_TYPE.'">'.PHP_EOL;
        $xml .= $tab.'<title>'.$canvasTitle.'</title>'.PHP_EOL;
        $xml .= $tab.'<content>'.PHP_EOL;

        foreach ($canvasTypes as $key => $data) {
            $xml .= $tab.$tab.'<element key="'.$key.'">'.PHP_EOL;

            foreach ($records as $record) {
                if ($record['box'] === $key) {
                    $xml .= $tab.$tab.$tab.'<item>'.PHP_EOL;
                    $xml .= $tab.$tab.$tab.$tab.'<created>'.($record['created'] ?? '').'</created>'.PHP_EOL;
                    $xml .= $tab.$tab.$tab.$tab.'<modified>'.($record['modified'] ?? '').'</modified>'.PHP_EOL;
                    $xml .= $tab.$tab.$tab.$tab.'<author id="'.($record['author'] ?? '').'" firstname="'.($record['authorFirstname'] ?? '').'" '.
                        'lastname="'.($record['authorLastname'] ?? '').'"/>'.PHP_EOL;
                    $xml .= $tab.$tab.$tab.$tab.'<description>'.($record['description'] ?? '').'</description>'.PHP_EOL;
                    $xml .= $tab.$tab.$tab.$tab.'<status key="'.($record['status'] ?? '').'" />'.PHP_EOL;
                    $xml .= $tab.$tab.$tab.$tab.'<relates key="'.($record['relates'] ?? '').'" />'.PHP_EOL;
                    $xml .= $tab.$tab.$tab.$tab.'<assumptions>'.($record['assumptions'] ?? '').'</assumptions>'.PHP_EOL;
                    $xml .= $tab.$tab.$tab.$tab.'<data>'.($record['data'] ?? '').'</data>'.PHP_EOL;
                    $xml .= $tab.$tab.$tab.$tab.'<conclusion>'.($record['conclusion'] ?? '').'</conclusion>'.PHP_EOL;
                    $xml .= $tab.$tab.$tab.'</item>'.PHP_EOL;
                }
            }

            $xml .= $tab.$tab.'</element>'.PHP_EOL;
        }
        $xml .= $tab.'</content>'.PHP_EOL;
        $xml .= '</canvas>'.PHP_EOL;

        return $xml;
    }
}

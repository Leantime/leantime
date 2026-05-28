<?php

namespace Leantime\Domain\Strategy\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Canvas\Services\Canvas as CanvaService;
use Symfony\Component\HttpFoundation\Response;

class ShowBoards extends Controller
{
    private CanvaService $canvasService;

    /**
     * Initializes dependencies.
     */
    public function init(CanvaService $canvasService): void
    {
        $this->canvasService = $canvasService;
    }

    /**
     * Displays the strategy boards overview.
     *
     * @param  array  $params  Request parameters
     *
     * @throws \Exception
     */
    public function get(array $params): Response
    {
        $level1 = [];
        $level2 = [];
        $level3 = [];

        $others = [
            'valuecanvas' => ['module' => 'valuecanvas',       'name' => 'label.valuecanvas',  'description' => 'description.valuecanvas', 'icon' => 'fa-solid fa-ranking-star',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'swotcanvas' => ['module' => 'swotcanvas',     'name' => 'label.swotcanvas', 'description' => 'description.swotcanvas', 'icon' => 'fa-solid fa-dumbbell',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'obmcanvas' => ['module' => 'obmcanvas',     'name' => 'label.obmcanvas',       'description' => 'description.obmcanvas', 'icon' => 'fa-solid fa-object-group', 'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'leancanvas' => ['module' => 'leancanvas',     'name' => 'label.leancanvas',       'description' => 'description.leancanvas', 'icon' => 'fa-solid fa-person-circle-question', 'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'minempathycanvas' => ['module' => 'minempathycanvas',       'name' => 'label.minempathycanvas',  'description' => 'description.minempathycanvas', 'icon' => 'fa-solid fa-heart-circle-check',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'sbcanvas' => ['module' => 'sbcanvas',       'name' => 'label.sbcanvas',  'description' => 'description.sbcanvas',           'icon' => 'fa-solid fa-briefcase',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'riskscanvas' => ['module' => 'riskscanvas',    'name' => 'label.riskscanvas',  'description' => 'description.riskscanvas',        'icon' => 'fa-solid fa-triangle-exclamation',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'eacanvas' => ['module' => 'eacanvas',       'name' => 'label.eacanvas', 'description' => 'description.eacanvas', 'icon' => 'fa-solid fa-seedling',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'lbmcanvas' => ['visible' => '0', 'module' => 'lbmcanvas',      'name' => 'label.lbmcanvas', 'description' => 'description.lbmcanvas', 'icon' => 'fa-solid fa-building',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'dbmcanvas' => ['visible' => '0', 'module' => 'dbmcanvas',      'name' => 'label.dbmcanvas', 'description' => 'description.dbmcanvas', 'icon' => 'fa-solid fa-city',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'sqcanvas' => ['visible' => '0', 'module' => 'sqcanvas',       'name' => 'label.sqcanvas', 'description' => 'description.sqcanvas', 'icon' => 'fa fa-chess',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'insightscanvas' => ['module' => 'insightscanvas', 'name' => 'label.insightscanvas', 'description' => 'description.insightscanvas',      'icon' => 'fa-solid fa-arrows-down-to-people',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'cpcanvas' => ['visible' => '0', 'module' => 'cpcanvas',       'name' => 'label.cpcanvas', 'description' => 'description.cpcanvas', 'icon' => 'fa-solid fa-list-check',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'smcanvas' => ['visible' => '0', 'module' => 'smcanvas',       'name' => 'label.smcanvas', 'description' => 'description.smcanvas', 'icon' => 'fa-solid fa-comments-dollar',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'emcanvas' => ['visible' => '0', 'module' => 'emcanvas',       'name' => 'label.emcanvas', 'description' => 'description.emcanvas', 'icon' => 'fa-solid fa-hand-holding-heart',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
        ];

        $boards = [
            'emcanvas', 'smcanvas', 'cpcanvas', 'insightscanvas',
            'sqcanvas', 'dbmcanvas', 'lbmcanvas', 'eacanvas', 'riskscanvas', 'sbcanvas',
            'swotcanvas', 'obmcanvas', 'valuecanvas', 'leancanvas', 'minempathycanvas',
        ];

        $recentlyUpdatedCanvas = $this->canvasService->getLastUpdatedCanvas((int) session('currentProject'), $boards);

        $recentProgressCanvas = [];

        foreach ($recentlyUpdatedCanvas as $canvas) {
            if (! isset($recentProgressCanvas[$canvas['type']])) {
                $recentProgressCanvas[$canvas['type']] = $others[$canvas['type']];
                $recentProgressCanvas[$canvas['type']]['count'] = 1;
                $recentProgressCanvas[$canvas['type']]['lastTitle'] = $canvas['title'];
                $recentProgressCanvas[$canvas['type']]['lastUpdate'] = $canvas['modified'];
                $recentProgressCanvas[$canvas['type']]['lastCanvasId'] = $canvas['id'];
                unset($others[$canvas['type']]);
            } else {
                $recentProgressCanvas[$canvas['type']]['count']++;
            }
        }

        $this->tpl->assign('recentProgressCanvas', $recentProgressCanvas);

        $canvasProgress = $this->canvasService->getBoardProgress(session('currentProject'), $boards);

        $this->tpl->assign('recentlyUpdatedCanvas', $recentlyUpdatedCanvas);
        $this->tpl->assign('canvasProgress', $canvasProgress);

        $this->tpl->assign('level1Boards', $level1);
        $this->tpl->assign('level2Boards', $level2);
        $this->tpl->assign('level3Boards', $level3);
        $this->tpl->assign('otherBoards', $others);

        return $this->tpl->display('strategy.showBoards');
    }
}

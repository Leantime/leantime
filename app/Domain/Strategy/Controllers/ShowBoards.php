<?php

namespace Leantime\Domain\Strategy\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Canvas\Services\Canvas as CanvaService;
use Symfony\Component\HttpFoundation\Response;

class ShowBoards extends Controller
{
    private CanvaService $canvasService;

    public function init(CanvaService $canvasService): void
    {
        $this->canvasService = $canvasService;
    }

    /**
     * @throws \Exception
     */
    public function run(): Response
    {

        // Ideate
        //
        // Validate
        // Empathize - Is it a problem
        // Business - Do users want it
        // Feasibility - Can we build it
        //
        // Define
        // Value
        // Metrics
        //
        // Plan
        // Timeline
        //
        // Execute
        //

        // Menu
        // Dashboard
        // Todos
        // Milestones
        // Planning
        // Project Brief
        // Risks
        // Definition

        // Validation
        // Empathy Maps
        // Lean Canvas
        // Environment
        // Ideation
        // Documents
        // Reports

        // Level 1: Validate
        $level1 = [];

        // Level 2: Define
        $level2 = [];

        // Level 3: Planning
        $level3 = [];

        // Everything else
        $others = [
            'valuecanvas' =>
            ['module' => 'valuecanvas',       'name' => 'label.valuecanvas',  'description' => 'description.valuecanvas', 'icon' => 'military_tech',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'swotcanvas' =>
            ['module' => 'swotcanvas',     'name' => 'label.swotcanvas', 'description' => 'description.swotcanvas', 'icon' => 'fitness_center',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],

            'obmcanvas' =>
            ['module' => 'obmcanvas',     'name' => 'label.obmcanvas',       'description' => 'description.obmcanvas', 'icon' => 'select_all', 'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],

            'leancanvas' =>
            ['module' => 'leancanvas',     'name' => 'label.leancanvas',       'description' => 'description.leancanvas', 'icon' => 'help', 'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'minempathycanvas' =>
                ['module' => 'minempathycanvas',       'name' => 'label.minempathycanvas',  'description' => 'description.minempathycanvas', 'icon' => 'favorite',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],

            'sbcanvas' =>
            ['module' => 'sbcanvas',       'name' => 'label.sbcanvas',  'description' => 'description.sbcanvas',           'icon' => 'work',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'riskscanvas' =>
            ['module' => 'riskscanvas',    'name' => 'label.riskscanvas',  'description' => 'description.riskscanvas',        'icon' => 'warning',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'eacanvas' =>
            ['module' => 'eacanvas',       'name' => 'label.eacanvas', 'description' => 'description.eacanvas', 'icon' => 'spa',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'lbmcanvas' =>
            ['visible' => '0', 'module' => 'lbmcanvas',      'name' => 'label.lbmcanvas', 'description' => 'description.lbmcanvas', 'icon' => 'apartment',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'dbmcanvas' =>
            ['visible' => '0', 'module' => 'dbmcanvas',      'name' => 'label.dbmcanvas', 'description' => 'description.dbmcanvas', 'icon' => 'location_city',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'sqcanvas' =>
            ['visible' => '0', 'module' => 'sqcanvas',       'name' => 'label.sqcanvas', 'description' => 'description.sqcanvas', 'icon' => 'extension',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'insightscanvas' =>
            ['module' => 'insightscanvas', 'name' => 'label.insightscanvas', 'description' => 'description.insightscanvas',      'icon' => 'groups',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'cpcanvas' =>
            ['visible' => '0', 'module' => 'cpcanvas',       'name' => 'label.cpcanvas', 'description' => 'description.cpcanvas', 'icon' => 'checklist',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'smcanvas' =>
            ['visible' => '0', 'module' => 'smcanvas',       'name' => 'label.smcanvas', 'description' => 'description.smcanvas', 'icon' => 'request_quote',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'emcanvas' =>
            ['visible' => '0', 'module' => 'emcanvas',       'name' => 'label.emcanvas', 'description' => 'description.emcanvas', 'icon' => 'volunteer_activism',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],

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

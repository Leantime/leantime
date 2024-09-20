<?php

namespace Leantime\Domain\Strategy\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Canvas\Services\Canvas as CanvaService;
use Symfony\Component\HttpFoundation\Response;

class ShowBoards extends Controller
{
    private CanvaService $canvasService;

    /**
     * @param CanvaService $canvasService
     *
     * @return void
     */
    public function init(CanvaService $canvasService): void
    {
        $this->canvasService = $canvasService;
    }

    /**
     * @throws \Exception
     *
     * @return Response
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

        //Menu
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

        //Level 1: Validate
        $level1 = [];

        //Level 2: Define
        $level2 = [];

        //Level 3: Planning<i class=""></i>
        $level3 = [];

        //Everything else
        $others = [
            'valuecanvas' =>
            //Empathy Min
            ['module' => 'valuecanvas',       'name' => 'label.valuecanvas',  'description' => 'description.valuecanvas', 'icon' => 'fa-solid fa-ranking-star',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'swotcanvas' =>
            //Swot Analysis
            ['module' => 'swotcanvas',     'name' => 'label.swotcanvas', 'description' => 'description.swotcanvas', 'icon' => 'fa-solid fa-dumbbell',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],

            'obmcanvas' =>
            //Lean Canvas
            ['module' => 'obmcanvas',     'name' => 'label.obmcanvas',       'description' => 'description.obmcanvas', 'icon' => 'fa-solid fa-object-group', 'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],

            'leancanvas' =>
            //Lean Canvas
            ['module' => 'leancanvas',     'name' => 'label.leancanvas',       'description' => 'description.leancanvas', 'icon' => 'fa-solid fa-person-circle-question', 'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'minempathycanvas' =>
            //Empathy Min
                ['module' => 'minempathycanvas',       'name' => 'label.minempathycanvas',  'description' => 'description.minempathycanvas', 'icon' => 'fa-solid fa-heart-circle-check',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],

            'sbcanvas' =>
            //Project Brief<i class=""></i>
            ['module' => 'sbcanvas',       'name' => 'label.sbcanvas',  'description' => 'description.sbcanvas',           'icon' => 'fa-solid fa-briefcase',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'riskscanvas' =>
            //Risks
            ['module' => 'riskscanvas',    'name' => 'label.riskscanvas',  'description' => 'description.riskscanvas',        'icon' => 'fa-solid fa-triangle-exclamation',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'eacanvas' =>
            //Environment
            ['module' => 'eacanvas',       'name' => 'label.eacanvas', 'description' => 'description.eacanvas', 'icon' => 'fa-solid fa-seedling',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'lbmcanvas' =>
            //Lightweight Business Model<i class=""></i>
            ['visible' => '0', 'module' => 'lbmcanvas',      'name' => 'label.lbmcanvas', 'description' => 'description.lbmcanvas', 'icon' => 'fa-solid fa-building',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'dbmcanvas' =>
            //Detailed Business Model<i class=""></i>
            ['visible' => '0', 'module' => 'dbmcanvas',      'name' => 'label.dbmcanvas', 'description' => 'description.dbmcanvas', 'icon' => 'fa-solid fa-city',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'sqcanvas' =>
            //Strategy Questions
            ['visible' => '0', 'module' => 'sqcanvas',       'name' => 'label.sqcanvas', 'description' => 'description.sqcanvas', 'icon' => 'fa fa-chess',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'insightscanvas' =>
            //Ethnographics<i class=""></i>
            ['module' => 'insightscanvas', 'name' => 'label.insightscanvas', 'description' => 'description.insightscanvas',      'icon' => 'fa-solid fa-arrows-down-to-people',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'cpcanvas' =>
            //Competitive Canvas (Jobs to be done V2)<i class=""></i>
            ['visible' => '0', 'module' => 'cpcanvas',       'name' => 'label.cpcanvas', 'description' => 'description.cpcanvas', 'icon' => 'fa-solid fa-list-check',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'smcanvas' =>
            //Strategy Messaging / Positioning<i class=""></i>
            ['visible' => '0', 'module' => 'smcanvas',       'name' => 'label.smcanvas', 'description' => 'description.smcanvas', 'icon' => 'fa-solid fa-comments-dollar',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'emcanvas' =>
            //Full empathy Map<i class=""></i>
            ['visible' => '0', 'module' => 'emcanvas',       'name' => 'label.emcanvas', 'description' => 'description.emcanvas', 'icon' => 'fa-solid fa-hand-holding-heart',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],

        ];

        $boards = [
            'emcanvas', 'smcanvas', 'cpcanvas', 'insightscanvas',
            'sqcanvas', 'dbmcanvas', 'lbmcanvas', 'eacanvas', 'riskscanvas', 'sbcanvas',
            'swotcanvas', 'obmcanvas', 'valuecanvas', 'leancanvas', 'minempathycanvas',
        ];

        $recentlyUpdatedCanvas = $this->canvasService->getLastUpdatedCanvas(session('currentProject'), $boards);

        $recentProgressCanvas = [];

        foreach ($recentlyUpdatedCanvas as $canvas) {
            if (!isset($recentProgressCanvas[$canvas['type']])) {
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

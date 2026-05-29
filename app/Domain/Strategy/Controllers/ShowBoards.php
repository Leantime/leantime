<?php

namespace Leantime\Domain\Strategy\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Strategy\Services\Strategy as StrategyService;
use Symfony\Component\HttpFoundation\Response;

class ShowBoards extends Controller
{
    private StrategyService $strategyService;

    /**
     * Initializes dependencies.
     */
    public function init(StrategyService $strategyService): void
    {
        $this->strategyService = $strategyService;
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
        $overview = $this->strategyService->getStrategyBoardsOverview((int) session('currentProject'));

        $this->tpl->assign('recentProgressCanvas', $overview['recentProgressCanvas']);
        $this->tpl->assign('recentlyUpdatedCanvas', $overview['recentlyUpdatedCanvas']);
        $this->tpl->assign('canvasProgress', $overview['canvasProgress']);
        $this->tpl->assign('otherBoards', $overview['otherBoards']);

        return $this->tpl->display('strategy.showBoards');
    }
}

<?php

declare(strict_types=1);

namespace Leantime\Domain\Blueprints\Controllers;

use Leantime\Core\UI\Template;
use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;
use Symfony\Component\HttpFoundation\Response;

/**
 * ShowBoards controller - displays the blueprints boards overview.
 *
 * Absorbed from the former Strategy domain (there is no longer a separate
 * "strategy" module). Native Laravel controller: a single route-bound get()
 * action that reads the active project from the session and renders the
 * recent + available boards overview.
 */
class ShowBoards
{
    /**
     * @param  Template  $tpl  Template engine
     * @param  BlueprintsService  $blueprintsService  Blueprints service providing the boards overview
     */
    public function __construct(
        private Template $tpl,
        private BlueprintsService $blueprintsService,
    ) {}

    /**
     * get - display the blueprints boards overview for the active project.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function get(): Response
    {
        $overview = $this->blueprintsService->getBoardsOverview((int) session('currentProject'));

        $this->tpl->assign('recentProgressCanvas', $overview['recentProgressCanvas']);
        $this->tpl->assign('recentlyUpdatedCanvas', $overview['recentlyUpdatedCanvas']);
        $this->tpl->assign('canvasProgress', $overview['canvasProgress']);
        $this->tpl->assign('otherBoards', $overview['otherBoards']);

        return $this->tpl->display('blueprints.showBoards');
    }
}

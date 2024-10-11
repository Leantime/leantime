<?php

namespace Leantime\Domain\Goalcanvas\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;
use Leantime\Domain\Projects\Services\Projects;


class GoalcanvasCard extends HtmxController
{
  protected static string $view = 'goalcanvas::partials.goalCanvasCard';

  private Goalcanvas $goalService;
  private Projects $projectService;

  /**
   * Controller constructor
   *
   */
  public function init(Goalcanvas $goalService, Projects $projectService): void
  {
    $this->goalService = $goalService;
    $this->projectService = $projectService;
  }



  public function get($params): void
  {

    $id = (int) ($params['id']);
    $canvas = $this->goalService->getSingleCanvas($id);
    $statusLabels = $this->goalService->getGoalStatusLabels();
    $relatesLabels = $this->goalService->getGoalRelatesLabels();
    $users = $this->projectService->getUsersAssignedToProject(session('currentProject'));
    $this->tpl->assign('canvas', $canvas);
    $this->tpl->assign('statusLabels', $statusLabels);
    $this->tpl->assign('relatesLabels', $relatesLabels);
    $this->tpl->assign('users', $users);
  }
}

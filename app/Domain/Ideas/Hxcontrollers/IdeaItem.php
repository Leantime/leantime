<?php

namespace Leantime\Domain\Goalcanvas\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Ideas\Services\Ideas as IdeaService;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;


class IdeaItem extends HtmxController
{
  protected static string $view = 'ideas::components.idea-item';

  private IdeaService $ideaService;
  private Projects $projectService;
  private IdeaRepository $ideaRepo;


  /**
   * Controller constructor
   *
   */
  public function init(IdeaService $ideaService, IdeaRepository $ideaRepo, Projects $projectService): void
  {
    $this->ideaService = $ideaService;
    $this->projectService = $projectService;
    $this->ideaRepo = $ideaRepo;
  }



  public function get($params): void
  {

    $id = (int) ($params['id']);
    $key = (int) ($params['key']);

    $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject(session('currentProject')));
    $this->tpl->assign('canvasItems', $this->ideaRepo->getSingleCanvasItem($id));
    $this->tpl->assign('key', $key);
  }
}

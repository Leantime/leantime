<?php

namespace Leantime\Domain\Projects\Hxcontrollers;

use Leantime\Core\HtmxController;
use Leantime\Domain\Projects\Services\Projects;

class Checklist extends HtmxController
{
    /**
     * @var string
     */
    protected static $view = 'projects::partials.checklist';

    /**
     * @var \Leantime\Domain\Projects\Services\Projects
     */
    private Projects $projectService;

    /**
     * Controller constructor
     *
     * @param \Leantime\Domain\Projects\Services\Projects $projectService The projects domain service.
     * @return void
     */
    public function init(Projects $projectService)
    {
        $this->projectService = $projectService;
    }

    /**
     * Updates subtask status
     *
     * @return void
     */
    public function updateSubtask()
    {
        if (! $this->incomingRequest->getMethod() == 'PATCH') {
            throw new \Error('This endpoint only supports PATCH requests');
        }

        // update project progress
        $projectProgress = $this->incomingRequest->request->all();

        $this->projectService->updateProjectProgress($projectProgress, $_SESSION['currentProject']);

        // return view with new data
        [$progressSteps, $percentDone] = $this->projectService->getProjectSetupChecklist($_SESSION['currentProject']);
        $this->tpl->assign("progressSteps", $progressSteps);
        $this->tpl->assign("percentDone", $percentDone);
        $this->tpl->assign("includeTitle", false);
    }
}

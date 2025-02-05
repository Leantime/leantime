<?php

namespace Leantime\Domain\Goalcanvas\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas as GoalcanvasService;
use Leantime\Domain\Projects\Services\Projects;
use Symfony\Component\HttpFoundation\Response;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Users\Services\Users as UserService;
use Leantime\Domain\Auth\Services\Auth as AuthService;

class EditCanvasItem extends HtmxController
{
    protected static string $view = 'goalcanvas::components.canvas';

    private GoalcanvasService $goalService;

    private Projects $projectService;

    /**
     * Controller constructor
     */
    public function init(GoalcanvasService $goalService, Projects $projectService): void
    {
        $this->goalService = $goalService;
        $this->projectService = $projectService;
    }

    public function patch($params): Response
    {

        if (! AuthService::userIsAtLeast(Roles::$editor)) {
            return $this->tpl->displayJson(['error' => 'Not Authorized'], 403);
        }

        if (! isset($params['id'])) {
            return $this->tpl->displayJson(['error' => 'ID not set'], 400);
        }

        if (! $this->goalService->patch($params['id'], $params)) {
            return $this->tpl->displayJson(['error' => 'Could not update status'], 500);
        }

        //$this->tpl->setNotification($this->tpl->__('notifications.ticket_updated'), 'success');

        return $this->tpl->emptyResponse();
    }
}

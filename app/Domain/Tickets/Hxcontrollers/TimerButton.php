<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\HtmxController;
use Leantime\Domain\Timesheets\Services\Timesheets;

class TimerButton extends HtmxController
{
    /**
     * @var string
     */
    protected static $view = 'tickets::partials.timerLink';

    /**
     * @var \Leantime\Domain\Projects\Services\Timesheets
     */
    private Timesheets $timesheetService;

    /**
     * Controller constructor
     *
     * @param \Leantime\Domain\Projects\Services\Projects $projectService The projects domain service.
     * @return void
     */
    public function init(Timesheets $timesheetService)
    {
        $this->timesheetService = $timesheetService;
    }

    public function getStatus()
    {

        $params =  $this->incomingRequest->query->all();

        $onTheClock = isset($_SESSION['userdata']) ? $this->timesheetService->isClocked($_SESSION["userdata"]["id"]) : false;
        $this->tpl->assign("onTheClock", $onTheClock);
        $this->tpl->assign("parentTicketId", $params['request_parts'] ?? false);
    }
}

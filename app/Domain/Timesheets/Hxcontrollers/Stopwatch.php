<?php

namespace Leantime\Domain\Timesheets\Hxcontrollers;

use Leantime\Core\HtmxController;
use Leantime\Domain\Timesheets\Services\Timesheets;

class Stopwatch extends HtmxController
{
    /**
     * @var string
     */
    protected static $view = 'timesheets::partials.stopwatch';

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

    /**
     * show stop watch
     *
     * @return void
     */
    public function getStatus() {

        $onTheClock = isset($_SESSION['userdata']) ? $this->timesheetService->isClocked($_SESSION["userdata"]["id"]) : false;
        $this->tpl->assign("onTheClock", $onTheClock);

    }

    /**
     * show stop watch
     *
     * @return void
     */
    public function stopTimer()
    {
        if (! $this->incomingRequest->getMethod() == 'PATCH') {
            throw new \Error('This endpoint only supports PATCH requests');
        }



        $params =  $this->incomingRequest->request->all();

        if (isset($params["action"]) === true && $params["action"] == "stop") {
            $ticketId = filter_var($params["ticketId"], FILTER_SANITIZE_NUMBER_INT);
            $hoursBooked = $this->timesheetService->punchOut($ticketId);
        }

        header("HX-Trigger:timerUpdate");

        $onTheClock = isset($_SESSION['userdata']) ? $this->timesheetService->isClocked($_SESSION["userdata"]["id"]) : false;

        $this->tpl->assign("onTheClock", $onTheClock);

    }

    public function startTimer()
    {
        if (! $this->incomingRequest->getMethod() == 'PATCH') {
            throw new \Error('This endpoint only supports PATCH requests');
        }

        header("HX-Trigger:timerUpdate");

        $params =  $this->incomingRequest->request->all();

        if (isset($params["action"]) === true && $params["action"] == "start") {
            $ticketId = filter_var($params["ticketId"], FILTER_SANITIZE_NUMBER_INT);
            $this->timesheetService->punchIn($ticketId);
        }

        $onTheClock = isset($_SESSION['userdata']) ? $this->timesheetService->isClocked($_SESSION["userdata"]["id"]) : false;
        $this->tpl->assign("onTheClock", $onTheClock);

    }
}

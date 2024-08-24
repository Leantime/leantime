<?php

namespace Leantime\Domain\Timesheets\Hxcontrollers;

use Error;
use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 *
 */
class Stopwatch extends HtmxController
{
    /**
     * @var string
     */
    protected static string $view = 'timesheets::partials.stopwatch';

    /**
     * @var Timesheets
     */
    private Timesheets $timesheetService;

    /**
     * Controller constructor
     *
     * @param Timesheets $timesheetService
     *
     * @return void
     */
    public function init(Timesheets $timesheetService): void
    {
        $this->timesheetService = $timesheetService;
    }

    /**
     * show stop watch
     *
     * @return void
     */
    public function getStatus(): void
    {

        $onTheClock = session()->exists("userdata") ? $this->timesheetService->isClocked(session("userdata.id")) : false;
        $this->tpl->assign("onTheClock", $onTheClock);
    }

    /**
     * show stop watch
     *
     * @return void
     */
    public function stopTimer(): void
    {
        if (! $this->incomingRequest->getMethod() == 'PATCH') {
            throw new Error('This endpoint only supports PATCH requests');
        }

        $params =  $this->incomingRequest->request->all();

        if (isset($params["action"]) === true && $params["action"] == "stop") {
            $ticketId = filter_var($params["ticketId"], FILTER_SANITIZE_NUMBER_INT);
            $hoursBooked = $this->timesheetService->punchOut($ticketId);
        }

        $this->setHTMXEvent("timerUpdate");

        $onTheClock = session()->exists("userdata") ? $this->timesheetService->isClocked(session("userdata.id")) : false;

        $this->tpl->assign("onTheClock", $onTheClock);
    }

    /**
     * @return void
     */
    public function startTimer(): void
    {
        if (! $this->incomingRequest->getMethod() == 'PATCH') {
            throw new Error('This endpoint only supports PATCH requests');
        }

        $params =  $this->incomingRequest->request->all();

        if (isset($params["action"]) === true && $params["action"] == "start") {
            $ticketId = filter_var($params["ticketId"], FILTER_SANITIZE_NUMBER_INT);
            if ($ticketId > 0) {
                $this->timesheetService->punchIn($ticketId);
            }
        }

        $this->setHTMXEvent("timerUpdate");

        $onTheClock = session()->exists("userdata") ? $this->timesheetService->isClocked(session("userdata.id")) : false;
        $this->tpl->assign("onTheClock", $onTheClock);
    }
}

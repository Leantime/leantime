<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Timesheets\Services\Timesheets;

class TimerButton extends HtmxController
{
    protected static string $view = 'tickets::partials.timerLink';

    private Timesheets $timesheetService;

    /**
     * Controller constructor
     */
    public function init(Timesheets $timesheetService): void
    {
        $this->timesheetService = $timesheetService;
    }

    public function getStatus(): void
    {

        $params = $this->incomingRequest->query->all();

        $onTheClock = session()->exists('userdata') ? $this->timesheetService->isClocked(session('userdata.id')) : false;
        $this->tpl->assign('onTheClock', $onTheClock);
        $this->tpl->assign('parentTicketId', $params['request_parts'] ?? false);
    }
}

<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\HtmxController;
use Leantime\Domain\Timesheets\Services\Timesheets;

class TimerButton extends HtmxController
{
    /**
     * @var string
     */
    protected static $view = 'tickets::components.timerbutton';

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

}

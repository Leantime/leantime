<?php

namespace Leantime\Domain\Menu\Hxcontrollers;

use Leantime\Core\HtmxController;
use Leantime\Domain\Timesheets\Services\Timesheets;

class ProjectSelector extends HtmxController
{
    /**
     * @var string
     */
    protected static $view = 'tickets::partials.projectSelector';

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

    public function get() {


    }

    public function filter() {

    }

}

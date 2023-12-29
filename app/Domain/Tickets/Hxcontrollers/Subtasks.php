<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\HtmxController;
use Leantime\Core\IncomingRequest;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Timesheets\Services\Timesheets;

/**
 *
 */
class Subtasks extends HtmxController
{
    /**
     * @var string
     */
    protected static string $view = 'tickets::partials.subtasks';

    /**
     * @var Tickets
     */
    private Tickets $ticketService;

    /**
     * Controller constructor
     *
     * @param Timesheets $timesheetService
     * @return void
     */
    public function init(Tickets $ticketService): void
    {
        $this->ticketService = $ticketService;
    }

    /**
     * @return void
     */
    public function save(): void
    {

        $postParams = $_POST;
        $id = $postParams['id'];

        $ticket = $this->ticketService->getTicket($id);

    }

    /**
     * @return void
     */
    public function get($params): void
    {

        $id = (int)($params['id']);
        $ticket = $this->ticketService->getAllSubtasks($id);
        $statusLabels  = $this->ticketService->getStatusLabels($_SESSION['currentProject']);
        $efforts = $this->ticketService->getEffortLabels();

    }
}

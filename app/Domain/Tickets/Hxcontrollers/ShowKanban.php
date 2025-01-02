<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Timesheets\Services\Timesheets;
use Leantime\Domain\Projects\Services\Projects;
use Symfony\Component\HttpFoundation\Response;
use Leantime\Core\Support\FromFormat;


class ShowKanban extends HtmxController
{
    protected static string $view = 'tickets::components.ticket-column';

    private Tickets $ticketService;
    private Timesheets $timesheetService;
    /**
     * Controller constructor
     *
     */
    public function init(Tickets $ticketService): void
    {
        $this->ticketService = $ticketService;
    }


    public function post($params): Response
    {
        $result = $this->ticketService->quickAddTicket($params);
        return response()->json(['success' => $result]);
    }
}

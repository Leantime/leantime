<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Timesheets\Services\Timesheets;
use Leantime\Domain\Projects\Services\Projects;
use Symfony\Component\HttpFoundation\Response;
use Leantime\Core\Support\FromFormat;
use Leantime\Domain\Sprints\Services\Sprints as SprintService;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Users\Services\Users as UserService;

class NewTicket extends HtmxController
{
    protected static string $view = 'tickets::components.ticket-column';

    private Tickets $ticketService;
    private Projects $projectService;
    private Timesheets $timesheetService;
    private SprintService $sprintService;
    private UserService $userService;

    /**
     * Controller constructor
     *
     * @param  Timesheets  $timesheetService
     */
    public function init(Tickets $ticketService, Projects $projectService, Timesheets $timesheetService, SprintService $sprintService, UserService $userService): void
    {
        $this->ticketService = $ticketService;
        $this->projectService = $projectService;
        $this->timesheetService = $timesheetService;
        $this->sprintService = $sprintService;
        $this->userService = $userService;
    }

    public function post($params): Response
    {
        if (!empty($params['tags']) && is_array($params['tags'])) {
            $params['tags'] = implode(',', $params['tags']);
        }
        if (isset($params['saveTicket']) || isset($params['saveAndCloseTicket'])) {

            $params['timeToFinish'] = format(value: $params['timeToFinish'] ?? '', fromFormat: FromFormat::User24hTime)->userTime24toUserTime();
            $params['timeFrom'] = format(value: $params['timeFrom'] ?? '', fromFormat: FromFormat::User24hTime)->userTime24toUserTime();
            $params['timeTo'] = format(value: $params['timeTo'] ?? '', fromFormat: FromFormat::User24hTime)->userTime24toUserTime();

            $result = $this->ticketService->addTicket($params);

            if (is_array($result) === false) {
                return response()->json(['success' => true, 'id' => $result]);
            } else {

                $ticket = app()->makeWith(TicketModel::class, ['values' => $params]);
                $ticket->userLastname = session('userdata.name');
                return response()->json(['success' => false]);
            }
        }

        return response()->json([
            'success' => true,
            

        ]);
    }


    public function get(): Response
    {
        return response()->json(['success' => true]);
    }
}

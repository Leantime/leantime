<?php

namespace Leantime\Domain\Tickets\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

class ShowAllMilestonesOverview extends Controller
{
    private TicketService $ticketService;

    private UserService $userService;

    private ClientService $clientService;

    public function init(
        TicketService $ticketService,
        UserService $userService,
        ClientService $clientService
    ): void {
        $this->ticketService = $ticketService;
        $this->userService = $userService;
        $this->clientService = $clientService;

        session(['lastPage' => CURRENT_URL]);
    }

    /**
     * @throws \Exception
     */
    public function get($params): Response
    {
        $clientId = 0;
        $currentClientName = '';
        if (isset($_GET['client']) === true && $_GET['client'] != '') {
            $clientId = (int) $_GET['client'];
            $currentClientName = $this->ticketService->getClientNameById($clientId);
        }

        $searchCriteria = $this->ticketService->getMilestonesOverviewSearchCriteria($params);

        $this->tpl->assign('allTickets', $this->ticketService->getAllMilestonesOverview(false, 'duedate', false, $clientId, $searchCriteria));
        $this->tpl->assign('allTicketStates', $this->ticketService->getStatusLabels());
        $this->tpl->assign('efforts', $this->ticketService->getEffortLabels());
        $this->tpl->assign('priorities', $this->ticketService->getPriorityLabels());

        $this->tpl->assign('ticketTypeIcons', $this->ticketService->getTypeIcons());

        $this->tpl->assign('searchCriteria', $searchCriteria);
        $this->tpl->assign('numOfFilters', $this->ticketService->countSetFilters($searchCriteria));

        $allClients = $this->clientService->getUserClients(session('userdata.id'));

        $this->tpl->assign('clients', $allClients);
        $this->tpl->assign('currentClientName', $currentClientName);
        $this->tpl->assign('currentClient', $clientId);

        $this->tpl->assign('users', $this->userService->getAll());
        $this->tpl->assign('milestones', $this->ticketService->getAllMilestones([
            'sprint' => '',
            'type' => 'milestone',
            'currentProject' => session('currentProject'),
        ]));
        $this->tpl->assign('types', $this->ticketService->getTicketTypes());

        return $this->tpl->display('tickets.showAllMilestonesOverview');
    }
}

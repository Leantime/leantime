<?php

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Symfony\Component\HttpFoundation\Response;

class CloneTicket extends Controller
{
    private TicketService $ticketService;

    public function init(TicketService $ticketService): void
    {
        $this->ticketService = $ticketService;
    }

    public function post($params): Response
    {
        $originalTicket = $this->ticketService->getTicketById($params['id']);

        $cloneParams = [
            'summary' => $originalTicket->get('summary'),
            'description' => $originalTicket->get('description'),
            'projectId' => $originalTicket->get('projectId'),
            'status' => $originalTicket->get('status'),
            'assigneeId' => $originalTicket->get('assigneeId'),
        ];

        $newTicketId = $this->ticketService->addTicket($cloneParams);

        return Frontcontroller::redirect(BASE_URL.'/tickets/showTicket?id='.$newTicketId);
    }
}

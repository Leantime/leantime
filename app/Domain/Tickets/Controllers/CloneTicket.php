<?php

namespace Leantime\Domain\Tickets\Controllers;

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
        $originalTicket = $this->ticketService->getTicket($params['id']);

        if (!$originalTicket) {
            return Frontcontroller::redirect(BASE_URL.'/tickets/showKanban');
        }

        $cloneParams = [
            'headline' => $originalTicket->headline,
            'description' => $originalTicket->description,
            'projectId' => $originalTicket->projectId,
            'status' => $originalTicket->status,
            'editorId' => $originalTicket->editorId,
            'type' => $originalTicket->type,
            'priority' => $originalTicket->priority,
        ];

        $newTicketId = $this->ticketService->addTicket($cloneParams);
        error_log('Current language: ' . session('usersettings.language'));
        $this->tpl->setNotification('To-Do was cloned succesfully!', 'success', 'ticket_cloned');
        return Frontcontroller::redirect(BASE_URL.'/tickets/showKanban');
    }
}
<?php

namespace Leantime\Domain\Tickets\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Symfony\Component\HttpFoundation\Response;

class PinTicket extends Controller
{
    private TicketService $ticketService;

    public function init(TicketService $ticketService): void
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Toggle pin status for a ticket
     */
    public function post($params): Response
    {
        if (!isset($params['id'])) {
            $this->tpl->setNotification($this->language->__('notification.error_occurred'), 'error');
            return Frontcontroller::redirect(session('lastPage') ?? BASE_URL.'/tickets/showKanban');
        }

        $ticketId = (int) $params['id'];
        $projectId = session('currentProject');

        // Get the ticket to verify it exists and belongs to the project
        $ticket = $this->ticketService->getTicket($ticketId);

        if (!$ticket || $ticket->projectId != $projectId) {
            $this->tpl->setNotification($this->language->__('notification.error_occurred'), 'error');
            return Frontcontroller::redirect(session('lastPage') ?? BASE_URL.'/tickets/showKanban');
        }

        // Check if ticket is currently pinned
        $isPinned = $this->ticketService->isTicketPinned($ticketId, $projectId);

        if ($isPinned) {
            // Unpin the ticket and keep it at current position
            $result = $this->ticketService->unpinTicketAndKeepPosition($ticketId, $projectId, $ticket->status);
            if ($result) {
                $this->tpl->setNotification($this->language->__('notification.ticket_unpinned'), 'success');
            } else {
                $this->tpl->setNotification($this->language->__('notification.error_occurred'), 'error');
            }
        } else {
            // Pin the ticket
            $result = $this->ticketService->pinTicket($ticketId, $projectId);
            if ($result) {
                $this->tpl->setNotification($this->language->__('notification.ticket_pinned'), 'success');
            } else {
                $this->tpl->setNotification($this->language->__('notification.error_occurred'), 'error');
            }
        }

        // Redirect back to the previous page
        $redirect = session('lastPage') ?? BASE_URL.'/tickets/showKanban';

        return Frontcontroller::redirect($redirect);
    }
}


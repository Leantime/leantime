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
        $editFrom = new \DateTime($originalTicket->editFrom);
        $editFrom->modify('+1 hour');
        $editTo = new \DateTime($originalTicket->editTo);
        $editTo->modify('+1 hour');

        $dateToFinish = new \DateTime($originalTicket->dateToFinish);
        $dateToFinish->modify('+1 hour');

        $cloneParams = [
            'headline' => $originalTicket->headline,
            'description' => $originalTicket->description,
            'projectId' => $originalTicket->projectId,
            'status' => $originalTicket->status,
            'editorId' => $originalTicket->editorId,
            'type' => $originalTicket->type,
            'priority' => $originalTicket->priority,
            'storypoints' => $originalTicket->storypoints,
            'dateToFinish' => $dateToFinish->format('Y-m-d H:i:s'),
            'tags' => $originalTicket->tags,
            'milestoneid' => $originalTicket->milestoneid,
            'dependingTicketId' => $originalTicket->dependingTicketId,
            'editFrom' => $editFrom->format('Y-m-d H:i:s'),
            'editTo' => $editTo->format('Y-m-d H:i:s'),
            'planHours' => $originalTicket->planHours,
            'hourRemaining' => $originalTicket->hourRemaining,
        ];

        $newTicketId = $this->ticketService->addTicket($cloneParams);
        error_log('Current language: ' . session('usersettings.language'));
        $this->tpl->setNotification('To-Do was cloned succesfully!', 'success', 'ticket_cloned');
        return Frontcontroller::redirect(BASE_URL.'/tickets/showKanban');
    }
}
<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ticket HxController — handles HTMX partial updates for individual ticket fields.
 *
 * Supports live-patching a single field on a ticket without a full page reload.
 * Used by the chips components (status, priority, milestone, etc.) in ticket views.
 */
class Ticket extends HtmxController
{
    protected static string $view = '';

    private TicketService $ticketService;

    /**
     * Initialise dependencies.
     */
    public function init(TicketService $ticketService): void
    {
        $this->ticketService = $ticketService;
    }

    /**
     * PATCH /hx/tickets/ticket/patch/{id}
     *
     * Accepts a POST body with one or more ticket field values and patches only
     * those fields on the ticket. Returns an empty 200 on success so the chip
     * does not need to swap any DOM content.
     *
     * @param  array<string, mixed>  $params  Route + request params (must include 'id').
     *
     * @api
     */
    public function patch(array $params): Response
    {
        if (! AuthService::userIsAtLeast(Roles::$editor)) {
            return $this->tpl->displayJson(['error' => 'Not Authorized'], 403);
        }

        if (empty($params['id'])) {
            return $this->tpl->displayJson(['error' => 'ID not set'], 400);
        }

        if (! $this->ticketService->patch($params['id'], $params)) {
            return $this->tpl->displayJson(['error' => 'Could not update ticket'], 500);
        }

        return $this->tpl->emptyResponse();
    }
}

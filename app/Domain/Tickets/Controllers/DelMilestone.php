<?php

namespace Leantime\Domain\Tickets\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Tickets\Permissions\TicketsPermissions;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Symfony\Component\HttpFoundation\Response;

class DelMilestone extends Controller
{
    private TicketService $ticketService;

    public function init(TicketService $ticketService): void
    {
        $this->ticketService = $ticketService;
    }

    /**
     * @throws BindingResolutionException
     */
    #[RequiresPermission(TicketsPermissions::DELETE)]
    public function get(): Response
    {
        if (! isset($_GET['id'])) {
            return $this->tpl->displayPartial('errors.error404', responseCode: 404);
        }

        $id = (int) $_GET['id'];

        $this->tpl->assign('ticket', $this->ticketService->getTicket($id));

        return $this->tpl->displayPartial('tickets.delMilestone');
    }

    /**
     * @throws BindingResolutionException
     */
    #[RequiresPermission(TicketsPermissions::DELETE)]
    public function post($params): Response
    {
        if (! isset($_GET['id'], $params['del'])) {
            return $this->tpl->displayPartial('errors.error404', responseCode: 404);
        }

        if ($result = $this->ticketService->deleteMilestone($id = (int) ($_GET['id']))) {
            $this->tpl->setNotification($this->language->__('notification.milestone_deleted'), 'success');

            return Frontcontroller::redirect(BASE_URL.'/tickets/roadmap');
        }

        $this->tpl->setNotification($this->language->__($result['msg']), 'error');
        $this->tpl->assign('ticket', $this->ticketService->getTicket($id));

        return $this->tpl->displayPartial('tickets.delMilestone');
    }
}

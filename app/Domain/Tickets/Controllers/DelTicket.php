<?php

namespace Leantime\Domain\Tickets\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Tickets\Permissions\TicketsPermissions;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Symfony\Component\HttpFoundation\Response;

class DelTicket extends Controller
{
    private TicketService $ticketService;

    public function init(TicketService $ticketService): void
    {
        $this->ticketService = $ticketService;
    }

    /**
     * @throws \Exception
     */
    #[RequiresPermission(TicketsPermissions::DELETE)]
    public function get(): Response
    {
        if (! isset($_GET['id'])) {
            return $this->tpl->display('errors.error404', responseCode: 404);
        }

        $id = (int) $_GET['id'];

        try {
            $this->ticketService->canDelete($id);
        } catch (\Exception $e) {
            $this->tpl->assign('error', $e->getMessage());

            return $this->tpl->displayPartial('tickets.delTicket');
        }

        $this->tpl->assign('error', '');
        $this->tpl->assign('ticket', $this->ticketService->getTicket($id));

        return $this->tpl->displayPartial('tickets.delTicket');
    }

    /**
     * @throws \Exception
     */
    #[RequiresPermission(TicketsPermissions::DELETE)]
    public function post($params): Response
    {
        if (! isset($params['del'])) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        if (! isset($_GET['id'])) {
            return $this->tpl->display('errors.error404', responseCode: 404);
        }

        $id = (int) $_GET['id'];

        $result = $this->ticketService->delete($id);

        if ($result === true) {
            $this->tpl->setNotification($this->language->__('notification.todo_deleted'), 'success');
            $redirect = session('lastPage') ?? BASE_URL.'/';

            return Frontcontroller::redirect($redirect);
        }

        $this->tpl->setNotification($this->language->__($result['msg']), 'error');
        $this->tpl->assign('ticket', $this->ticketService->getTicket($id));

        return $this->tpl->displayPartial('tickets.delTicket');
    }
}

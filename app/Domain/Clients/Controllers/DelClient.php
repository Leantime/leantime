<?php

namespace Leantime\Domain\Clients\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Clients\Permissions\ClientsPermissions;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Symfony\Component\HttpFoundation\Response;

/**
 * DelClient Controller - Deleting clients.
 */
class DelClient extends Controller
{
    private ClientService $clientService;

    /**
     * Initializes dependencies.
     */
    public function init(ClientService $clientService): void
    {
        $this->clientService = $clientService;
    }

    /**
     * Displays the delete client confirmation page.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(ClientsPermissions::DELETE, global: true)]
    public function get(array $params): Response
    {
        if (! isset($params['id'])) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        $id = (int) $params['id'];

        if ($this->clientService->hasTickets($id)) {
            $this->tpl->setNotification($this->language->__('notification.client_has_todos'), 'error');
        }

        $this->tpl->assign('client', $this->clientService->get($id));

        return $this->tpl->display('clients.delClient');
    }

    /**
     * Handles client deletion.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(ClientsPermissions::DELETE, global: true)]
    public function post(array $params): Response
    {
        if (! isset($params['id'])) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        $id = (int) $params['id'];

        if ($this->clientService->hasTickets($id)) {
            $this->tpl->setNotification($this->language->__('notification.client_has_todos'), 'error');
            $this->tpl->assign('client', $this->clientService->get($id));

            return $this->tpl->display('clients.delClient');
        }

        $this->clientService->delete($id);
        $this->tpl->setNotification($this->language->__('notification.client_deleted'), 'success');

        return Frontcontroller::redirect(BASE_URL.'/clients/showAll');
    }
}

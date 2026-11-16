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
     * Displays the delete client confirmation page, listing the projects that are deleted with it.
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

        $this->assignConfirmationData($id);

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
            $this->assignConfirmationData($id);

            return $this->tpl->display('clients.delClient');
        }

        $clientDeleted = $this->clientService->delete($id);
        if (! $clientDeleted) {
            $this->tpl->setNotification($this->language->__('notification.client_not_deleted'), 'error');
            $this->assignConfirmationData($id);

            return $this->tpl->display('clients.delClient');
        }

        $this->tpl->setNotification($this->language->__('notification.client_deleted'), 'success');

        return Frontcontroller::redirect(BASE_URL.'/clients/showAll');
    }

    /**
     * Assigns the client and the projects that are deleted along with it.
     *
     * @param  int  $id  Client id
     */
    private function assignConfirmationData(int $id): void
    {
        $this->tpl->assign('client', $this->clientService->get($id));
        $this->tpl->assign('clientProjects', $this->clientService->getProjectsDeletedWithClient($id));
    }
}

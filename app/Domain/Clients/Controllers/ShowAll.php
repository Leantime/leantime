<?php

namespace Leantime\Domain\Clients\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Clients\Permissions\ClientsPermissions;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Symfony\Component\HttpFoundation\Response;

/**
 * ShowAll Controller - Show all clients.
 */
class ShowAll extends Controller
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
     * Displays the list of all clients.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(ClientsPermissions::VIEW, global: true)]
    public function get(array $params): Response
    {
        if (session('userdata.role') == 'admin') {
            $this->tpl->assign('admin', true);
        }

        $this->tpl->assign('allClients', $this->clientService->getAll());

        return $this->tpl->display('clients.showAll');
    }
}

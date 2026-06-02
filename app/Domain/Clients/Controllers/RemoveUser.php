<?php

namespace Leantime\Domain\Clients\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Clients\Permissions\ClientsPermissions;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Symfony\Component\HttpFoundation\Response;

/**
 * RemoveUser Controller - Remove user from client.
 */
class RemoveUser extends Controller
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
     * Displays the remove user confirmation (no state change on GET).
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(ClientsPermissions::EDIT, global: true)]
    public function get(array $params): Response
    {
        $clientId = (int) ($params['id'] ?? $_GET['id'] ?? 0);

        return Frontcontroller::redirect(BASE_URL.'/clients/showClient/'.$clientId);
    }

    /**
     * Handles user removal from client via POST (CSRF-protected).
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(ClientsPermissions::EDIT, global: true)]
    public function post(array $params): Response
    {
        $clientId = (int) ($params['id'] ?? $_POST['id'] ?? 0);
        $userId = (int) ($params['userId'] ?? $_POST['userId'] ?? 0);

        if ($clientId === 0 || $userId === 0) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        if ($this->clientService->removeUser($clientId, $userId)) {
            $this->tpl->setNotification(
                $this->language->__('notification.user_removed_from_client'),
                'success'
            );
        } else {
            $this->tpl->setNotification(
                $this->language->__('notification.error_removing_user'),
                'error'
            );
        }

        return Frontcontroller::redirect(BASE_URL.'/clients/showClient/'.$clientId);
    }
}

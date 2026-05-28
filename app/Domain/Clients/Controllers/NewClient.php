<?php

namespace Leantime\Domain\Clients\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Symfony\Component\HttpFoundation\Response;

/**
 * NewClient Controller - Add a new client.
 */
class NewClient extends Controller
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
     * Displays the new client form.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        if (! Auth::userIsAtLeast(Roles::$admin)) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        $values = [
            'name' => '',
            'street' => '',
            'zip' => '',
            'city' => '',
            'state' => '',
            'country' => '',
            'phone' => '',
            'internet' => '',
            'email' => '',
        ];

        $this->tpl->assign('values', $values);

        return $this->tpl->display('clients.newClient');
    }

    /**
     * Handles new client form submission.
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        if (! Auth::userIsAtLeast(Roles::$admin)) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        $values = [
            'name' => $_POST['name'] ?? '',
            'street' => $_POST['street'] ?? '',
            'zip' => $_POST['zip'] ?? '',
            'city' => $_POST['city'] ?? '',
            'state' => $_POST['state'] ?? '',
            'country' => $_POST['country'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'internet' => $_POST['internet'] ?? '',
            'email' => $_POST['email'] ?? '',
        ];

        if ($values['name'] !== '') {
            if ($this->clientService->isClient($values) !== true) {
                $id = $this->clientService->create($values);
                $this->tpl->setNotification($this->language->__('notification.client_added_successfully'), 'success', 'new_client');

                return Frontcontroller::redirect(BASE_URL.'/clients/showClient/'.$id);
            } else {
                $this->tpl->setNotification($this->language->__('notification.client_exists_already'), 'error');
            }
        } else {
            $this->tpl->setNotification($this->language->__('notification.client_name_not_specified'), 'error');
        }

        $this->tpl->assign('values', $values);

        return $this->tpl->display('clients.newClient');
    }
}

<?php

namespace Leantime\Domain\Clients\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Symfony\Component\HttpFoundation\Response;

/**
 * EditClient Controller - Editing clients.
 */
class EditClient extends Controller
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
     * Displays the edit client form.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        if (! Auth::userIsAtLeast(Roles::$admin)) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        if (! isset($params['id'])) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        $id = (int) $params['id'];
        $row = $this->clientService->get($id);

        if ($row === false) {
            return $this->tpl->display('errors.error404', responseCode: 404);
        }

        $this->tpl->assign('values', $row);

        return $this->tpl->display('clients.editClient');
    }

    /**
     * Handles client edit form submission.
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        if (! Auth::userIsAtLeast(Roles::$admin)) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        if (! isset($params['id'])) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        $id = (int) $params['id'];

        $values = [
            'id' => $id,
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
            $this->clientService->editClient($values);
            $this->tpl->setNotification('EDIT_CLIENT_SUCCESS', 'success', 'client_updated');
        } else {
            $this->tpl->setNotification('NO_NAME', 'error');
        }

        $this->tpl->assign('values', $values);

        return $this->tpl->display('clients.editClient');
    }
}

<?php

namespace Leantime\Domain\Clients\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * RemoveUser Controller - Remove user from client.
 */
class RemoveUser extends Controller
{
    private UserRepository $userRepo;

    /**
     * Initializes dependencies.
     */
    public function init(UserRepository $userRepo): void
    {
        $this->userRepo = $userRepo;
    }

    /**
     * Handles user removal from client via GET (action link).
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        if (! Auth::userIsAtLeast(Roles::$admin)) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        if (! isset($_GET['id']) || ! isset($_GET['userId'])) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        $clientId = (int) $_GET['id'];
        $userId = (int) $_GET['userId'];

        if ($this->userRepo->removeFromClient($userId)) {
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

    /**
     * Handles user removal from client via POST.
     *
     * @param  array  $params  Request parameters
     */
    public function post(array $params): Response
    {
        return $this->get($params);
    }
}

<?php

/**
 * RemoveUser Class - Remove user from client
 */

namespace Leantime\Domain\Clients\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Users\Repositories\Users as UserRepository;

class RemoveUser extends Controller
{
    private UserRepository $userRepo;

    /**
     * init - initialize private variables
     */
    public function init()
    {
        $this->userRepo = app()->make(UserRepository::class);
    }

    /**
     * run - remove user from client
     */
    public function run()
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        // Only admins can remove users from clients
        if (Auth::userIsAtLeast(Roles::$admin)) {
            if (isset($_GET['id']) === true && isset($_GET['userId']) === true) {
                $clientId = (int) ($_GET['id']);
                $userId = (int) ($_GET['userId']);

                // Remove user from client by setting clientId to null
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
            } else {
                return $this->tpl->display('errors.error403', responseCode: 403);
            }
        } else {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }
    }
}

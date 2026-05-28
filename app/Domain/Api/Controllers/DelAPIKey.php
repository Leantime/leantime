<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Api\Services\Api;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles API key deletion.
 */
class DelAPIKey extends Controller
{
    private Api $APIService;

    private UserRepository $userRepo;

    /**
     * Initializes dependencies.
     */
    public function init(Api $APIService, UserRepository $userRepo): void
    {
        $this->APIService = $APIService;
        $this->userRepo = $userRepo;
    }

    /**
     * Displays the delete API key confirmation.
     *
     * @param  array  $params  Request parameters
     *
     * @throws \Exception
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            return $this->tpl->display('errors.error403');
        }

        $user = $this->userRepo->getUser($id);

        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        session(['formTokenName' => substr(str_shuffle($permitted_chars), 0, 32)]);
        session(['formTokenValue' => substr(str_shuffle($permitted_chars), 0, 32)]);

        $this->tpl->assign('user', $user);

        return $this->tpl->display('api.delKey');
    }

    /**
     * Handles API key deletion.
     *
     * @param  array  $params  Request parameters
     *
     * @throws \Exception
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            return $this->tpl->display('errors.error403');
        }

        $user = $this->userRepo->getUser($id);

        if (isset($_POST['del'])) {
            if (isset($_POST[session('formTokenName')]) && $_POST[session('formTokenName')] == session('formTokenValue')) {
                $this->userRepo->deleteUser($id);
                $this->tpl->setNotification($this->language->__('notifications.key_deleted'), 'success', 'apikey_deleted');

                return Frontcontroller::redirect(BASE_URL.'/setting/editCompanySettings/#apiKeys');
            } else {
                $this->tpl->setNotification($this->language->__('notification.form_token_incorrect'), 'error');
            }
        }

        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        session(['formTokenName' => substr(str_shuffle($permitted_chars), 0, 32)]);
        session(['formTokenValue' => substr(str_shuffle($permitted_chars), 0, 32)]);

        $this->tpl->assign('user', $user);

        return $this->tpl->display('api.delKey');
    }
}

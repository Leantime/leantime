<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles API key deletion.
 */
class DelAPIKey extends Controller
{
    private UserService $userService;

    /**
     * Initializes dependencies.
     */
    public function init(UserService $userService): void
    {
        $this->userService = $userService;
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

        $this->tpl->assign('user', $this->userService->getUser($id));
        $this->generateFormTokens();

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

        if (isset($_POST['del'])) {
            if (isset($_POST[session('formTokenName')]) && $_POST[session('formTokenName')] == session('formTokenValue')) {
                $this->userService->deleteUser($id);
                $this->tpl->setNotification($this->language->__('notifications.key_deleted'), 'success', 'apikey_deleted');

                return Frontcontroller::redirect(BASE_URL.'/setting/editCompanySettings/#apiKeys');
            }

            $this->tpl->setNotification($this->language->__('notification.form_token_incorrect'), 'error');
        }

        $this->tpl->assign('user', $this->userService->getUser($id));
        $this->generateFormTokens();

        return $this->tpl->display('api.delKey');
    }

    /**
     * Generates CSRF form tokens for the delete confirmation form.
     */
    private function generateFormTokens(): void
    {
        $permittedChars = '0123456789abcdefghijklmnopqrstuvwxyz';
        session(['formTokenName' => substr(str_shuffle($permittedChars), 0, 32)]);
        session(['formTokenValue' => substr(str_shuffle($permittedChars), 0, 32)]);
    }
}

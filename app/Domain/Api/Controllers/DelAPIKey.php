<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Domain\Api\Services\Api as ApiService;
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

    private ApiService $apiService;

    /**
     * Initializes dependencies.
     */
    public function init(UserService $userService, ApiService $apiService): void
    {
        $this->userService = $userService;
        $this->apiService = $apiService;
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
                try {
                    $this->apiService->deleteApiKey($id);
                    $this->tpl->setNotification($this->language->__('notifications.key_deleted'), 'success', 'apikey_deleted');

                    return Frontcontroller::redirect(BASE_URL.'/setting/editCompanySettings/#apiKeys');
                } catch (AuthorizationException $e) {
                    $this->tpl->setNotification($this->language->__('notification.apikey_update_not_allowed'), 'error');
                }
            } else {
                $this->tpl->setNotification($this->language->__('notification.form_token_incorrect'), 'error');
            }
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
        session(['formTokenName' => bin2hex(random_bytes(16))]);
        session(['formTokenValue' => bin2hex(random_bytes(16))]);
    }
}

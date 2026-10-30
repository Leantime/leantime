<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Domain\Api\Permissions\ApiPermissions;
use Leantime\Domain\Api\Services\Api as ApiService;
use Leantime\Domain\Setting\Permissions\SettingPermissions;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles API key deletion.
 */
class DelAPIKey extends Controller
{
    private UserService $userService;

    private ApiService $apiService;

    private PermissionService $permissionService;

    /**
     * Initializes dependencies.
     */
    public function init(UserService $userService, ApiService $apiService, PermissionService $permissionService): void
    {
        $this->userService = $userService;
        $this->apiService = $apiService;
        $this->permissionService = $permissionService;
    }

    /**
     * Displays the delete API key confirmation.
     *
     * @param  array  $params  Request parameters
     *
     * @throws \Exception
     */
    #[RequiresPermission(ApiPermissions::MANAGE, global: true)]
    public function get(array $params): Response
    {
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
    #[RequiresPermission(ApiPermissions::MANAGE, global: true)]
    public function post(array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        if ($id <= 0) {
            return $this->tpl->display('errors.error403');
        }

        if (isset($_POST['del'])) {
            if (isset($_POST[session('formTokenName')]) && $_POST[session('formTokenName')] == session('formTokenValue')) {
                try {
                    $this->apiService->deleteApiKey($id);
                    $this->tpl->setNotification($this->language->__('notifications.key_deleted'), 'success', 'apikey_deleted');

                    // The key list lives in company settings, which is a separate permission.
                    if ($this->permissionService->currentUserCan(SettingPermissions::COMPANY_VIEW)) {
                        return Frontcontroller::redirect(BASE_URL.'/setting/editCompanySettings/#apiKeys');
                    }

                    return Frontcontroller::redirect(BASE_URL.'/dashboard/home');
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

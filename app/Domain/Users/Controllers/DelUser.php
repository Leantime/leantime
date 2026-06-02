<?php

namespace Leantime\Domain\Users\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Users\Permissions\UsersPermissions;
use Leantime\Domain\Users\Services\Users;
use Symfony\Component\HttpFoundation\Response;

class DelUser extends Controller
{
    private Users $userService;

    /**
     * Initializes dependencies.
     */
    public function init(Users $userService): void
    {
        $this->userService = $userService;
    }

    /**
     * Displays the delete user confirmation page.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(UsersPermissions::DELETE, global: true)]
    public function get(array $params): Response
    {
        if (! isset($params['id'])) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        $id = (int) $params['id'];
        $user = $this->userService->getUser($id);

        $this->generateFormTokens();

        $this->tpl->assign('user', $user);

        return $this->tpl->display('users.delUser');
    }

    /**
     * Handles user deletion.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(UsersPermissions::DELETE, global: true)]
    public function post(array $params): Response
    {
        if (! isset($params['id'])) {
            return $this->tpl->display('errors.error403', responseCode: 403);
        }

        $id = (int) $params['id'];
        $user = $this->userService->getUser($id);

        if (isset($_POST['del'])) {
            if (isset($_POST[session('formTokenName')]) && $_POST[session('formTokenName')] == session('formTokenValue')) {
                $this->userService->deleteUser($id);
                $this->tpl->setNotification($this->language->__('notifications.user_deleted'), 'success', 'user_deleted');

                return Frontcontroller::redirect(BASE_URL.'/users/showAll');
            }

            $this->tpl->setNotification($this->language->__('notification.form_token_incorrect'), 'error');
        }

        $this->generateFormTokens();

        $this->tpl->assign('user', $user);

        return $this->tpl->display('users.delUser');
    }

    /**
     * Generates CSRF form tokens for the sensitive delete form.
     */
    private function generateFormTokens(): void
    {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        session(['formTokenName' => substr(str_shuffle($permitted_chars), 0, 32)]);
        session(['formTokenValue' => substr(str_shuffle($permitted_chars), 0, 32)]);
    }
}

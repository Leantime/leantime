<?php

namespace Leantime\Domain\Users\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Exceptions\AuthorizationException;
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

        $user = $this->findUser($params['id']);

        if ($user === false) {
            return $this->tpl->display('errors.error404', responseCode: 404);
        }

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

        $user = $this->findUser($params['id']);

        if ($user === false) {
            return $this->tpl->display('errors.error404', responseCode: 404);
        }

        $id = (int) $user['id'];

        if (isset($_POST['del'])) {
            if (isset($_POST[session('formTokenName')]) && $_POST[session('formTokenName')] == session('formTokenValue')) {
                try {
                    $this->userService->deleteUser($id);
                    $this->tpl->setNotification($this->language->__('notifications.user_deleted'), 'success', 'user_deleted');

                    return Frontcontroller::redirect(BASE_URL.'/users/showAll');
                } catch (AuthorizationException $e) {
                    $this->tpl->setNotification($this->language->__('notification.role_not_allowed'), 'error');
                }
            } else {
                $this->tpl->setNotification($this->language->__('notification.form_token_incorrect'), 'error');
            }
        }

        $this->generateFormTokens();

        $this->tpl->assign('user', $user);

        return $this->tpl->display('users.delUser');
    }

    /**
     * Loads the user the confirmation is about, so the page can name them.
     *
     * The service resolves id 0 to the logged-in user, so a non-positive id is
     * treated as "not found" rather than silently showing someone else.
     *
     * @param  mixed  $rawId  The id from the route
     * @return array<string, mixed>|false The user row, or false when there is no such user
     */
    private function findUser(mixed $rawId): array|false
    {
        $id = (int) $rawId;
        if ($id <= 0) {
            return false;
        }

        $user = $this->userService->getUser($id);

        return is_array($user) ? $user : false;
    }

    /**
     * Generates CSRF form tokens for the sensitive delete form.
     */
    private function generateFormTokens(): void
    {
        session(['formTokenName' => bin2hex(random_bytes(16))]);
        session(['formTokenValue' => bin2hex(random_bytes(16))]);
    }
}

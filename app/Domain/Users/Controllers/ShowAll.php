<?php

namespace Leantime\Domain\Users\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Users\Permissions\UsersPermissions;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

class ShowAll extends Controller
{
    private UserService $userService;

    public function init(UserService $userService): void
    {
        $this->userService = $userService;
    }

    /**
     * @throws \Exception
     */
    #[RequiresPermission(UsersPermissions::VIEW, global: true)]
    public function get(): Response
    {
        $this->tpl->assign('allUsers', $this->userService->getAllVisibleToUser());
        $this->tpl->assign('admin', true);
        $this->tpl->assign('roles', Roles::getRoles());

        return $this->tpl->display('users.showAll');
    }

    public function post($params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not Implemented']);
    }
}

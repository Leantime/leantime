<?php

namespace Leantime\Domain\Users\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
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
    public function get(): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        // Only Admins
        if (! Auth::userIsAtLeast(Roles::$admin)) {
            return $this->tpl->display('errors.error403');
        }

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

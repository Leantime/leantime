<?php

/* Not production ready yet. Prepping for future version */

namespace Leantime\Domain\Users\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Ldap\Services\Ldap as LdapService;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

class Import extends Controller
{
    private UserService $userService;

    private LdapService $ldapService;

    public function init(UserService $userService, LdapService $ldapService): void
    {
        $this->userService = $userService;
        $this->ldapService = $ldapService;

        if (! session()->exists('tmp')) {
            session(['tmp' => []]);
        }
    }

    /**
     * @throws \Exception
     */
    public function get(): Response
    {
        // Only Admins
        if (! Auth::userIsAtLeast(Roles::$admin)) {
            return $this->tpl->display('errors.error403');
        }

        $this->tpl->assign('allUsers', $this->userService->getAll());
        $this->tpl->assign('admin', true);
        $this->tpl->assign('roles', Roles::getRoles());

        if (session()->exist('tmp.ldapUsers') && count(session('tmp.ldapUsers')) > 0) {
            $this->tpl->assign('allLdapUsers', session('tmp.ldapUsers'));
            $this->tpl->assign('confirmUsers', true);
        }

        return $this->tpl->displayPartial('users.importLdapDialog');
    }

    /**
     * @throws BindingResolutionException
     */
    public function post($params): Response
    {
        // Password Submit to connect to ldap and retrieve users. Sets tmp session var
        if (isset($params['pwSubmit'])) {
            $bindUsername = $this->ldapService->extractLdapFromUsername(session('userdata.mail'));
            $members = $this->userService->fetchLdapMembers($bindUsername, $params['password']);

            if ($members !== false) {
                session(['tmp.ldapUsers' => $members]);
                $this->tpl->assign('allLdapUsers', session('tmp.ldapUsers'));
                $this->tpl->assign('confirmUsers', true);
            } else {
                $this->tpl->setNotification($this->language->__('notifications.username_or_password_incorrect'), 'error');
            }
        }

        // Import/Update User Post
        if (isset($params['importSubmit'])) {
            if (is_array($params['users'])) {
                $this->userService->importSelectedLdapUsers(session('tmp.ldapUsers'), $params['users']);
            }
        }

        return $this->tpl->displayPartial('users.importLdapDialog');
    }
}

<?php

namespace Leantime\Domain\Users\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Leantime\Domain\Ldap\Services\Ldap as LdapService;
use Leantime\Domain\Auth\Services\Auth;

    class ShowAll extends Controller
    {
        private UserRepository $userRepo;
        private LdapService $ldapService;

        public function init(UserRepository $userRepo, LdapService $ldapService)
        {
            $this->userRepo = $userRepo;
            $this->ldapService = $ldapService;
        }

        public function get()
        {

            Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);



            //Only Admins
            if (Auth::userIsAtLeast(Roles::$admin)) {
                if (Auth::userIsAtLeast(Roles::$admin)) {
                    $this->tpl->assign('allUsers', $this->userRepo->getAll());
                } else {
                    $this->tpl->assign('allUsers', $this->userRepo->getAllClientUsers(Auth::getUserClientId()));
                }

                $this->tpl->assign('admin', true);
                $this->tpl->assign('roles', Roles::getRoles());

                $this->tpl->display('users.showAll');
            } else {
                $this->tpl->display('errors.error403');
            }
        }

        public function post($params)
        {
        }
    }

}

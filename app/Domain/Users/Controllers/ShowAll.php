<?php

namespace Leantime\Domain\Users\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;
    use Leantime\Domain\Ldap\Services\Ldap as LdapService;
    use Leantime\Domain\Auth\Services\Auth;

    /**
     *
     */
    class ShowAll extends Controller
    {
        private UserRepository $userRepo;
        private LdapService $ldapService;

        /**
         * @param UserRepository $userRepo
         * @param LdapService    $ldapService
         * @return void
         */
        public function init(UserRepository $userRepo, LdapService $ldapService): void
        {
            $this->userRepo = $userRepo;
            $this->ldapService = $ldapService;
        }

        /**
         * @return void
         * @throws \Exception
         */
        public function get(): void
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

        /**
         * @param $params
         * @return void
         */
        public function post($params): void
        {
        }
    }

}

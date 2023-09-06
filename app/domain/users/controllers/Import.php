<?php

/* Not production ready yet. Prepping for future version */

namespace Leantime\Domain\Users\Controllers {

    use Leantime\Core\Template as TemplateCore;
use Leantime\Core\Controller;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Leantime\Domain\Ldap\Services\Ldap as LdapService;
use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Auth\Models\Roles;

    class Import extends Controller
    {
        private UserRepository $userRepo;
        private LdapService $ldapService;

        public function init(UserRepository $userRepo, LdapService $ldapService)
        {
            $this->userRepo = $userRepo;
            $this->ldapService = $ldapService;

            if (!isset($_SESSION['tmp'])) {
                $_SESSION['tmp'] = [];
            }
        }

        public function get()
        {

            //Only Admins
            if (Auth::userIsAtLeast(Roles::$admin)) {
                $this->tpl->assign('allUsers', $this->userRepo->getAll());
                $this->tpl->assign('admin', true);
                $this->tpl->assign('roles', Roles::getRoles());

                if (isset($_SESSION['tmp']["ldapUsers"]) && count($_SESSION['tmp']["ldapUsers"]) > 0) {
                    $this->tpl->assign('allLdapUsers', $_SESSION['tmp']["ldapUsers"]);
                    $this->tpl->assign('confirmUsers', true);
                }

                $this->tpl->displayPartial('users.importLdapDialog');
            } else {
                $this->tpl->display('errors.error403');
            }
        }

        public function post($params)
        {
            $this->tpl = app()->make(TemplateCore::class);
            $this->ldapService = app()->make(LdapService::class);

            //Password Submit to connect to ldap and retrieve users. Sets tmp session var
            if (isset($params['pwSubmit'])) {
                $username = $this->ldapService->extractLdapFromUsername($_SESSION["userdata"]["mail"]);

                $this->ldapService->connect();

                if ($this->ldapService->bind($username, $params['password'])) {
                    $_SESSION['tmp']["ldapUsers"] = $this->ldapService->getAllMembers();
                    $this->tpl->assign('allLdapUsers', $_SESSION['tmp']["ldapUsers"]);
                    $this->tpl->assign('confirmUsers', true);
                } else {
                    $this->tpl->setNotification($this->language->__("notifications.username_or_password_incorrect"), "error");
                }
            }

            //Import/Update User Post
            if (isset($params['importSubmit'])) {
                if (is_array($params["users"])) {
                    $users = array();
                    foreach ($_SESSION['tmp']["ldapUsers"] as $user) {
                        if (array_search($user['username'], $params["users"])) {
                            $users[] = $user;
                        }
                    }

                    $this->ldapService->upsertUsers($users);
                }
            }

            $this->tpl->displayPartial('users.importLdapDialog');
        }
    }
}

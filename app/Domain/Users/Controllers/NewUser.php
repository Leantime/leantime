<?php

namespace Leantime\Domain\Users\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;
    use Leantime\Domain\Users\Services\Users as UserService;

    /**
     *
     */
    class NewUser extends Controller
    {
        private UserRepository $userRepo;
        private ProjectRepository $projectsRepo;
        private UserService $userService;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(
            UserRepository $userRepo,
            ProjectRepository $projectsRepo,
            UserService $userService
        ) {
            $this->userRepo = $userRepo;
            $this->projectsRepo = $projectsRepo;
            $this->userService = $userService;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

            $values = array(
                'firstname' => "",
                'lastname' => "",
                'user' => "",
                'phone' => "",
                'role' => "",
                'password' => "",
                'clientId' => "",
                'jobTitle' => '',
                'jobLevel' => '',
                'department' => '',

            );

            //only Admins
            if (Auth::userIsAtLeast(Roles::$manager)) {
                $projectrelation = array();

                if (isset($_POST['save'])) {
                    $values = array(
                        'firstname' => ($_POST['firstname']),
                        'lastname' => ($_POST['lastname']),
                        'user' => ($_POST['user']),
                        'phone' => ($_POST['phone']),
                        'role' => ($_POST['role']),
                        'password' => '',
                        'pwReset' => '',
                        'status' => '',
                        'jobTitle' => ($_POST['jobTitle']),
                        'jobLevel' => ($_POST['jobLevel']),
                        'department' => ($_POST['department']),
                        'clientId' => Auth::userHasRole(Roles::$manager) ? session("userdata.clientId") : $_POST['client'],
                    );
                    if (isset($_POST['projects']) && is_array($_POST['projects'])) {
                        foreach ($_POST['projects'] as $project) {
                            $projectrelation[] = $project;
                        }
                    }

                    if ($values['user'] !== '') {
                        if (filter_var($values['user'], FILTER_VALIDATE_EMAIL)) {
                            if ($this->userRepo->usernameExist($values['user']) === false) {
                                $userId = $this->userService->createUserInvite($values);

                                //Update Project Relationships
                                if (isset($_POST['projects']) && count($_POST['projects']) > 0) {
                                    if ($_POST['projects'][0] !== '0') {
                                        $this->projectsRepo->editUserProjectRelations($userId, $_POST['projects']);
                                    } else {
                                        $this->projectsRepo->deleteAllProjectRelations($userId);
                                    }
                                }

                                $this->tpl->setNotification("notification.user_invited_successfully", 'success', 'user_invited');
                            } else {
                                $this->tpl->setNotification($this->language->__("notification.user_exists"), 'error');
                            }
                        } else {
                            $this->tpl->setNotification($this->language->__("notification.no_valid_email"), 'error');
                        }
                    } else {
                        $this->tpl->setNotification($this->language->__("notification.enter_email"), 'error');
                    }
                }

                $this->tpl->assign('values', $values);
                $clients = app()->make(ClientRepository::class);

                if (isset($_GET['preSelectProjectId'])) {
                    $preSelected = explode(",", $_GET['preSelectProjectId']);

                    foreach ($preSelected as $item) {
                        $projectrelation[] = (int) $item;
                    }
                }

                $preSelectedClient = '';
                if (isset($_GET['preSelectedClient'])) {
                    $preSelectedClient = (int)$_GET['preSelectedClient'];
                }


                $this->tpl->assign('preSelectedClient', $preSelectedClient);
                $this->tpl->assign('clients', $clients->getAll());
                $this->tpl->assign('allProjects', $this->projectsRepo->getAll());
                $this->tpl->assign('roles', Roles::getRoles());

                $this->tpl->assign('relations', $projectrelation);

                return $this->tpl->displayPartial('users.newUser');
            } else {
                return $this->tpl->displayPartial('errors.error403');
            }
        }
    }
}

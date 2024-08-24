<?php

namespace Leantime\Domain\Users\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;
    use Leantime\Domain\Users\Services\Users;
    use Ramsey\Uuid\Uuid;

    /**
     *
     */
    class EditUser extends Controller
    {
        private ProjectRepository $projectsRepo;
        private UserRepository $userRepo;
        private ClientRepository $clientsRepo;
        private Users $userService;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(
            ProjectRepository $projectsRepo,
            UserRepository $userRepo,
            ClientRepository $clientsRepo,
            Users $userService
        ) {
            $this->projectsRepo = $projectsRepo;
            $this->userRepo = $userRepo;
            $this->clientsRepo = $clientsRepo;
            $this->userService = $userService;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

            //Only admins

            if (isset($_GET['id']) === true) {
                $id = (int)($_GET['id']);
                $row = $this->userRepo->getUser($id);
                $edit = false;
                $infoKey = '';

                //Build values array
                $values = array(
                    'id' => $row['id'],
                    'firstname' => $row['firstname'],
                    'lastname' => $row['lastname'],
                    'user' => $row['username'],
                    'phone' => $row['phone'],
                    'status' => $row['status'],
                    'role' => $row['role'],
                    'hours' => $row['hours'],
                    'wage' => $row['wage'],
                    'clientId' => $row['clientId'],
                    'source' =>  $row['source'],
                    'pwReset' => $row['pwReset'],
                    'jobTitle' => $row['jobTitle'],
                    'jobLevel' => $row['jobLevel'],
                    'department' => $row['department'],

                );

                if (isset($_GET['resendInvite']) && $row !== false) {
                    if (!session()->exists("lastInvite." . $values['id']) ||
                        session("lastInvite." . $values['id']) < time() - 240) {
                        session(["lastInvite." . $values['id'] => time()]);

                        //If pw reset is empty for whatever reason, create new invite code
                        if(empty($values['pwReset'])){
                            $inviteCode = Uuid::uuid4()->toString();
                            $this->userRepo->patchUser($values['id'], array("pwReset" => $inviteCode));
                            $values['pwReset'] = $inviteCode;
                        }


                        $this->userService->sendUserInvite(
                            inviteCode: $values['pwReset'],
                            user: $values['user']
                        );

                        $this->tpl->setNotification($this->language->__("notification.invitation_sent"), 'success', "userinvitation_sent");
                    } else {
                        $this->tpl->setNotification($this->language->__("notification.invite_too_soon"), 'error');
                    }

                    Frontcontroller::redirect(BASE_URL . '/users/editUser/' . $values['id']);
                }

                if (isset($_POST['save'])) {
                    if (isset($_POST[session("formTokenName")]) && $_POST[session("formTokenName")] == session("formTokenValue")) {
                        $values = array(
                            'id' =>  $row['id'],
                            'firstname' => ($_POST['firstname'] ?? $row['firstname']),
                            'lastname' => ($_POST['lastname'] ??  $row['lastname']),
                            'user' => ($_POST['user'] ?? $row['username']),
                            'phone' => ($_POST['phone'] ??  $row['phone']),
                            'status' => ($_POST['status'] ?? $row['status']),
                            'role' => ($_POST['role'] ?? $row['role']),
                            'hours' => ($_POST['hours'] ?? $row['hours']),
                            'wage' => ($_POST['wage'] ?? $row['wage']),
                            'clientId' => ($_POST['client'] ?? $row['clientId']),
                            'source' =>  $row['source'],
                            'pwReset' => $row['pwReset'],
                            'jobTitle' => ($_POST['jobTitle'] ?? $row['jobTitle']),
                            'jobLevel' => ($_POST['jobLevel'] ?? $row['jobLevel']),
                            'department' => ($_POST['department'] ?? $row['department']),
                        );

                        $changedEmail = 0;

                        if ($row['username'] != $values['user']) {
                            $changedEmail = 1;
                        }

                        if ($values['user'] !== '') {
                            if (!isset($_POST['password']) || ($_POST['password'] == $_POST['password2'])) {
                                if (filter_var($values['user'], FILTER_VALIDATE_EMAIL)) {
                                    if ($changedEmail == 1) {
                                        if ($this->userRepo->usernameExist($row['username'], $id) === false) {
                                            $edit = true;
                                        } else {
                                            $this->tpl->setNotification($this->language->__("notification.user_exists"), 'error');
                                        }
                                    } else {
                                        $edit = true;
                                    }
                                } else {
                                    $this->tpl->setNotification($this->language->__("notification.no_valid_email"), 'error');
                                }
                            } else {
                                $this->tpl->setNotification($this->language->__("notification.enter_email"), 'error');
                            }
                        } else {
                            $this->tpl->setNotification($this->language->__("notification.passwords_dont_match"), 'error');
                        }
                    } else {
                        $this->tpl->setNotification($this->language->__("notification.form_token_incorrect"), 'error');
                    }
                }

                //Was everything okay?
                if ($edit !== false) {
                    $this->userRepo->editUser($values, $id);

                    if (isset($_POST['projects'])) {
                        if ($_POST['projects'][0] !== '0') {
                            $this->projectsRepo->editUserProjectRelations($id, $_POST['projects']);
                        } else {
                            $this->projectsRepo->deleteAllProjectRelations($id);
                        }
                    } else {
                        //If projects is not set, all project assignments have been removed.
                        $this->projectsRepo->deleteAllProjectRelations($id);
                    }
                    $this->tpl->setNotification($this->language->__("notifications.user_edited"), 'success');
                }

                // Get relations to projects
                $projects = $this->projectsRepo->getUserProjectRelation($id);

                $projectrelation = array();

                foreach ($projects as $projectId) {
                    $projectrelation[] = $projectId['projectId'];
                }

                //Assign vars
                $this->tpl->assign('allProjects', $this->projectsRepo->getAll(true));
                $this->tpl->assign('roles', Roles::getRoles());
                $this->tpl->assign('clients', $this->clientsRepo->getAll());

                //Sensitive Form, generate form tokens
                $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
                session(["formTokenName" => substr(str_shuffle($permitted_chars), 0, 32)]);
                session(["formTokenValue" => substr(str_shuffle($permitted_chars), 0, 32)]);

                $this->tpl->assign('values', $values);
                $this->tpl->assign('relations', $projectrelation);

                $this->tpl->assign('status', $this->userRepo->status);
                $this->tpl->assign('id', $id);


                return $this->tpl->display('users.editUser');
            } else {
                return $this->tpl->display('errors.error403');
            }
        }
    }
}

<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class apiKey extends controller
    {
        private repositories\projects $projectsRepo;
        private repositories\users $userRepo;
        private repositories\clients $clientsRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(
            repositories\projects $projectsRepo,
            repositories\users $userRepo,
            repositories\clients $clientsRepo
        ) {
            $this->projectsRepo = $projectsRepo;
            $this->userRepo = $userRepo;
            $this->clientsRepo = $clientsRepo;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin], true);

            //Only admins

            if (isset($_GET['id']) === true) {
                $id = (int)($_GET['id']);
                $row = $this->userRepo->getUser($id);
                $edit = false;

                //Build values array
                $values = array(
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
                );

                if (isset($_POST['save'])) {
                    if (isset($_POST[$_SESSION['formTokenName']]) && $_POST[$_SESSION['formTokenName']] == $_SESSION['formTokenValue']) {
                        $values = array(
                            'firstname' => ($_POST['firstname'] ?? $row['firstname']),
                            'lastname' => '',
                            'user' => $row['username'],
                            'phone' => '',
                            'status' => ($_POST['status'] ?? $row['status']),
                            'role' => ($_POST['role'] ?? $row['role']),
                            'hours' => '',
                            'wage' => '',
                            'clientId' => '',
                            'password' => '',
                            'source' =>  'api',
                            'pwReset' => '',
                        );

                        $edit = true;
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
                    $this->tpl->setNotification($this->language->__("notifications.key_updated"), 'success');
                }

                // Get relations to projects
                $projects = $this->projectsRepo->getUserProjectRelation($id);

                $projectrelation = array();

                foreach ($projects as $projectId) {
                    $projectrelation[] = $projectId['projectId'];
                }

                //Assign vars
                $this->tpl->assign('allProjects', $this->projectsRepo->getAll());
                $this->tpl->assign('roles', roles::getRoles());
                $this->tpl->assign('clients', $this->clientsRepo->getAll());

                //Sensitive Form, generate form tokens
                $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
                $_SESSION['formTokenName'] = substr(str_shuffle($permitted_chars), 0, 32);
                $_SESSION['formTokenValue'] = substr(str_shuffle($permitted_chars), 0, 32);

                $this->tpl->assign('values', $values);
                $this->tpl->assign('relations', $projectrelation);

                $this->tpl->assign('status', $this->userRepo->status);
                $this->tpl->assign('id', $id);

                $this->tpl->displayPartial('api.apiKey');
            } else {
                $this->tpl->display('errors.error403');
            }
        }
    }
}

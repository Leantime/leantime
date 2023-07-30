<?php

namespace leantime\domain\controllers {

    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;

    class newApiKey extends controller
    {
        private repositories\users $userRepo;
        private repositories\projects $projectsRepo;
        private services\users $userService;
        private services\api $APIService;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(
            repositories\users $userRepo,
            repositories\projects $projectsRepo,
            services\users $userService,
            services\api $APIService
        ) {
            $this->userRepo = $userRepo;
            $this->projectsRepo = $projectsRepo;
            $this->userService = $userService;
            $this->APIService = $APIService;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {
            auth::authOrRedirect([roles::$owner, roles::$admin], true);

            $values = array(
                'firstname' => "",
                'lastname' => "",
                'user' => "",
                'role' => "",
                'password' => "",
                'status' => 'a',
                'source' => 'api'
            );

            //only Admins
            if (auth::userIsAtLeast(roles::$admin)) {

                $projectrelation = array();

                if (isset($_POST['save'])) {
                    $values = array(
                        'firstname' => ($_POST['firstname']),
                        'user' => '',
                        'role' => ($_POST['role']),
                        'password' => '',
                        'pwReset' => '',
                        'status' => '',
                        'source' => 'api'
                    );

                    if (isset($_POST['projects']) && is_array($_POST['projects'])) {
                        foreach ($_POST['projects'] as $project) {
                            $projectrelation[] = $project;
                        }
                    }

                    $apiKeyValues = $this->APIService->createAPIKey($values);

                    //Update Project Relationships
                    if (isset($_POST['projects']) && count($_POST['projects']) > 0) {
                        if ($_POST['projects'][0] !== '0') {
                            $this->projectsRepo->editUserProjectRelations($apiKeyValues['id'], $_POST['projects']);
                        } else {
                            $this->projectsRepo->deleteAllProjectRelations($apiKeyValues['id']);
                        }
                    }

                    $this->tpl->setNotification("notification.api_key_created", 'success');

                    $this->tpl->assign('apiKeyValues', $apiKeyValues);

                }

                $this->tpl->assign('values', $values);

                $this->tpl->assign('allProjects', $this->projectsRepo->getAll());
                $this->tpl->assign('roles', roles::getRoles());

                $this->tpl->assign('relations', $projectrelation);

                $this->tpl->displayPartial('api.newAPIKey');
            } else {
                $this->tpl->displayPartial('errors.error403');
            }
        }
    }
}

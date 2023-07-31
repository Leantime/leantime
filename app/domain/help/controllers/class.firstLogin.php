<?php

namespace leantime\domain\controllers {

    use Exception;
    use leantime\core\controller;
    use leantime\core\theme;
    use leantime\domain\models\auth\roles;
    use leantime\domain\services\auth;
    use leantime\domain\services\projects;
    use leantime\plugins\repositories\billing;

    class firstLogin extends controller
    {
        /**
         * get - handle get requests
         *
         * @access public
         *
         */
        public function get($params)
        {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager]);

            $step = 1;
            if (isset($_GET['step']) && is_numeric($_GET['step'])) {
                $step = $_GET['step'];
            }

            $this->tpl->assign('currentStep', $step);
            $this->tpl->displayPartial("help.firstLoginDialog");
        }

        /**
         * post - handle post requests
         *
         * @access public
         *
         */
        public function post($params)
        {
            $settingsRepo = new \leantime\domain\repositories\setting();

            if (isset($_POST['step']) && $_POST['step'] == 1) {

                if (isset($_POST['projectname'])) {
                    $projectService = new projects();
                    $projectService->patch($_SESSION['currentProject'], array("name" => $_POST['projectname']));
                    $projectService->changeCurrentSessionProject($_SESSION['currentProject']);
                }

                $settingsRepo->saveSetting("companysettings.completedOnboarding", true);

                $this->tpl->redirect(BASE_URL . "/help/firstLogin?step=2");
            }

            if (isset($_POST['step']) && $_POST['step'] == 2) {
                if (isset($_POST['theme'])) {
                    $postTheme = htmlentities($_POST['theme']);


                    $themeCore = new theme();

                    //Only save if it is actually available.
                    //Should not be an issue unless some shenanigans is happening
                    try {
                        $themeCore->setActive($postTheme);
                        $settingsRepo->saveSetting(
                            "usersettings." . $_SESSION['userdata']['id'] . ".theme",
                            $postTheme
                        );
                    } catch (Exception $e) {
                        error_log($e);
                    }
                }
                $this->tpl->redirect(BASE_URL . "/help/firstLogin?step=3");
            }

            if (isset($_POST['step']) && $_POST['step'] == 3) {
                $userService = new \leantime\domain\services\users();
                $projectsRepo = new \leantime\domain\repositories\projects();


                for ($i = 1; $i <= 3; $i++) {
                    if (isset($_POST['email' . $i]) && $_POST['email' . $i] != '') {
                        $values = array(
                            'firstname' => '',
                            'lastname' => '',
                            'user' => ($_POST['email' . $i]),
                            'phone' => '',
                            'role' => '20',
                            'password' => '',
                            'pwReset' => '',
                            'status' => '',
                            'clientId' => ''
                        );

                        if (filter_var($_POST['email' . $i], FILTER_VALIDATE_EMAIL)) {
                            if ($userService->usernameExist($_POST['email' . $i]) === false) {
                                $userId = $userService->createUserInvite($values);
                                $projectsRepo->editUserProjectRelations($userId, array($_SESSION['currentProject']));

                                $this->tpl->setNotification("notification.user_invited_successfully", 'success');
                            }
                        }
                    }
                }

                $this->tpl->redirect(BASE_URL . "/help/firstLogin?step=complete");
            }
        }

        /**
         * put - handle put requests
         *
         * @access public
         *
         */
        public function put($params)
        {
        }

        /**
         * delete - handle delete requests
         *
         * @access public
         *
         */
        public function delete($params)
        {
        }
    }

}

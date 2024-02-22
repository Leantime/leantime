<?php

namespace Leantime\Domain\Help\Controllers {

    use Exception;
    use Leantime\Core\Controller;
    use Leantime\Core\Frontcontroller;
    use Leantime\Core\Theme;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Help\Services\Helper;
    use Leantime\Domain\Projects\Services\Projects;
    use Leantime\Domain\Setting\Repositories\Setting;
    use Leantime\Domain\Users\Services\Users;

    /**
     *
     */
    class FirstLogin extends Controller
    {

        private Helper $helperService;

        public function init(Helper $helperService)
        {
            $this->$helperService = $helperService;
        }

        /**
         * get - handle get requests
         *
         * @access public
         *
         */
        public function get($params)
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager]);

            $allSteps = $this->helperService->getFirstLoginSteps();

            $step = key($allSteps[0]);
            if (isset($_GET['step']) && is_numeric($_GET['step'])) {
                $step = $_GET['step'];
            }



            $this->tpl->assign('currentStep', $step);
            return $this->tpl->displayPartial("help.firstLoginDialog");
        }

        /**
         * post - handle post requests
         *
         * @access public
         *
         */
        public function post($params)
        {
            $settingsRepo = app()->make(Setting::class);

            if (isset($_POST['step']) && $_POST['step'] == 1) {
                if (isset($_POST['projectname'])) {
                    $projectService = app()->make(Projects::class);
                    $projectService->patch($_SESSION['currentProject'], array("name" => $_POST['projectname']));
                    $projectService->changeCurrentSessionProject($_SESSION['currentProject']);
                }

                $settingsRepo->saveSetting("companysettings.completedOnboarding", true);

                return Frontcontroller::redirect(BASE_URL . "/help/firstLogin?step=2");
            }

            if (isset($_POST['step']) && $_POST['step'] == 2) {
                if (isset($_POST['theme'])) {
                    $postTheme = htmlentities($_POST['theme']);

                    $themeCore = app()->make(Theme::class);

                    //Only save if it is actually available.
                    //Should not be an issue unless some shenanigans is happening
                    try {
                        $themeCore->setColorMode($postTheme);

                    } catch (Exception $e) {
                        error_log($e);
                    }
                }
                return Frontcontroller::redirect(BASE_URL . "/help/firstLogin?step=3");
            }

            if (isset($_POST['step']) && $_POST['step'] == 3) {
                $userService = app()->make(Users::class);
                $projectsRepo = app()->make(\Leantime\Domain\Projects\Repositories\Projects::class);

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
                            'clientId' => '',
                        );

                        if (filter_var($_POST['email' . $i], FILTER_VALIDATE_EMAIL)) {
                            if ($userService->usernameExist($_POST['email' . $i]) === false) {
                                $userId = $userService->createUserInvite($values);
                                $projectsRepo->editUserProjectRelations($userId, array($_SESSION['currentProject']));

                                $this->tpl->setNotification("notification.user_invited_successfully", 'success', 'user_invited_' . $i);
                            }
                        }
                    }
                }

                return Frontcontroller::redirect(BASE_URL . "/help/firstLogin?step=complete");
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

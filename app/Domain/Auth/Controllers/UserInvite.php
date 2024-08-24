<?php

namespace Leantime\Domain\Auth\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Setting\Services\Setting;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class UserInvite extends Controller
{
    private AuthService $authService;
    private UserService $userService;
    private Setting $settingService;

    /**
     * init - initializes the objects for the class
     *
     * @access public
     *
     * @param AuthService $authService    The AuthService object
     * @param UserService $userService    The UserService object
     * @param Setting     $settingService The Setting object
     *
     * @return void
     *
     * @throws \Exception
     */
    public function init(
        AuthService $authService,
        UserService $userService,
        Setting $settingService
    ): void {
        $this->authService = $authService;
        $this->userService = $userService;
        $this->settingService = $settingService;
    }

    /**
     * get - handle get requests
     *
     * @access public
     *
     * @param array $params
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function get(array $params): Response
    {
        if (isset($_GET["id"]) === true) {
            $user = $this->authService->getUserByInviteLink($_GET["id"]);

            if (!$user) {
                return FrontcontrollerCore::redirect(BASE_URL . "/auth/login");
            }

            $this->tpl->assign("user", $user);

            if (isset($_GET['step']) && is_numeric($_GET['step'])) {
                return $this->tpl->display('auth.userInvite' . $_GET['step'], 'entry');
            }

            return $this->tpl->display('auth.userInvite', 'entry');
        }

        return FrontcontrollerCore::redirect(BASE_URL . "/errors/error404");
    }

    /**
     * post - handle post requests
     *
     * @access public
     *
     * @param array $params
     *
     * @return Response
     *
     * @throws BindingResolutionException
     */
    public function post(array $params): Response
    {

        $invitationId = $_GET["id"] ?? "";

            //Step 1
        if (isset($_POST["saveAccount"]) && isset($_POST["step"])) {
            $userInvite = $this->authService->getUserByInviteLink($invitationId);

            if (!isset($userInvite['id'])) {
                return FrontcontrollerCore::redirect(BASE_URL . "/auth/login");
            }

            if (strlen($_POST['password']) == 0 || $_POST['password'] != $_POST['password2']) {
                $this->tpl->setNotification($this->language->__('notification.passwords_dont_match'), "error");
                return FrontcontrollerCore::redirect(BASE_URL . "/auth/userInvite/" . $invitationId);
            }

            if (!$this->userService->checkPasswordStrength($_POST['password'])) {
                $this->tpl->setNotification(
                    $this->language->__("notification.password_not_strong_enough"),
                    'error'
                );

                return FrontcontrollerCore::redirect(BASE_URL . "/auth/userInvite/" . $invitationId);
            }

            $userInvite["firstname"] = $_POST["firstname"];
            $userInvite["lastname"] = $_POST["lastname"];
            $userInvite["jobTitle"] = $_POST["jobTitle"] ?? "";
            $userInvite["status"] = "I";
            $userInvite["user"] =  $userInvite["username"];
            $userInvite["password"] = $_POST['password'];
            session(["tempPassword" => $_POST['password']]);

            $editUser = $this->userService->editUser($userInvite, $userInvite["id"]);

            if ($editUser) {
                return FrontcontrollerCore::redirect(BASE_URL . "/auth/userInvite/" . $invitationId . "?step=2");
            } else {
                $this->tpl->setNotification(
                    $this->language->__('notifications.problem_updating_user'),
                    "error"
                );
            }
        }

        if (isset($_POST["challenge"]) && isset($_POST["step"]) && $_POST["step"] == 2) {
            $userInvite = $this->authService->getUserByInviteLink($invitationId);

            $challenge = $_POST["challenge"];

            $this->settingService->saveSetting("usersettings." . $userInvite['id'] . ".challenge", $challenge);

            return FrontcontrollerCore::redirect(BASE_URL . "/auth/userInvite/" . $invitationId . "?step=3");
        }

        if (isset($_POST["impact"]) && isset($_POST["step"]) && $_POST["step"] == 3) {
            $userInvite = $this->authService->getUserByInviteLink($invitationId);

            $challenge = $_POST["impact"];

            $this->settingService->saveSetting("usersettings." . $userInvite['id'] . ".impact", $challenge);

            return FrontcontrollerCore::redirect(BASE_URL . "/auth/userInvite/" . $invitationId . "?step=4");
        }

        if (isset($_POST["function"]) && isset($_POST["step"]) && $_POST["step"] == 4) {

            $userInvite = $this->authService->getUserByInviteLink($invitationId);

            $function = $_POST["function"];

            $this->settingService->saveSetting("usersettings." . $userInvite['id'] . ".function", $function);

            return FrontcontrollerCore::redirect(BASE_URL . "/auth/userInvite/" . $invitationId . "?step=5");
        }

        if (isset($_POST["complete"]) && isset($_POST["step"]) && $_POST["step"] == 5) {

            $userInvite = $this->authService->getUserByInviteLink($invitationId);

            $userInvite["status"] = "A";
            $userInvite["password"] = "";
            $userInvite["user"] =  $userInvite["username"];

            $result = $this->userService->editUser($userInvite, $userInvite["id"]);

            $this->tpl->setNotification(
                $this->language->__('notifications.you_are_active'),
                "success",
                "user_activated"
            );
            $loggedIn = $this->authService->login($userInvite["username"], session("tempPassword"));

            session()->forget("tempPassword");

            self::dispatch_event("userSignUpSuccess", ['user' => $userInvite]);

            if ($loggedIn) {
                return FrontcontrollerCore::redirect(BASE_URL . "/dashboard/show");
            } else {
                return FrontcontrollerCore::redirect(BASE_URL . "/auth/login");
            }
        }

        return FrontcontrollerCore::redirect(BASE_URL . "/auth/userInvite/" . $invitationId);
    }
}

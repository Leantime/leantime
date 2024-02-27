<?php

namespace Leantime\Domain\Auth\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller;
use Leantime\Core\Frontcontroller as FrontcontrollerCore;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Files\Repositories\Files as FileRepository;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class UserInvite extends Controller
{
    private FileRepository $fileRepo;
    private AuthService $authService;
    private UserService $userService;

    /**
     * init - initialize private variables
     *
     * @access public
     *
     * @param FileRepository $fileRepo
     * @param AuthService    $authService
     * @param UserService    $userService
     *
     * @return void
     */
    public function init(
        FileRepository $fileRepo,
        AuthService $authService,
        UserService $userService
    ): void {
        $this->fileRepo = $fileRepo;
        $this->authService = $authService;
        $this->userService = $userService;
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

            if ($user) {
                $this->tpl->assign("user", $user);
                return $this->tpl->display('auth.userInvite', 'entry');
            } else {
                return FrontcontrollerCore::redirect(BASE_URL . "/auth/login");
            }
        }
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

        if (isset($_POST["saveAccount"])) {
            $userInvite = $this->authService->getUserByInviteLink($_GET["id"]);

            if (isset($_POST['password']) === true && isset($_POST['password2']) === true) {
                if (strlen($_POST['password']) == 0 || $_POST['password'] != $_POST['password2']) {
                    $this->tpl->setNotification($this->language->__('notification.passwords_dont_match'), "error");

                    return FrontcontrollerCore::redirect(BASE_URL . "/auth/userInvite/" . $_GET['id']);
                } else {
                    if ($this->userService->checkPasswordStrength($_POST['password'])) {
                        if (isset($userInvite['id'])) {
                            $user = $this->userService->getUser($userInvite['id']);
                        } else {
                            return FrontcontrollerCore::redirect(BASE_URL . "/auth/login");
                        }

                        $user["firstname"] = $_POST["firstname"];
                        $user["lastname"] = $_POST["lastname"];
                        $user["status"] = "A";
                        $user["user"] =  $user["username"];
                        $user["password"] = $_POST['password'];

                        $editUser = $this->userService->editUser($user, $user["id"]);

                        if ($editUser) {
                            $this->tpl->setNotification(
                                $this->language->__('notifications.you_are_active'),
                                "success",
                                "user_activated"
                            );
                            $loggedIn = $this->authService->login($user["username"], $_POST['password']);

                            self::dispatch_event("userSignUpSuccess", ['user' => $user]);

                            if ($loggedIn) {
                                return FrontcontrollerCore::redirect(BASE_URL . "/dashboard/home");
                            } else {
                                return FrontcontrollerCore::redirect(BASE_URL . "/auth/login");
                            }
                        } else {
                            $this->tpl->setNotification(
                                $this->language->__('notifications.problem_updating_user'),
                                "error"
                            );

                            return FrontcontrollerCore::redirect(BASE_URL . "/auth/userInvite/" . $_GET['id']);
                        }
                    } else {
                        $this->tpl->setNotification(
                            $this->language->__("notification.password_not_strong_enough"),
                            'error'
                        );

                        return FrontcontrollerCore::redirect(BASE_URL . "/auth/userInvite/" . $_GET['id']);
                    }
                }
            }
        }

        return FrontcontrollerCore::redirect(BASE_URL . "/auth/userInvite/");
    }
}

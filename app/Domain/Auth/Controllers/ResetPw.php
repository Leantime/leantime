<?php

namespace Leantime\Domain\Auth\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Files\Repositories\Files as FileRepository;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class ResetPw extends Controller
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
        if ((isset($_GET["id"]) === true && $this->authService->validateResetLink($_GET["id"]))) {
            return $this->tpl->display('auth.resetPw', 'entry');
        } else {
            return $this->tpl->display('auth.requestPwLink', 'entry');
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
        if (!isset($_POST['resetPassword'])) {
            return FrontcontrollerCore::redirect(BASE_URL . "/auth/resetPw/");
        }

        if (isset($_POST['username']) === true) {
            //Always return success to prevent db attacks checking which email address are in there
            $this->authService->generateLinkAndSendEmail($_POST["username"]);
            $this->tpl->setNotification($this->language->__('notifications.email_was_sent_to_reset'), "success");

            return FrontcontrollerCore::redirect(BASE_URL . '/auth/resetPw/');
        }

        if (isset($_POST['password']) === true && isset($_POST['password2']) === true) {
            if (strlen($_POST['password']) == 0 || $_POST['password'] != $_POST['password2']) {
                $this->tpl->setNotification($this->language->__('notification.passwords_dont_match'), "error");

                return FrontcontrollerCore::redirect(BASE_URL . "/auth/resetPw/" . $_GET['id']);
            }

            if ($this->userService->checkPasswordStrength($_POST['password'])) {
                if ($this->authService->changePW($_POST['password'], $_GET['id'])) {
                    $this->tpl->setNotification(
                        $this->language->__('notifications.passwords_changed_successfully'),
                        "success",
                        "password_changed"
                    );

                    return FrontcontrollerCore::redirect(BASE_URL . "/auth/login");
                }

                $this->tpl->setNotification(
                    $this->language->__('notifications.problem_resetting_password'),
                    "error"
                );

                return FrontcontrollerCore::redirect(BASE_URL . "/auth/resetPw/" . $_GET['id']);
            }

            $this->tpl->setNotification(
                $this->language->__("notification.password_not_strong_enough"),
                'error'
            );

            return FrontcontrollerCore::redirect(BASE_URL . "/auth/resetPw/" . $_GET['id']);
        }

        $this->tpl->setNotification(
            $this->language->__('notifications.problem_resetting_password'),
            "error"
        );

        return FrontcontrollerCore::redirect(BASE_URL . '/auth/resetPw/' . $_GET["id"] ?? '');
    }
}

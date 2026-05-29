<?php

namespace Leantime\Domain\Auth\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Symfony\Component\HttpFoundation\Response;

class ResetPw extends Controller
{
    private AuthService $authService;

    /**
     * init - initialize private variables
     */
    public function init(
        AuthService $authService
    ): void {
        $this->authService = $authService;
    }

    /**
     * get - handle get requests
     *
     *
     *
     *
     * @throws \Exception
     */
    public function get(array $params): Response
    {
        if ((isset($params['id']) === true && $this->authService->validateResetLink($params['id']))) {
            return $this->tpl->display('auth.resetPw', 'entry');
        } else {
            return $this->tpl->display('auth.requestPwLink', 'entry');
        }
    }

    /**
     * post - handle post requests
     *
     *
     *
     *
     * @throws BindingResolutionException
     */
    public function post(array $params): Response
    {
        if (! isset($_POST['resetPassword'])) {
            return FrontcontrollerCore::redirect(BASE_URL.'/auth/resetPw/');
        }

        if (isset($_POST['username']) === true) {
            // Always return success to prevent db attacks checking which email address are in there
            $this->authService->generateLinkAndSendEmail($_POST['username']);
            $this->tpl->setNotification($this->language->__('notifications.email_was_sent_to_reset'), 'success');

            return FrontcontrollerCore::redirect(BASE_URL.'/auth/resetPw/');
        }

        if (isset($_POST['password']) === true && isset($_POST['password2']) === true) {
            $result = $this->authService->resetPassword($_POST['password'], $_POST['password2'], $params['id']);

            if ($result === 'success') {
                $this->tpl->setNotification(
                    $this->language->__('notifications.passwords_changed_successfully'),
                    'success',
                    'password_changed'
                );

                return FrontcontrollerCore::redirect(BASE_URL.'/auth/login');
            }

            if ($result === 'mismatch') {
                $this->tpl->setNotification($this->language->__('notification.passwords_dont_match'), 'error');

                return FrontcontrollerCore::redirect(BASE_URL.'/auth/resetPw/'.$params['id']);
            }

            if ($result === 'weak') {
                $this->tpl->setNotification(
                    $this->language->__('notification.password_not_strong_enough'),
                    'error'
                );

                return FrontcontrollerCore::redirect(BASE_URL.'/auth/resetPw/'.$params['id']);
            }

            $this->tpl->setNotification(
                $this->language->__('notifications.problem_resetting_password'),
                'error'
            );

            return FrontcontrollerCore::redirect(BASE_URL.'/auth/resetPw/'.$params['id']);
        }

        $this->tpl->setNotification(
            $this->language->__('notifications.problem_resetting_password'),
            'error'
        );

        return FrontcontrollerCore::redirect(BASE_URL.'/auth/resetPw/'.$params['id'] ?? '');
    }
}

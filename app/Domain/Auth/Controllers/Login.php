<?php

namespace Leantime\Domain\Auth\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Symfony\Component\HttpFoundation\Response;

class Login extends Controller
{
    private AuthService $authService;

    private Environment $config;

    /**
     * init - initialize private variables
     */
    public function init(
        AuthService $authService,
        Environment $config
    ): void {
        $this->authService = $authService;
        $this->config = $config;
    }

    /**
     * get - handle get requests
     *
     *
     *
     *
     * @throws BindingResolutionException
     */
    public function get(array $params): Response
    {
        self::dispatchEvent('beforeAuth', $params);

        $return = self::dispatchFilter('beforeAuthHandling', $params);
        if ($return instanceof Response) {
            return $return;
        }

        $redirectUrl = $this->authService->resolveSafeRedirect($_GET['redirect'] ?? null);

        $this->tpl->assign('inputPlaceholder', $this->authService->getLoginInputPlaceholder());
        $this->tpl->assign('redirectUrl', urlencode($redirectUrl));
        $this->tpl->assign('oidcEnabled', $this->config->oidcEnable);
        $this->tpl->assign('noLoginForm', $this->authService->shouldHideLoginForm());

        return $this->tpl->display('auth.login', 'entry');
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
        if (isset($_POST['username']) === true && isset($_POST['password']) === true) {
            if (isset($_POST['redirectUrl'])) {
                $redirectUrl = urldecode(filter_var($_POST['redirectUrl'], FILTER_SANITIZE_URL));
            } else {
                $redirectUrl = '';
            }

            $username = trim($_POST['username']);
            $password = $_POST['password'];

            try {
                // Allow login interruptions through events
                self::dispatch_event('beforeAuthServiceCall', ['post' => $_POST]);

            } catch (\Exception $e) {

                $this->tpl->setNotification($e->getMessage(), 'error');

                return FrontcontrollerCore::redirect(BASE_URL.'/auth/login');
            }

            // If login successful redirect to the correct url to avoid post on reload
            if ($this->authService->login($username, $password) === true) {

                self::dispatch_event('successfulLogin', ['post' => $_POST]);

                if ($this->authService->use2FA()) {
                    return FrontcontrollerCore::redirect(BASE_URL.'/auth/twoFA');
                }

                return FrontcontrollerCore::redirect($redirectUrl);
            } else {
                $this->tpl->setNotification('notifications.username_or_password_incorrect', 'error');

                return FrontcontrollerCore::redirect(BASE_URL.'/auth/login');
            }
        } else {
            $this->tpl->setNotification('notifications.username_or_password_missing', 'error');

            return FrontcontrollerCore::redirect(BASE_URL.'/auth/login');
        }
    }
}

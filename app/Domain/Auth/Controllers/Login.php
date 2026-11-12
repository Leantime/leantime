<?php

namespace Leantime\Domain\Auth\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Auth\Services\Onboarding as OnboardingService;
use Symfony\Component\HttpFoundation\Response;

class Login extends Controller
{
    private AuthService $authService;

    private Environment $config;

    private OnboardingService $onboardingService;

    /**
     * init - initialize private variables
     */
    public function init(
        AuthService $authService,
        Environment $config,
        OnboardingService $onboardingService
    ): void {
        $this->authService = $authService;
        $this->config = $config;
        $this->onboardingService = $onboardingService;
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

        // Guard the type: redirect[]=x arrives as an array, which would TypeError against
        // resolveSafeRedirect(?string) and 500 the login page on malformed input.
        $rawRedirect = $_GET['redirect'] ?? null;
        $redirectUrl = $this->authService->resolveSafeRedirect(is_string($rawRedirect) ? $rawRedirect : null);

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

            // Same array guard as the GET path above — redirectUrl[]=x must not 500 the login POST.
            $rawRedirect = $_POST['redirectUrl'] ?? null;
            $redirectUrl = $this->authService->resolveSafeRedirect(is_string($rawRedirect) ? $rawRedirect : null);

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
                // Right password, but the account's onboarding was left before the last step, so
                // it is still pending. Send the person back into it instead of a misleading error.
                $inviteToken = $this->onboardingService->resumeOnboarding($username, $password);
                if ($inviteToken !== null) {
                    $this->tpl->setNotification('notifications.finish_account_setup', 'info');

                    return FrontcontrollerCore::redirect(BASE_URL.'/auth/userInvite/'.$inviteToken.'?step=2');
                }

                $this->tpl->setNotification('notifications.username_or_password_incorrect', 'error');

                return FrontcontrollerCore::redirect(BASE_URL.'/auth/login');
            }
        } else {
            $this->tpl->setNotification('notifications.username_or_password_missing', 'error');

            return FrontcontrollerCore::redirect(BASE_URL.'/auth/login');
        }
    }
}

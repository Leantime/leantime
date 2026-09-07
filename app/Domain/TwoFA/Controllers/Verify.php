<?php

namespace Leantime\Domain\TwoFA\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Symfony\Component\HttpFoundation\Response;

class Verify extends Controller
{
    private AuthService $authService;

    /**
     * init - initialize private variables
     *
     * @params parameters or body of the request
     */
    public function init(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * get - handle get requests
     *
     * @params parameters or body of the request
     */
    public function get($params)
    {
        // Guard the type: redirect[]=x arrives as an array, which would TypeError
        // against resolveSafeRedirect(?string). Route the user-supplied value
        // through resolveSafeRedirect() (as Login::get() does) so an open-redirect
        // target (e.g. //attacker.com) can never reach the redirect.
        $rawRedirect = $_GET['redirect'] ?? null;
        $redirectUrl = $this->authService->resolveSafeRedirect(is_string($rawRedirect) ? $rawRedirect : null);

        $this->tpl->assign('redirectUrl', $redirectUrl);

        return $this->tpl->display('twofa.verify', 'entry');
    }

    public function post($params): Response
    {

        if (session()->exists('userdata') && $this->authService->use2FA()) {
            if (isset($params['twoFA_code']) === true) {
                $redirectUrl = filter_var($params['redirectUrl'], FILTER_SANITIZE_URL);

                if ($this->authService->verify2FA($params['twoFA_code'])) {
                    $this->authService->set2FAVerified();

                    return FrontcontrollerCore::redirect($redirectUrl);
                } else {
                    $this->tpl->setNotification('notification.incorrect_twoFA_code', 'error');

                    return FrontcontrollerCore::redirect(BASE_URL.'/twoFA/verify');
                }
            } else {
                $this->tpl->setNotification('notification.incorrect_twoFA_code', 'error');

                return FrontcontrollerCore::redirect(BASE_URL.'/twoFA/verify');
            }
        }

        /** @todo make a 400 response page **/
        return $this->tpl->display('error.400');
    }
}

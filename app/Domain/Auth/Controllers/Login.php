<?php

namespace Leantime\Domain\Auth\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class Login extends Controller
{
    private AuthService $authService;
    private Environment $config;

    /**
     * init - initialize private variables
     *
     * @access public
     *
     * @param AuthService $authService
     * @param Environment $config
     *
     * @return void
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
     * @access public
     *
     * @param array $params
     *
     * @return Response
     *
     * @throws BindingResolutionException
     */
    public function get(array $params): Response
    {
        self::dispatch_event('beforeAuth', $params);

        $redirectUrl = BASE_URL . "/dashboard/home";

        if (isset($_GET['redirect']) && trim($_GET['redirect']) !== '' && trim($_GET['redirect']) !== '/') {
            $url = urldecode($_GET['redirect']);

            //Check for open redirects, don't allow redirects to external sites.
            if (
                filter_var($url, FILTER_VALIDATE_URL) === false &&
                !in_array($url, ["/auth/logout"])
            ) {
                $redirectUrl = BASE_URL ."/" . $url;
            }
        }

        if ($this->config->useLdap) {
            $this->tpl->assign("inputPlaceholder", "input.placeholders.enter_email_or_username");
        } else {
            $this->tpl->assign("inputPlaceholder", "input.placeholders.enter_email");
        }
        $this->tpl->assign('redirectUrl', urlencode($redirectUrl));

        $this->tpl->assign('oidcEnabled', $this->config->oidcEnable);
        $this->tpl->assign('noLoginForm', $this->config->disableLoginForm);

        return $this->tpl->display('auth.login', 'entry');
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
        if (isset($_POST['username']) === true && isset($_POST['password']) === true) {
            if (isset($_POST['redirectUrl'])) {
                $redirectUrl = urldecode(filter_var($_POST['redirectUrl'], FILTER_SANITIZE_URL));
            } else {
                $redirectUrl = "";
            }

            $username = filter_var($_POST['username'], FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];

            self::dispatch_event("beforeAuthServiceCall", ['post' => $_POST]);

            //If login successful redirect to the correct url to avoid post on reload
            if ($this->authService->login($username, $password) === true) {
                if ($this->authService->use2FA()) {
                    return FrontcontrollerCore::redirect(BASE_URL . "/auth/twoFA");
                }

                self::dispatch_event("afterAuthServiceCall", ['post' => $_POST]);

                return FrontcontrollerCore::redirect($redirectUrl);
            } else {
                $this->tpl->setNotification("notifications.username_or_password_incorrect", "error");
                return FrontcontrollerCore::redirect(BASE_URL . "/auth/login");
            }
        } else {
            $this->tpl->setNotification("notifications.username_or_password_missing", "error");
            return FrontcontrollerCore::redirect(BASE_URL . "/auth/login");
        }
    }
}

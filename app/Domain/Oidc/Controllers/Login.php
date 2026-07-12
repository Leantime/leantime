<?php

namespace Leantime\Domain\Oidc\Controllers;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Oidc\Services\Oidc as OidcService;
use Symfony\Component\HttpFoundation\Response;

class Login extends Controller
{
    private OidcService $oidc;

    /**
     * Initializes dependencies.
     *
     * @throws GuzzleException
     */
    public function init(OidcService $oidc): void
    {
        $this->oidc = $oidc;
    }

    /**
     * Redirects to the OIDC provider login page.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        try {
            // Mobile-brokered SSO: the app passes ?mobile=1&redirect_uri=<app scheme>
            // so the callback mints a token + one-time code and redirects back to
            // the app instead of establishing a web session. The service validates
            // the redirect scheme; a non-mobile web login passes neither.
            $mobile = ! empty($params['mobile']);
            $redirectUri = is_string($params['redirect_uri'] ?? null) ? $params['redirect_uri'] : '';

            $loginUrl = $this->oidc->buildLoginUrl($mobile, $redirectUri);

            if ($loginUrl) {
                return Frontcontroller::redirect($loginUrl, 302);
            }
        } catch (\Throwable $e) {
            Log::error($e);
        }

        $this->tpl->setNotification('Auth URL could not be found. Check the logs for more details', 'error');

        return Frontcontroller::redirect(BASE_URL.'/auth/login');
    }
}

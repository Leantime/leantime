<?php

namespace Leantime\Domain\Oidc\Controllers;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Http\Controller\Controller;
use Leantime\Core\Routing\Frontcontroller;
use Leantime\Domain\Oidc\Services\Oidc as OidcService;
use Symfony\Component\HttpFoundation\Response;

class Login extends Controller
{
    private OidcService $oidc;

    /**
     * @throws GuzzleException
     */
    public function init(OidcService $oidc): void
    {
        $this->oidc = $oidc;
    }

    public function run(): Response
    {

        try {
            $loginUrl = $this->oidc->buildLoginUrl();

            if ($loginUrl) {
                return Frontcontroller::redirect($this->oidc->buildLoginUrl(), 302);
            }
        } catch (\Throwable $e) {
            Log::error($e);
        }

        $this->tpl->setNotification('Auth URL could not be found. Check the logs for more details', 'error');

        return Frontcontroller::redirect(BASE_URL.'/auth/login');
    }
}

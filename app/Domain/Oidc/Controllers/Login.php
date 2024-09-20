<?php

namespace Leantime\Domain\Oidc\Controllers;

use GuzzleHttp\Exception\GuzzleException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Oidc\Services\Oidc as OidcService;
use Symfony\Component\HttpFoundation\Response;

class Login extends Controller
{
    private OidcService $oidc;

    /**
     * @param OidcService $oidc
     *
     * @throws GuzzleException
     *
     * @return void
     */
    public function init(OidcService $oidc)
    {
        $this->oidc = $oidc;
    }

    public function run(): Response
    {
        return Frontcontroller::redirect($this->oidc->buildLoginUrl(), 302);
    }
}

<?php

namespace Leantime\Domain\Oidc\Controllers;

use GuzzleHttp\Exception\GuzzleException;
use Leantime\Core\Controller;
use Leantime\Domain\Oidc\Services\Oidc as OidcService;
use Leantime\Core\Frontcontroller;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class Login extends Controller
{
    private OidcService $oidc;

    /**
     * @param OidcService     $oidc
     * @return void
     * @throws GuzzleException
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

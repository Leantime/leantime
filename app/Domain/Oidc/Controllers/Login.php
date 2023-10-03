<?php

namespace Leantime\Domain\Oidc\Controllers;

use GuzzleHttp\Exception\GuzzleException;
use Leantime\Core\Controller;
use Leantime\Domain\Oidc\Services\Oidc as OidcService;
use Leantime\Core\Frontcontroller;

/**
 *
 */

/**
 *
 */
class Login extends Controller
{
    private OidcService $oidc;

    /**
     * @param OidcService     $oidc
     * @param Frontcontroller $frontcontroller
     * @return void
     * @throws GuzzleException
     */
    public function init(OidcService $oidc, frontcontroller $frontcontroller)
    {
        $this->oidc = $oidc;
        $frontcontroller::redirect($this->oidc->buildLoginUrl(), 302);
    }
}

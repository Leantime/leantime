<?php

namespace Leantime\Domain\Oidc\Controllers;

use Leantime\Core\Controller;
use Leantime\Domain\Oidc\Services\Oidc as OidcService;
use Leantime\Core\Frontcontroller;

class Login extends Controller
{
    private OidcService $oidc;

    public function init(OidcService $oidc, frontcontroller $frontcontroller)
    {
        $this->oidc = $oidc;
        $frontcontroller::redirect($this->oidc->buildLoginUrl(), 302);
    }
}

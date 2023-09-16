<?php

namespace Leantime\Domain\Oidc\Controllers;

use Exception;
use Leantime\Core\Controller;
use Leantime\Domain\Oidc\Services\Oidc as OidcService;

class Callback extends Controller
{
    private OidcService $oidc;

    public function init(OidcService $oidc)
    {
        $this->oidc = $oidc;
    }

    public function get($params)
    {
        $code = $_GET['code'];
        $state = $_GET['state'];
        try {
            $this->oidc->callback($code, $state);
        } catch (Exception $ex) {
            error_log($ex);
        }
    }
}

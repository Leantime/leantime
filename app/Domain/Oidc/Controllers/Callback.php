<?php

namespace Leantime\Domain\Oidc\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Leantime\Core\Controller;
use Leantime\Domain\Oidc\Services\Oidc as OidcService;

/**
 *
 */
class Callback extends Controller
{
    private OidcService $oidc;

    /**
     * @param OidcService $oidc
     * @return void
     */
    public function init(OidcService $oidc): void
    {
        $this->oidc = $oidc;
    }

    /**
     * @param $params
     * @return void
     * @throws GuzzleException
     */
    /**
     * @param $params
     * @return void
     * @throws GuzzleException
     */
    public function get($params): void
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

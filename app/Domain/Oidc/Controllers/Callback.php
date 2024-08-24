<?php

namespace Leantime\Domain\Oidc\Controllers;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Exception\HttpResponseException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Oidc\Services\Oidc as OidcService;
use Symfony\Component\HttpFoundation\Response;

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
     * @return Response
     * @throws GuzzleException|HttpResponseException
     */
    public function get($params): Response
    {
        $code = $_GET['code'];
        $state = $_GET['state'];

        try {
            return $this->oidc->callback($code, $state);
        } catch (\Exception $e) {
            $this->tpl->setNotification($e->getMessage(), 'danger', 'oidc_error');
            return Frontcontroller::redirect(BASE_URL . '/oidc/login');
        }
    }
}

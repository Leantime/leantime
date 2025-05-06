<?php

namespace Leantime\Domain\Auth\Controllers;

use Leantime\Core\Http\Controller\Controller;
use Leantime\Core\Routing\Frontcontroller as FrontcontrollerCore;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Symfony\Component\HttpFoundation\Response;

class Logout extends Controller
{
    private AuthService $authService;

    /**
     * init - initialize private variables
     */
    public function init(AuthService $authService): void
    {
        $this->authService = $authService;
    }

    /**
     * get - handle get requests
     */
    public function get(array $params): Response
    {
        $this->authService->logout();

        return FrontcontrollerCore::redirect(BASE_URL.'/');
    }
}

<?php

namespace Leantime\Domain\Auth\Controllers;

use Laravel\Socialite\Facades\Socialite;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Services\AccessToken;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeping the session alive when not active
 *
 * @Deprecated With laravels new session management we should not need this anymore
 */
class Redirect extends Controller
{
    private AuthService $authService;

    private AccessToken $personalToken;

    /**
     * init - initialize private variables
     */
    public function init(
        AuthService $authService,
        AccessToken $personalToken
    ): void {
        $this->authService = $authService;
        $this->personalToken = $personalToken;
    }

    /**
     * get - handle get requests
     */
    public function run(array $params): Response
    {

        return Socialite::driver('github')->setScopes(['user:email'])->redirect();

    }
}

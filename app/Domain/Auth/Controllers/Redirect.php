<?php

namespace Leantime\Domain\Auth\Controllers;

use Laravel\Socialite\Facades\Socialite;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Services\AccessToken;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects to the OAuth provider for authentication.
 */
class Redirect extends Controller
{
    private AuthService $authService;

    private AccessToken $personalToken;

    /**
     * Initializes dependencies.
     */
    public function init(
        AuthService $authService,
        AccessToken $personalToken
    ): void {
        $this->authService = $authService;
        $this->personalToken = $personalToken;
    }

    /**
     * Redirects to the GitHub OAuth login page.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        return Socialite::driver('github')->setScopes(['user:email'])->redirect();
    }
}

<?php

namespace Leantime\Domain\Auth\Controllers;

use Laravel\Socialite\Facades\Socialite;
use Leantime\Core\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects to the OAuth provider for authentication.
 */
class Redirect extends Controller
{
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

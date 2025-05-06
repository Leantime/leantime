<?php

namespace Leantime\Infrastructure\Auth\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Session\Middleware\AuthenticatesSessions;
use Illuminate\Http\Request;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Infrastructure\i18n\Language;
use Leantime\Domain\Setting\Services\Setting;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateSession implements AuthenticatesSessions
{
    /**
     * Create a new middleware instance.
     *
     * @return void
     */
    public function __construct(
        protected AuthFactory $auth,
        private readonly Setting $settings,
        private readonly Environment $config,
        private readonly Language $language) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $request->hasSession() || ! $request->user()) {
            return $next($request);
        }

        if ($this->guard()->viaRemember()) {
            $passwordHash = explode('|', $request->cookies->get($this->guard()->getRecallerName()))[2] ?? null;

            if (! $passwordHash || $passwordHash != $request->user()->getAuthPassword()) {
                $this->logout($request);
            }
        }

        if (! $request->session()->has('password_hash_'.$this->auth->getDefaultDriver())) {
            $this->storePasswordHashInSession($request);
        }

        if ($request->session()->get('password_hash_'.$this->auth->getDefaultDriver()) !== $request->user(
        )->getAuthPassword()) {
            $this->logout($request);
        }

        return tap($next($request), function () use ($request) {
            if (! is_null($this->guard()->user())) {
                $this->storePasswordHashInSession($request);
            }
        });
    }

    /**
     * Store the user's current password hash in the session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function storePasswordHashInSession($request)
    {
        if (! $request->user()) {
            return;
        }

        $request->session()->put([
            'password_hash_'.$this->auth->getDefaultDriver() => $request->user()->getAuthPassword(),
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function logout($request)
    {
        $this->guard()->logoutCurrentDevice();

        $request->session()->flush();

        throw new AuthenticationException(
            'Unauthenticated.', [$this->auth->getDefaultDriver()], $this->redirectTo($request)
        );
    }

    /**
     * Get the guard instance that should be used by the middleware.
     *
     * @return \Illuminate\Contracts\Auth\Factory|\Illuminate\Contracts\Auth\Guard
     */
    protected function guard()
    {
        return $this->auth;
    }

    /**
     * Get the path the user should be redirected to when their session is not authenticated.
     *
     * @return string|null
     */
    protected function redirectTo(Request $request)
    {
        //
    }

    public function setLeantimeSession(IncomingRequest $request, Closure $next): Response
    {
        if (! $request->hasSession() || ! $request->user()) {
            session(['userdata' => null]);

            return $next($request);
        }

        $user = $request->user();

        // Set up the user session data
        $currentUser = [
            'id' => (int) $user->id,
            'name' => strip_tags($user->firstname),
            'profileId' => $user->profileId,
            'mail' => filter_var($user->username, FILTER_SANITIZE_EMAIL),
            'clientId' => $user->clientId,
            'role' => $user->role,
            'settings' => $user->settings ? unserialize($user->settings) : [],
            'twoFAEnabled' => $user->twoFAEnabled ?? false,
            'twoFAVerified' => false,
            'twoFASecret' => $user->twoFASecret ?? '',
            'isExternalAuth' => false,
            'createdOn' => ! empty($user->createdOn) ? dtHelper()->parseDbDateTime($user->createdOn) : dtHelper()->userNow(),
            'modified' => ! empty($user->modified) ? dtHelper()->parseDbDateTime($user->modified) : dtHelper()->userNow(),
        ];

        session(['userdata' => $currentUser]);

        return $next($request);
    }
}

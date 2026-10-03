<?php

namespace Leantime\Core\Middleware;

use Closure;
use Illuminate\Contracts\Session\Middleware\AuthenticatesSessions;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Auth\PasswordFingerprint;
use Leantime\Core\Http\ApiRequest;
use Leantime\Core\Http\HtmxRequest;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ends web sessions whose password is no longer current.
 *
 * Login pins the session to a fingerprint of the user's password hash
 * ({@see PasswordFingerprint}). Each request compares it with the hash in the database; after a
 * password reset or change every other session of that user no longer matches and is logged
 * out. Web sessions without a fingerprint (created before it existed) must sign in again.
 * Costs one primary-key lookup per authenticated web request.
 */
class AuthenticateSession implements AuthenticatesSessions
{
    /**
     * Request attribute set by AuthCheck when the request was authenticated by a validated API key
     * or Bearer token (rather than the web session).
     */
    public const TOKEN_AUTHENTICATED = 'leantime.tokenAuthenticated';

    public function __construct(
        private readonly UserRepository $userRepo,
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(IncomingRequest $request, Closure $next): Response
    {
        if (! $request->hasSession()) {
            return $next($request);
        }

        $session = $request->session();
        $userId = (int) $session->get('userdata.id', 0);

        if ($userId === 0) {
            return $next($request);
        }

        $storedFingerprint = $session->get(PasswordFingerprint::SESSION_KEY);

        // Requests authenticated by a validated API key / Bearer token rebuild userdata from that
        // credential and never carry a fingerprint. Only AuthCheck's verdict counts — a credential
        // header that is merely present proves nothing.
        if ($storedFingerprint === null && $request->attributes->get(self::TOKEN_AUTHENTICATED) === true) {
            return $next($request);
        }

        $user = $this->userRepo->getUser($userId);

        if (! is_array($user)) {
            return $this->endSession($request, 'user no longer exists');
        }

        $currentPasswordHash = $user['password'] ?? '';

        // A web session without a fingerprint predates it (or was built outside the login flow).
        // Its password state can't be verified, so it has to sign in again.
        if (! is_string($storedFingerprint) || $storedFingerprint === '') {
            return $this->endSession($request, 'session has no password fingerprint');
        }

        if (! PasswordFingerprint::matches($storedFingerprint, $currentPasswordHash)) {
            return $this->endSession($request, 'password changed');
        }

        return $next($request);
    }

    /**
     * Log the session out and send the client back to the login page.
     */
    private function endSession(IncomingRequest $request, string $reason): Response
    {
        Log::info('Ending session for user '.$request->session()->get('userdata.id').': '.$reason);

        app(AuthService::class)->logout();

        if ($request instanceof ApiRequest) {
            return new Response(json_encode(['error' => 'Session expired']), Response::HTTP_UNAUTHORIZED);
        }

        $loginUrl = BASE_URL.'/auth/login';

        if ($request instanceof HtmxRequest) {
            return new Response('', Response::HTTP_OK, ['HX-Redirect' => $loginUrl]);
        }

        return new RedirectResponse($loginUrl);
    }
}

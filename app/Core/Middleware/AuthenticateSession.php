<?php

namespace Leantime\Core\Middleware;

use Closure;
use Illuminate\Contracts\Session\Middleware\AuthenticatesSessions;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Auth\AccountStatus;
use Leantime\Core\Auth\Contracts\ChecksProjectAccess;
use Leantime\Core\Auth\PasswordFingerprint;
use Leantime\Core\Http\ApiRequest;
use Leantime\Core\Http\HtmxRequest;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ends web sessions whose user was deactivated or deleted, or whose password is no longer current.
 *
 * Login only accepts active accounts, so each request re-checks the account status from the same
 * user row: once an admin deactivates a user, their open sessions are logged out on the next
 * request instead of living on until they sign out (#4042).
 *
 * Login pins the session to a fingerprint of the user's password hash
 * ({@see PasswordFingerprint}). Each request compares it with the hash in the database; after a
 * password reset or change every other session of that user no longer matches and is logged
 * out. Web sessions without a fingerprint (created before it existed) must sign in again.
 *
 * Login also copies the user's role into the session, and switching projects copies the project
 * role. Both are refreshed here from the database, so a role an admin changes takes effect on the
 * user's next request instead of after their next login (#4090).
 *
 * Costs one primary-key lookup per authenticated web request, plus one indexed membership lookup
 * when a user below manager has a current project.
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
     * Handle an incoming request: let it through only when the session's user still exists, is
     * active and logged in with the password that is current.
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
        // header that is merely present proves nothing. Those credentials refuse deactivated
        // accounts where they are validated (Api::getAPIKeyUser, Auth::getUserByToken).
        if ($storedFingerprint === null && $request->attributes->get(self::TOKEN_AUTHENTICATED) === true) {
            return $next($request);
        }

        $user = $this->userRepo->getUser($userId);

        if (! is_array($user)) {
            return $this->endSession($request, 'user no longer exists');
        }

        if (! AccountStatus::isActive($user)) {
            return $this->endSession($request, 'user is deactivated');
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

        $this->syncRolesFromDatabase($session, $user);

        return $next($request);
    }

    /**
     * Bring the session's global role, client and current project role in line with the database
     * (#4090). The session is only written when a value actually changed.
     *
     * @param  array<string, mixed>  $user  The session user's zp_user row.
     */
    private function syncRolesFromDatabase(Session $session, array $user): void
    {
        $userId = (int) $user['id'];

        if (array_key_exists('role', $user)) {
            $currentRole = Roles::getRoleString($user['role']);

            if ($session->get('userdata.role') !== $currentRole) {
                Log::info('Role of user '.$userId.' changed to '.var_export($currentRole, true).', updating their session');
                $session->put('userdata.role', $currentRole);
            }
        }

        // The client decides which client-restricted projects the user can open.
        if (array_key_exists('clientId', $user) && (string) $session->get('userdata.clientId') !== (string) $user['clientId']) {
            $session->put('userdata.clientId', $user['clientId'] ?? '');
        }

        $this->syncCurrentProjectRole($session, $userId);
    }

    /**
     * Refresh `userdata.projectRole` (the role in the session's current project, set when the user
     * switched to it) from the membership row. Managers and above keep their global role in every
     * project, so their project role is never read and needs no lookup.
     *
     * Stores the same value {@see \Leantime\Domain\Projects\Services\Projects::changeCurrentSessionProject()}
     * does: the role name, or '' when the project role is inherited.
     */
    private function syncCurrentProjectRole(Session $session, int $userId): void
    {
        $currentProjectId = (int) $session->get('currentProject', 0);

        if ($currentProjectId <= 0) {
            return;
        }

        $globalRole = $session->get('userdata.role');

        if (is_string($globalRole) && Roles::isAtLeast($globalRole, Roles::$manager)) {
            return;
        }

        $storedProjectRole = app(ChecksProjectAccess::class)->getProjectRole($userId, $currentProjectId);
        // Stored as a role key, or as a role name on legacy rows; unknown values inherit ('').
        $projectRoleKey = Roles::getRoleKey($storedProjectRole);
        $currentProjectRole = $projectRoleKey === false ? '' : (string) Roles::getRoleString($projectRoleKey);

        if ((string) $session->get('userdata.projectRole', '') === $currentProjectRole) {
            return;
        }

        Log::info('Role of user '.$userId.' in project '.$currentProjectId.' changed, updating their session');
        $session->put('userdata.projectRole', $currentProjectRole);
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

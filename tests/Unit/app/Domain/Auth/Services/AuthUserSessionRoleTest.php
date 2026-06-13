<?php

namespace Unit\app\Domain\Auth\Services;

use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Auth\Services\AuthUser;

/**
 * Regression guard for the 3.9.x Bearer-auth role bug.
 *
 * AuthUser is the userdata builder on the Sanctum (Bearer) guard path: AccessToken::findToken ->
 * AuthUser::setUser -> setUserSession. It stored the RAW DB role int ("50") in session('userdata'),
 * while the permission engine's Auth::getRoleToCheck() validates the session role against
 * Roles::getRoles() (the role-NAME list). So "50" resolved to false and the engine denied every
 * #[RequiresPermission] @api method with -32001 — for every Bearer/Sanctum integrator, on any
 * server that exposes the Authorization header (production). CI missed it because its Apache hid
 * the header, routing Bearer through the fallback path (which builds userdata via
 * Api::setApiUserSession, and that one DOES convert the role).
 *
 * The fix: AuthUser::setUserSession must store the role NAME string, matching the other two
 * userdata builders. This asserts the resulting session role is engine-valid for every built-in
 * role — it FAILS on the raw-int bug and PASSES on the fix, independent of web server config.
 */
class AuthUserSessionRoleTest extends \Unit\TestCase
{
    private function userRow(int $role): array
    {
        return [
            'id' => 1,
            'firstname' => 'Test',
            'username' => 'test@leantime.io',
            'profileId' => 0,
            'clientId' => 0,
            'role' => $role,
            'settings' => '',
            'twoFAEnabled' => false,
            'twoFASecret' => '',
            'createdOn' => '2026-01-01 00:00:00',
            'modified' => '2026-01-01 00:00:00',
        ];
    }

    public function test_sanctum_guard_session_role_is_engine_valid_for_every_builtin_role(): void
    {
        // setUserSession touches no instance state, so a constructor-less instance avoids the DB.
        $authUser = (new \ReflectionClass(AuthUser::class))->newInstanceWithoutConstructor();
        $setUserSession = new \ReflectionMethod(AuthUser::class, 'setUserSession');
        $setUserSession->setAccessible(true);

        foreach (array_keys(Roles::getRoles()) as $roleInt) {
            session()->forget('userdata');

            $setUserSession->invoke($authUser, $this->userRow((int) $roleInt));

            $sessionRole = session('userdata.role');

            $this->assertContains(
                $sessionRole,
                Roles::getRoles(),
                "AuthUser stored an engine-invalid role for DB int $roleInt: ".var_export($sessionRole, true)
            );
            $this->assertNotFalse(
                Auth::getRoleToCheck(false),
                "getRoleToCheck() rejected the Sanctum-guard session role for DB int $roleInt"
            );
        }
    }
}

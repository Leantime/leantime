<?php

namespace Unit\app\Domain\Auth\Services;

use Leantime\Domain\Api\Services\Api;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\AuthUser;
use Leantime\Domain\Auth\Services\UserSessionBuilder;

/**
 * Guards the userdata-builder bug family (3.9.x Bearer regression + the twoFAVerified twin).
 *
 * Every auth path now builds session('userdata') through UserSessionBuilder, so a field can't
 * silently drift between paths. These tests pin the two invariants that historically broke:
 *  - role is ALWAYS the engine-valid NAME string (never the raw DB int), for every built-in role;
 *  - the two token paths (Sanctum/Bearer via AuthUser, x-api-key via Api) agree on role +
 *    twoFAVerified.
 */
class UserSessionBuilderTest extends \Unit\TestCase
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

    public function test_role_is_engine_valid_name_string_for_every_builtin_role(): void
    {
        foreach (array_keys(Roles::getRoles()) as $roleInt) {
            $userdata = UserSessionBuilder::build($this->userRow((int) $roleInt));

            $this->assertSame(Roles::getRoleString((int) $roleInt), $userdata['role']);
            $this->assertContains(
                $userdata['role'],
                Roles::getRoles(),
                "Factory produced an engine-invalid role for DB int $roleInt: ".var_export($userdata['role'], true)
            );
        }
    }

    public function test_flags_are_honored(): void
    {
        $tokenSession = UserSessionBuilder::build($this->userRow(50), isExternalAuth: true, twoFAVerified: true);
        $this->assertTrue($tokenSession['isExternalAuth']);
        $this->assertTrue($tokenSession['twoFAVerified']);

        $default = UserSessionBuilder::build($this->userRow(50));
        $this->assertFalse($default['isExternalAuth']);
        $this->assertFalse($default['twoFAVerified']);
    }

    public function test_both_token_paths_build_consistent_role_and_twofa(): void
    {
        // The Sanctum/Bearer path (AuthUser) and the x-api-key path (Api) are both token auth and
        // must produce the same role + twoFAVerified — these are the exact two fields that drifted.
        // setUserSession/setApiUserSession touch no instance state, so construct without the DB.
        $row = $this->userRow(50);

        session()->forget('userdata');
        $authUser = (new \ReflectionClass(AuthUser::class))->newInstanceWithoutConstructor();
        $m = new \ReflectionMethod(AuthUser::class, 'setUserSession');
        $m->setAccessible(true);
        $m->invoke($authUser, $row);
        $guardSession = session('userdata');

        session()->forget('userdata');
        $api = (new \ReflectionClass(Api::class))->newInstanceWithoutConstructor();
        $api->setApiUserSession($row, false);
        $apiKeySession = session('userdata');

        $this->assertSame($guardSession['role'], $apiKeySession['role'], 'token paths disagree on role');
        $this->assertSame($guardSession['twoFAVerified'], $apiKeySession['twoFAVerified'], 'token paths disagree on twoFAVerified');
        $this->assertContains($guardSession['role'], Roles::getRoles());
        $this->assertTrue($guardSession['twoFAVerified'], 'token sessions should be 2FA-verified');
    }
}

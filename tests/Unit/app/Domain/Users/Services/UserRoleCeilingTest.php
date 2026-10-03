<?php

namespace Unit\app\Domain\Users\Services;

use Illuminate\Support\Facades\RateLimiter;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\Avatarcreator;
use Leantime\Core\UI\Theme as ThemeCore;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
use Leantime\Domain\Files\Services\Files;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Leantime\Domain\Users\Services\Users as UserService;
use Unit\TestCase;

/**
 * Role ceiling on account management: holding users.create / users.edit lets a caller manage
 * accounts, but never hand out (or take over) a role above their own.
 */
class UserRoleCeilingTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private const CALLER_ID = 6161;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('invites:'.BASE_URL.':user:'.self::CALLER_ID);
        RateLimiter::clear('invites:'.BASE_URL.':tenant');
    }

    private function actAs(string $role, ?int $clientId = null): void
    {
        session(['userdata' => ['id' => self::CALLER_ID, 'role' => $role, 'clientId' => $clientId, 'name' => 'Caller', 'mail' => 'caller@example.com']]);
    }

    /**
     * A real Users service over a stubbed repository; the invitation email is stubbed out.
     *
     * @param  array<string, mixed>  $repoMethods
     */
    private function makeService(array $repoMethods): UserService
    {
        $service = $this->construct(UserService::class, [
            $this->make(UserRepository::class, $repoMethods),
            $this->make(LanguageCore::class),
            $this->make(ProjectRepository::class),
            $this->make(ClientRepository::class),
            $this->make(AuthService::class),
            $this->make(Files::class),
            $this->make(Avatarcreator::class),
            $this->make(SettingService::class),
            $this->make(ThemeCore::class),
            $this->make(ProjectService::class),
        ], [
            'sendUserInvite' => fn () => true,
        ]);

        $service->setPermissionService($this->make(PermissionService::class, [
            'currentUserCan' => fn () => true,
            'authorize' => fn () => null,
        ]));

        return $service;
    }

    public function test_manager_cannot_invite_an_owner(): void
    {
        $this->actAs('manager');
        $service = $this->makeService([
            'addUser' => function () {
                $this->fail('no account may be created');
            },
        ]);

        $this->expectException(AuthorizationException::class);

        $service->createUserInvite(['user' => 'boss@example.com', 'role' => 50]);
    }

    public function test_manager_cannot_add_an_owner(): void
    {
        $this->actAs('manager');
        $service = $this->makeService([
            'addUser' => function () {
                $this->fail('no account may be created');
            },
        ]);

        $this->expectException(AuthorizationException::class);

        $service->addUser(['username' => 'boss@example.com', 'role' => 50, 'password' => 'Known!Pass1', 'status' => 'a']);
    }

    public function test_manager_add_user_is_turned_into_an_invite_in_their_own_client(): void
    {
        $this->actAs('manager', clientId: 3);
        $stored = null;
        $service = $this->makeService([
            'addUser' => function (array $values) use (&$stored) {
                $stored = $values;

                return '501';
            },
        ]);

        $result = $service->addUser([
            'username' => 'dev@example.com',
            'role' => 20,
            'password' => 'Known!Pass1',
            'status' => 'a',
            'clientId' => 99,
        ]);

        $this->assertSame(501, $result);
        $this->assertSame('i', $stored['status'], 'a manager cannot create an active account');
        $this->assertNotSame('Known!Pass1', $stored['password'], 'a manager cannot choose the password');
        $this->assertNotEmpty($stored['pwReset']);
        $this->assertSame(3, $stored['clientId'], 'a manager can only create users in their own client');
    }

    public function test_admin_add_user_keeps_direct_creation(): void
    {
        $this->actAs('admin');
        $stored = null;
        $service = $this->makeService([
            'addUser' => function (array $values) use (&$stored) {
                $stored = $values;

                return '77';
            },
        ]);

        $this->assertSame(77, $service->addUser(['username' => 'a@example.com', 'role' => 40, 'password' => 'Known!Pass1', 'status' => 'a']));
        $this->assertSame('a', $stored['status']);
    }

    public function test_invite_new_user_reports_a_role_above_the_inviter(): void
    {
        $this->actAs('manager');
        $service = $this->makeService([
            'usernameExist' => fn () => false,
            'addUser' => function () {
                $this->fail('no account may be created');
            },
        ]);

        $this->assertSame('role_not_allowed', $service->inviteNewUser(['user' => 'boss@example.com', 'role' => '50'], null, true));
    }

    public function test_admin_cannot_edit_an_owner_account(): void
    {
        $this->actAs('admin');
        $service = $this->makeService([
            'getUser' => fn () => ['id' => 1, 'role' => 50],
            'editUser' => function () {
                $this->fail('an owner account must not be modified by an admin');
            },
        ]);

        $this->expectException(AuthorizationException::class);

        $service->editUser(['user' => 'owner@example.com', 'password' => 'new', 'role' => 50], 1);
    }

    public function test_admin_cannot_promote_to_owner(): void
    {
        $this->actAs('admin');
        $service = $this->makeService([
            'getUser' => fn () => ['id' => self::CALLER_ID, 'role' => 40],
            'editUser' => function () {
                $this->fail('the promotion must not be stored');
            },
            'patchUser' => function () {
                $this->fail('the promotion must not be stored');
            },
        ]);

        try {
            $service->editUser(['role' => 50], self::CALLER_ID);
            $this->fail('editUser must reject the promotion');
        } catch (AuthorizationException) {
        }

        $this->expectException(AuthorizationException::class);
        $service->patchUser(self::CALLER_ID, ['role' => '50']);
    }

    public function test_admin_can_still_edit_users_at_or_below_their_role(): void
    {
        $this->actAs('admin');
        $edited = false;
        $service = $this->makeService([
            'getUser' => fn () => ['id' => 12, 'role' => 20],
            'editUser' => function () use (&$edited) {
                $edited = true;

                return true;
            },
        ]);

        $this->assertTrue($service->editUser(['firstname' => 'Ann', 'role' => 40], 12));
        $this->assertTrue($edited);
    }

    public function test_invites_require_users_create_even_when_called_from_another_service(): void
    {
        $this->actAs('editor');
        $service = $this->makeService([
            'addUser' => function () {
                $this->fail('no account may be created without users.create');
            },
        ]);
        $service->setPermissionService($this->make(PermissionService::class, [
            'currentUserCan' => fn () => false,
            'authorize' => function (): void {
                throw new AuthorizationException;
            },
        ]));

        $this->expectException(AuthorizationException::class);

        $service->createUserInviteWithStatus(['user' => 'peer@example.com', 'role' => '20']);
    }

    public function test_ldap_import_cannot_grant_a_role_above_the_caller(): void
    {
        $this->actAs('admin');
        app()->instance(\Leantime\Domain\Ldap\Services\Ldap::class, $this->make(\Leantime\Domain\Ldap\Services\Ldap::class, [
            'upsertUsers' => function () {
                $this->fail('nothing may be imported');
            },
        ]));
        $service = $this->makeService(['getUserByEmail' => fn () => false]);

        $staged = [
            ['username' => 'boss', 'user' => 'boss@example.com', 'role' => 50],
        ];

        $this->expectException(AuthorizationException::class);

        $service->importSelectedLdapUsers($staged, ['boss']);
    }

    public function test_ldap_import_cannot_update_an_account_above_the_caller(): void
    {
        $this->actAs('admin');
        app()->instance(\Leantime\Domain\Ldap\Services\Ldap::class, $this->make(\Leantime\Domain\Ldap\Services\Ldap::class, [
            'upsertUsers' => function () {
                $this->fail('the owner account must not be modified');
            },
        ]));
        $service = $this->makeService(['getUserByEmail' => fn () => ['id' => 1, 'role' => 50]]);

        $staged = [
            ['username' => 'owner', 'user' => 'owner@example.com', 'role' => 5],
        ];

        $this->expectException(AuthorizationException::class);

        $service->importSelectedLdapUsers($staged, ['owner']);
    }

    public function test_ldap_import_within_the_ceiling_is_passed_on(): void
    {
        $this->actAs('admin');
        $imported = null;
        app()->instance(\Leantime\Domain\Ldap\Services\Ldap::class, $this->make(\Leantime\Domain\Ldap\Services\Ldap::class, [
            'upsertUsers' => function (array $users) use (&$imported) {
                $imported = $users;

                return true;
            },
        ]));
        $service = $this->makeService(['getUserByEmail' => fn () => ['id' => 8, 'role' => 20]]);

        $service->importSelectedLdapUsers([
            ['username' => 'dev', 'user' => 'dev@example.com', 'role' => 40],
        ], ['dev']);

        $this->assertSame('dev@example.com', $imported[0]['user'] ?? null);
    }

    public function test_api_source_cannot_be_set_through_user_creation(): void
    {
        foreach (['manager', 'owner'] as $role) {
            $this->actAs($role);
            RateLimiter::clear('invites:'.BASE_URL.':user:'.self::CALLER_ID);
            $stored = [];
            $service = $this->makeService([
                'addUser' => function (array $values) use (&$stored) {
                    $stored[] = $values['source'] ?? '';

                    return '9';
                },
            ]);

            $service->createUserInvite(['user' => 'svc@example.com', 'role' => 20, 'source' => 'api']);
            $service->addUser(['username' => 'svc2@example.com', 'role' => 20, 'password' => 'x', 'source' => 'API']);

            $this->assertSame(['', ''], $stored, $role.' must not create an api-source account');
        }
    }

    public function test_only_admins_may_set_a_non_api_source(): void
    {
        $this->actAs('admin');
        $stored = null;
        $service = $this->makeService([
            'addUser' => function (array $values) use (&$stored) {
                $stored = $values['source'];

                return '9';
            },
        ]);
        $service->addUser(['username' => 'csv@example.com', 'role' => 20, 'password' => 'x', 'source' => 'csvImport']);
        $this->assertSame('csvImport', $stored);

        $this->actAs('manager');
        RateLimiter::clear('invites:'.BASE_URL.':user:'.self::CALLER_ID);
        $service->addUser(['username' => 'csv2@example.com', 'role' => 20, 'password' => 'x', 'source' => 'ldap']);
        $this->assertSame('', $stored);
    }

    public function test_ldap_import_includes_the_first_selected_user(): void
    {
        $this->actAs('admin');
        $imported = null;
        app()->instance(\Leantime\Domain\Ldap\Services\Ldap::class, $this->make(\Leantime\Domain\Ldap\Services\Ldap::class, [
            'upsertUsers' => function (array $users) use (&$imported) {
                $imported = $users;

                return true;
            },
        ]));
        $service = $this->makeService(['getUserByEmail' => fn () => false]);

        $service->importSelectedLdapUsers([
            ['username' => 'first', 'user' => 'first@example.com', 'role' => 20],
            ['username' => 'second', 'user' => 'second@example.com', 'role' => 20],
            ['username' => 'unselected', 'user' => 'unselected@example.com', 'role' => 20],
        ], ['first', 'second']);

        $this->assertSame(['first@example.com', 'second@example.com'], array_column($imported, 'user'));
    }
}

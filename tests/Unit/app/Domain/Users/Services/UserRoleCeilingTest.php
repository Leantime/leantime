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
}

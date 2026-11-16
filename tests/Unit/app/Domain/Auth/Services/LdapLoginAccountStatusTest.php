<?php

namespace Unit\app\Domain\Auth\Services;

use Illuminate\Session\SessionManager;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Auth\Repositories\AccessTokenRepository;
use Leantime\Domain\Auth\Repositories\Auth as AuthRepository;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Ldap\Services\Ldap;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\TestCase;

/**
 * An LDAP login must not get past an account an admin deactivated in Leantime.
 *
 * The lookup only found active accounts, so a deactivated user looked new: the login tried to
 * create the account again and failed on the unique username with a database error.
 */
class LdapLoginAccountStatusTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('ldap')) {
            $this->markTestSkipped('The LDAP login path needs the ldap extension.');
        }

        session()->flush();

        app()->instance(Ldap::class, $this->make(Ldap::class, [
            'connect' => fn () => true,
            'bind' => fn () => true,
            'getEmail' => fn (string $username) => 'ada@example.com',
            'getSingleUser' => fn () => [
                'firstname' => 'Ada',
                'lastname' => 'Lovelace',
                'phone' => '',
                'user' => 'ada@example.com',
                'role' => 20,
                'department' => '',
                'jobTitle' => '',
                'jobLevel' => '',
            ],
        ]));
    }

    private function userRow(string $status): array
    {
        return [
            'id' => 7,
            'firstname' => 'Ada',
            'lastname' => '',
            'phone' => '',
            'username' => 'ada@example.com',
            'password' => '',
            'profileId' => 0,
            'clientId' => 0,
            'role' => 20,
            'settings' => '',
            'status' => $status,
            'source' => 'ldap',
            'department' => '',
            'jobTitle' => '',
            'jobLevel' => '',
            'twoFAEnabled' => false,
            'twoFASecret' => '',
            'createdOn' => '2026-01-01 00:00:00',
            'modified' => '2026-01-01 00:00:00',
        ];
    }

    /**
     * A user repository holding one row. getUserByEmail() filters like the real one: the default
     * status 'a' only finds active rows, '' finds the row whatever its status.
     */
    private function userRepoWith(array $row, array &$writes): UserRepository
    {
        return $this->make(UserRepository::class, [
            'getUserByEmail' => fn (string $email, string $status = 'a') => $status === 'a' && strtolower($row['status']) !== 'a' ? false : $row,
            'getUser' => fn () => $row,
            'addUser' => function (array $values) use (&$writes) {
                $writes[] = 'addUser';

                return '99';
            },
            'editUser' => function () use (&$writes) {
                $writes[] = 'editUser';

                return true;
            },
        ]);
    }

    private function authService(UserRepository $userRepo): AuthService
    {
        $config = new EnvironmentCore;
        $config->set('useLdap', true);

        return new AuthService(
            $config,
            $this->make(SessionManager::class),
            $this->make(LanguageCore::class),
            $this->make(SettingRepository::class),
            $this->make(AuthRepository::class, ['getUserByLogin' => fn () => false, 'updateUserSession' => fn () => true]),
            $userRepo,
            $this->make(AccessTokenRepository::class),
        );
    }

    public static function inactiveStatuses(): array
    {
        return [
            'deactivated' => [''],
            'deactivated (0)' => ['0'],
            'invited, not accepted yet' => ['i'],
        ];
    }

    /**
     * @dataProvider inactiveStatuses
     */
    public function test_ldap_login_refuses_an_account_that_is_not_active(string $status): void
    {
        $writes = [];
        $auth = $this->authService($this->userRepoWith($this->userRow($status), $writes));

        $this->assertFalse($auth->login('ada', 'directory-password'));
        $this->assertSame([], $writes, 'The account must be neither created again nor updated');
        $this->assertNull(session('userdata.id'));
    }

    public function test_ldap_login_still_signs_in_an_active_account(): void
    {
        $writes = [];
        $auth = $this->authService($this->userRepoWith($this->userRow('a'), $writes));

        $this->assertTrue($auth->login('ada', 'directory-password'));
        $this->assertSame(['editUser'], $writes);
        $this->assertSame(7, (int) session('userdata.id'));
    }
}

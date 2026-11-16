<?php

namespace Tests\Unit\app\Domain\Oidc\Services;

use Leantime\Core\Configuration\Environment;
use Leantime\Core\Language;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Oidc\Services\Oidc;
use Leantime\Domain\Users\Repositories\Users as UserRepository;

/**
 * An OIDC login must not get past an account an admin deactivated in Leantime.
 *
 * The lookup only found active accounts, so a deactivated user looked new: with user creation on,
 * the login inserted the account again, failed on the unique username and showed the database
 * error on the login page.
 */
class OidcLoginAccountStatusTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function oidc(array $row, array &$writes, int &$sessionsStarted): Oidc
    {
        $settings = [
            'oidcProviderUrl' => 'https://idp.example.com',
            'oidcClientId' => 'leantime-client',
            'oidcFieldEmail' => 'email',
            'oidcCreateUser' => true,
        ];

        $config = $this->make(Environment::class, [
            'get' => fn ($key, $default = null) => $settings[$key] ?? $default,
        ]);

        $userRepo = $this->make(UserRepository::class, [
            'getUserByEmail' => fn (string $email, string $status = 'a') => $status === 'a' && strtolower($row['status']) !== 'a' ? false : $row,
            'addUser' => function () use (&$writes) {
                $writes[] = 'addUser';

                return '99';
            },
            'editUser' => function () use (&$writes) {
                $writes[] = 'editUser';

                return true;
            },
        ]);

        $authService = $this->make(AuthService::class, [
            'setUserSession' => function () use (&$sessionsStarted) {
                $sessionsStarted++;
            },
            'learnTrustedAppUrl' => fn () => null,
            'dispatchLoginSucceeded' => fn () => null,
        ]);

        return new Oidc(
            $config,
            $this->make(Language::class, ['__' => fn (string $index) => $index]),
            $authService,
            $userRepo,
        );
    }

    private function login(Oidc $oidc, array $userInfo): mixed
    {
        $login = new \ReflectionMethod(Oidc::class, 'login');

        return $login->invoke($oidc, $userInfo);
    }

    private function row(string $status): array
    {
        return [
            'id' => 7,
            'username' => 'ada@example.com',
            'firstname' => 'Ada',
            'lastname' => '',
            'phone' => '',
            'jobTitle' => '',
            'jobLevel' => '',
            'department' => '',
            'role' => 20,
            'status' => $status,
        ];
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
    public function test_oidc_login_refuses_an_account_that_is_not_active(string $status): void
    {
        $writes = [];
        $sessionsStarted = 0;
        $oidc = $this->oidc($this->row($status), $writes, $sessionsStarted);

        try {
            $this->login($oidc, ['email' => 'ada@example.com', 'email_verified' => true]);
            $this->fail('An account that is not active must not sign in');
        } catch (\RuntimeException $e) {
            $this->assertSame('oidc.error.userInactive', $e->getMessage());
        }

        $this->assertSame([], $writes, 'The account must be neither created again nor updated');
        $this->assertSame(0, $sessionsStarted);
    }

    public function test_oidc_login_still_signs_in_an_active_account(): void
    {
        $writes = [];
        $sessionsStarted = 0;
        $oidc = $this->oidc($this->row('a'), $writes, $sessionsStarted);

        $this->login($oidc, ['email' => 'ada@example.com', 'email_verified' => true]);

        $this->assertSame(['editUser'], $writes);
        $this->assertSame(1, $sessionsStarted);
    }
}

<?php

namespace Unit\app\Domain\Auth\Services;

use Illuminate\Session\SessionManager;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Auth\Repositories\AccessTokenRepository;
use Leantime\Domain\Auth\Repositories\Auth as AuthRepository;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\TestCase;

/**
 * The trusted app URL is only learned from a completed sign-in: accounts with two-factor
 * authentication teach it after the second factor is verified, never from the password step.
 */
class TrustedAppUrlLearningTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<int, mixed> roles passed to learnFromAdminLogin() */
    private array $learnedRoles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->learnedRoles = [];
        app()->instance(TrustedAppUrl::class, $this->make(TrustedAppUrl::class, [
            'learnFromAdminLogin' => function (mixed $role) {
                $this->learnedRoles[] = $role;
            },
        ]));
    }

    private function makeService(): AuthService
    {
        return new AuthService(
            $this->make(EnvironmentCore::class),
            $this->make(SessionManager::class),
            $this->make(LanguageCore::class),
            $this->make(SettingRepository::class),
            $this->make(AuthRepository::class),
            $this->make(UserRepository::class),
            $this->make(AccessTokenRepository::class),
        );
    }

    public function test_login_without_2fa_learns_the_url(): void
    {
        $this->makeService()->learnTrustedAppUrl(['id' => 1, 'role' => 50, 'twoFAEnabled' => 0]);

        $this->assertSame([50], $this->learnedRoles);
    }

    public function test_password_step_of_a_2fa_account_does_not_learn_the_url(): void
    {
        $this->makeService()->learnTrustedAppUrl(['id' => 1, 'role' => 50, 'twoFAEnabled' => 1]);

        $this->assertSame([], $this->learnedRoles);
    }

    public function test_url_is_learned_once_the_second_factor_is_verified(): void
    {
        $service = $this->makeService();

        session(['userdata' => ['id' => 1, 'role' => 'owner', 'twoFAEnabled' => 1, 'twoFAVerified' => false]]);
        $service->learnTrustedAppUrlAfter2FA();
        $this->assertSame([], $this->learnedRoles, 'an unverified session never teaches the URL');

        session(['userdata.twoFAVerified' => true]);
        $service->learnTrustedAppUrlAfter2FA();
        $this->assertSame(['owner'], $this->learnedRoles);
    }
}

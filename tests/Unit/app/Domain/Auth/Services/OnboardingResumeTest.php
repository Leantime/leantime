<?php

namespace Unit\app\Domain\Auth\Services;

use Illuminate\Session\SessionManager;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\UI\Theme;
use Leantime\Domain\Auth\Repositories\AccessTokenRepository;
use Leantime\Domain\Auth\Repositories\Auth as AuthRepository;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Auth\Services\Onboarding as OnboardingService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Leantime\Domain\Users\Services\Users as UserService;
use Unit\TestCase;

/**
 * An onboarding left after step 1 (password chosen, account still pending) or finished in
 * another browser session must not dead-end: the login page resumes it, and the last step
 * never runs without the password it needs to log the person in.
 */
class OnboardingResumeTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private const PASSWORD = 'Correct-Horse-9';

    protected function setUp(): void
    {
        parent::setUp();

        session()->forget('tempPassword');
    }

    private function pendingUser(array $overrides = []): array
    {
        return array_merge([
            'id' => 1,
            'username' => 'owner@example.com',
            'status' => 'i',
            'password' => password_hash(self::PASSWORD, PASSWORD_DEFAULT),
            'pwReset' => 'invite-token-123',
        ], $overrides);
    }

    private function makeAuthService(array|false $pendingUser): AuthService
    {
        $userRepo = $this->make(UserRepository::class, [
            'getUserByEmail' => function (string $email, string $status = 'a') use ($pendingUser) {
                return $status === 'i' ? $pendingUser : false;
            },
        ]);

        return new AuthService(
            $this->make(EnvironmentCore::class),
            $this->make(SessionManager::class),
            $this->make(LanguageCore::class),
            $this->make(SettingRepository::class),
            $this->make(AuthRepository::class),
            $userRepo,
            $this->make(AccessTokenRepository::class),
        );
    }

    private function makeOnboarding(AuthService $authService, ?UserService $userService = null): OnboardingService
    {
        return new OnboardingService(
            $authService,
            $userService ?? $this->make(UserService::class),
            $this->make(SettingService::class),
            $this->make(Theme::class),
            $this->make(LanguageCore::class),
        );
    }

    public function test_pending_invite_token_is_returned_for_the_chosen_password(): void
    {
        $auth = $this->makeAuthService($this->pendingUser());

        $this->assertSame('invite-token-123', $auth->getPendingInviteToken('owner@example.com', self::PASSWORD));
    }

    public function test_pending_invite_token_needs_the_right_password(): void
    {
        $auth = $this->makeAuthService($this->pendingUser());

        $this->assertNull($auth->getPendingInviteToken('owner@example.com', 'wrong-password'));
        $this->assertNull($auth->getPendingInviteToken('owner@example.com', ''));
    }

    public function test_no_token_before_a_password_was_chosen_or_without_an_invite(): void
    {
        $this->assertNull($this->makeAuthService($this->pendingUser(['password' => '']))
            ->getPendingInviteToken('owner@example.com', ''));
        $this->assertNull($this->makeAuthService($this->pendingUser(['pwReset' => null]))
            ->getPendingInviteToken('owner@example.com', self::PASSWORD));
        $this->assertNull($this->makeAuthService(false)
            ->getPendingInviteToken('nobody@example.com', self::PASSWORD));
    }

    public function test_resume_keeps_the_password_for_the_last_step(): void
    {
        $onboarding = $this->makeOnboarding($this->makeAuthService($this->pendingUser()));

        $this->assertSame('invite-token-123', $onboarding->resumeOnboarding('owner@example.com', self::PASSWORD));
        $this->assertSame(self::PASSWORD, session('tempPassword'));
        $this->assertTrue($onboarding->canCompleteOnboarding($this->pendingUser()));
    }

    public function test_failed_resume_stores_nothing(): void
    {
        $onboarding = $this->makeOnboarding($this->makeAuthService($this->pendingUser()));

        $this->assertNull($onboarding->resumeOnboarding('owner@example.com', 'wrong-password'));
        $this->assertNull(session('tempPassword'));
    }

    public function test_last_step_in_a_new_session_changes_nothing_instead_of_failing(): void
    {
        $userService = $this->make(UserService::class, [
            'editUser' => function () {
                $this->fail('the account must stay pending when nobody can be logged in');
            },
        ]);
        $onboarding = $this->makeOnboarding($this->makeAuthService($this->pendingUser()), $userService);

        $this->assertFalse($onboarding->canCompleteOnboarding($this->pendingUser()));
        $this->assertFalse($onboarding->completeOnboarding($this->pendingUser()));
    }

    public function test_last_step_needs_the_password_of_this_account(): void
    {
        session(['tempPassword' => 'someone-elses-password']);
        $onboarding = $this->makeOnboarding($this->makeAuthService($this->pendingUser()));

        $this->assertFalse($onboarding->canCompleteOnboarding($this->pendingUser()));
    }
}

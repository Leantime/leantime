<?php

namespace Unit\app\Domain\Auth\Services;

use Leantime\Core\Language as LanguageCore;
use Leantime\Core\UI\Theme;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Auth\Services\Onboarding as OnboardingService;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Users\Services\Users as UserService;
use Unit\TestCase;

/**
 * Unit tests for the onboarding/invite business logic extracted from the
 * UserInvite controller during the thin-controller refactor.
 */
class OnboardingServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Builds a real Onboarding service with mocked dependencies.
     */
    private function makeService(
        ?UserService $userService = null,
        ?SettingService $settingService = null,
        ?Theme $theme = null,
        ?AuthService $authService = null
    ): OnboardingService {
        return new OnboardingService(
            $authService ?? $this->make(AuthService::class),
            $userService ?? $this->make(UserService::class),
            $settingService ?? $this->make(SettingService::class),
            $theme ?? $this->make(Theme::class),
            $this->make(LanguageCore::class),
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        session()->forget('tempPassword');
    }

    public function test_save_account_rejects_weak_password(): void
    {
        $userService = $this->make(UserService::class, [
            'checkPasswordStrength' => fn () => false,
            'editUser' => function () {
                $this->fail('editUser must not be called for a weak password');
            },
        ]);

        $result = $this->makeService($userService)->saveAccount(
            ['id' => 5, 'username' => 'jane@example.com'],
            'Jane Doe',
            'Engineer',
            'weak'
        );

        $this->assertSame('weak', $result);
        $this->assertNull(session('tempPassword'));
    }

    public function test_save_account_splits_name_and_persists(): void
    {
        $captured = null;
        $userService = $this->make(UserService::class, [
            'checkPasswordStrength' => fn () => true,
            'editUser' => function ($values, $id) use (&$captured) {
                $captured = ['values' => $values, 'id' => $id];

                return true;
            },
        ]);

        $result = $this->makeService($userService)->saveAccount(
            ['id' => 5, 'username' => 'jane@example.com'],
            'Jane Doe',
            'Engineer',
            'StrongPass1!'
        );

        $this->assertSame('saved', $result);
        $this->assertSame(5, $captured['id']);
        $this->assertSame('Jane', $captured['values']['firstname']);
        $this->assertSame('Doe', $captured['values']['lastname']);
        $this->assertSame('Engineer', $captured['values']['jobTitle']);
        $this->assertSame('i', $captured['values']['status']);
        $this->assertSame('jane@example.com', $captured['values']['user']);
        $this->assertSame('StrongPass1!', $captured['values']['password']);
        // Temp password is stored so the user can be auto-logged-in later.
        $this->assertSame('StrongPass1!', session('tempPassword'));
    }

    public function test_save_account_handles_single_word_name(): void
    {
        $captured = null;
        $userService = $this->make(UserService::class, [
            'checkPasswordStrength' => fn () => true,
            'editUser' => function ($values) use (&$captured) {
                $captured = $values;

                return true;
            },
        ]);

        $this->makeService($userService)->saveAccount(
            ['id' => 9, 'username' => 'mono@example.com'],
            'Cher',
            '',
            'StrongPass1!'
        );

        $this->assertSame('Cher', $captured['firstname']);
        $this->assertSame('', $captured['lastname']);
    }

    public function test_save_account_reports_error_when_persist_fails(): void
    {
        $userService = $this->make(UserService::class, [
            'checkPasswordStrength' => fn () => true,
            'editUser' => fn () => false,
        ]);

        $result = $this->makeService($userService)->saveAccount(
            ['id' => 5, 'username' => 'jane@example.com'],
            'Jane Doe',
            'Engineer',
            'StrongPass1!'
        );

        $this->assertSame('error', $result);
    }

    public function test_get_invite_settings_applies_defaults_when_unset(): void
    {
        $settingService = $this->make(SettingService::class, [
            'getSetting' => fn () => false,
        ]);

        $theme = $this->make(Theme::class, [
            'getAvailableColorSchemes' => fn () => ['companyColors'],
            'getAvailableFonts' => fn () => ['Roboto'],
            'getAll' => fn () => ['default'],
        ]);

        $settings = $this->makeService(null, $settingService, $theme)
            ->getInviteSettings(['id' => 7]);

        $this->assertSame('default', $settings['userTheme']);
        $this->assertSame('light', $settings['userColorMode']);
        $this->assertSame('companyColors', $settings['userColorScheme']);
        $this->assertSame('Roboto', $settings['themeFont']);
        $this->assertSame($this->makeService()->getDefaultWorkdays(), $settings['workdays']);
        $this->assertSame($this->makeService()->getDefaultDaySchedule(), $settings['daySchedule']);
        $this->assertArrayHasKey('dayHourOptions', $settings);
        $this->assertArrayHasKey('dateTimeValues', $settings);
    }
}

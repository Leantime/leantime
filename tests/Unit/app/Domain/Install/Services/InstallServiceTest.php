<?php

namespace Unit\app\Domain\Install\Services;

use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\Install\Repositories\Install as InstallRepository;
use Leantime\Domain\Install\Services\Install as InstallService;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Users\Events\UserCreated;
use Unit\TestCase;

/**
 * Unit tests for the Install service helpers extracted during the
 * thin-controller refactor (validateInstallInput, needsUpdate), and for
 * resuming an owner setup that a lost post-install redirect left unfinished (#3968).
 */
class InstallServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Builds a real Install service, allowing each dependency to be
     * overridden with a stub.
     */
    private function makeService(
        ?AppSettings $appSettings = null,
        ?InstallRepository $installRepo = null,
        ?SettingService $settingService = null,
    ): InstallService {
        return new InstallService(
            $appSettings ?? $this->make(AppSettings::class),
            $installRepo ?? $this->make(InstallRepository::class),
            $settingService ?? $this->make(SettingService::class),
        );
    }

    public function test_validate_install_input_passes_for_complete_values(): void
    {
        $service = $this->makeService();

        $service->validateInstallInput([
            'email' => 'admin@example.com',
            'firstname' => 'Ada',
            'lastname' => 'Lovelace',
            'company' => 'Analytical Engines',
        ]);

        // No exception thrown means success.
        $this->assertTrue(true);
    }

    public function test_validate_install_input_throws_email_key_first(): void
    {
        $service = $this->makeService();

        try {
            $service->validateInstallInput([
                'email' => '',
                'firstname' => '',
                'lastname' => '',
                'company' => '',
            ]);
            $this->fail('Expected InvalidArgumentException was not thrown');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame('notification.enter_email', $e->getMessage());
        }
    }

    public function test_validate_install_input_throws_firstname_key_when_only_email_present(): void
    {
        $service = $this->makeService();

        try {
            $service->validateInstallInput([
                'email' => 'admin@example.com',
                'firstname' => '',
                'lastname' => '',
                'company' => '',
            ]);
            $this->fail('Expected InvalidArgumentException was not thrown');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame('notification.enter_firstname', $e->getMessage());
        }
    }

    public function test_validate_install_input_throws_lastname_key_when_company_also_missing(): void
    {
        $service = $this->makeService();

        try {
            $service->validateInstallInput([
                'email' => 'admin@example.com',
                'firstname' => 'Ada',
                'lastname' => '',
                'company' => '',
            ]);
            $this->fail('Expected InvalidArgumentException was not thrown');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame('notification.enter_lastname', $e->getMessage());
        }
    }

    public function test_validate_install_input_throws_company_key_last(): void
    {
        $service = $this->makeService();

        try {
            $service->validateInstallInput([
                'email' => 'admin@example.com',
                'firstname' => 'Ada',
                'lastname' => 'Lovelace',
                'company' => '',
            ]);
            $this->fail('Expected InvalidArgumentException was not thrown');
        } catch (\InvalidArgumentException $e) {
            $this->assertSame('notification.enter_company', $e->getMessage());
        }
    }

    public function test_needs_update_is_true_when_versions_differ(): void
    {
        $appSettings = $this->make(AppSettings::class);
        $appSettings->dbVersion = '3.5.1';

        $settingService = $this->make(SettingService::class, [
            'getSetting' => fn () => '3.5.0',
        ]);

        $needsUpdate = $this->makeService(
            appSettings: $appSettings,
            settingService: $settingService,
        )->needsUpdate();

        $this->assertTrue($needsUpdate);
    }

    public function test_needs_update_is_false_when_versions_match(): void
    {
        $appSettings = $this->make(AppSettings::class);
        $appSettings->dbVersion = '3.5.1';

        $settingService = $this->make(SettingService::class, [
            'getSetting' => fn () => '3.5.1',
        ]);

        $needsUpdate = $this->makeService(
            appSettings: $appSettings,
            settingService: $settingService,
        )->needsUpdate();

        $this->assertFalse($needsUpdate);
    }

    /**
     * The owner row exactly as the installer writes it (SchemaBuilder::insertInitialData).
     *
     * @return array<string, mixed>
     */
    private function installerOwnerRow(array $overrides = []): array
    {
        return array_merge([
            'id' => 1,
            'role' => '50',
            'status' => 'i',
            'lastlogin' => null,
            'pwReset' => 'installer-token',
            'password' => '',
        ], $overrides);
    }

    public function test_owner_setup_is_pending_for_the_never_set_up_installer_owner(): void
    {
        $this->assertTrue(InstallService::isOwnerSetupPending([$this->installerOwnerRow()]));
        $this->assertTrue(InstallService::isOwnerSetupPending([$this->installerOwnerRow(['status' => 'I'])]));
    }

    public function test_owner_setup_is_not_pending_once_the_instance_is_in_use(): void
    {
        // No users at all (nothing to resume).
        $this->assertFalse(InstallService::isOwnerSetupPending([]));

        // A second account exists, e.g. an SSO user signed in while the owner stayed pending.
        $this->assertFalse(InstallService::isOwnerSetupPending([
            $this->installerOwnerRow(),
            ['id' => 2, 'role' => '20', 'status' => 'a', 'lastlogin' => '2026-10-01 10:00:00', 'pwReset' => null],
        ]));

        // The owner finished onboarding.
        $this->assertFalse(InstallService::isOwnerSetupPending([$this->installerOwnerRow(['status' => 'A'])]));

        // The owner signed in at some point (e.g. was deactivated later).
        $this->assertFalse(InstallService::isOwnerSetupPending([$this->installerOwnerRow(['lastlogin' => '2026-10-01 10:00:00'])]));

        // The only account is not an owner.
        $this->assertFalse(InstallService::isOwnerSetupPending([$this->installerOwnerRow(['role' => '40'])]));
    }

    public function test_owner_setup_is_not_resumed_for_a_later_owner(): void
    {
        // A replacement owner invited before the installer's account was deleted: their invite link is private.
        $this->assertFalse(InstallService::isOwnerSetupPending([$this->installerOwnerRow(['id' => 7])]));
    }

    public function test_owner_setup_is_not_resumed_once_onboarding_stored_a_password(): void
    {
        // Onboarding step 1 saved the password but the owner hasn't finished yet: resuming from the
        // public /install page would let anyone redo step 1 and overwrite it.
        $this->assertFalse(InstallService::isOwnerSetupPending([$this->installerOwnerRow(['password' => '$2y$10$hashedPassword'])]));
    }

    public function test_resume_owner_setup_returns_the_installer_token_without_changing_it(): void
    {
        $storedTokens = [];
        $installRepo = $this->make(InstallRepository::class, [
            'getFirstUsers' => fn (): array => [$this->installerOwnerRow()],
            'setInviteToken' => function (int $userId, string $token) use (&$storedTokens): bool {
                $storedTokens[$userId] = $token;

                return true;
            },
        ]);

        $token = $this->makeService(installRepo: $installRepo)->resumeOwnerSetup();

        $this->assertSame('installer-token', $token);
        $this->assertSame([], $storedTokens, 'A link from the original install keeps working');
    }

    public function test_resume_owner_setup_issues_a_token_when_the_owner_has_none(): void
    {
        $storedTokens = [];
        $installRepo = $this->make(InstallRepository::class, [
            'getFirstUsers' => fn (): array => [$this->installerOwnerRow(['pwReset' => null])],
            'setInviteToken' => function (int $userId, string $token) use (&$storedTokens): bool {
                $storedTokens[$userId] = $token;

                return true;
            },
        ]);

        $token = $this->makeService(installRepo: $installRepo)->resumeOwnerSetup();

        $this->assertIsString($token);
        $this->assertSame(32, strlen($token));
        $this->assertSame([1 => $token], $storedTokens);
    }

    public function test_resume_owner_setup_hands_out_nothing_for_an_instance_in_use(): void
    {
        $installRepo = $this->make(InstallRepository::class, [
            'getFirstUsers' => fn (): array => [$this->installerOwnerRow(['status' => 'a', 'lastlogin' => '2026-10-01 10:00:00', 'pwReset' => null])],
            'setInviteToken' => function (): bool {
                $this->fail('No token may be issued for an active owner');
            },
        ]);

        $this->assertNull($this->makeService(installRepo: $installRepo)->resumeOwnerSetup());
    }

    public function test_resume_owner_setup_fails_closed_when_the_users_cannot_be_read(): void
    {
        $installRepo = $this->make(InstallRepository::class, [
            'getFirstUsers' => function (): array {
                throw new \RuntimeException('database unavailable');
            },
        ]);

        $this->assertNull($this->makeService(installRepo: $installRepo)->resumeOwnerSetup());
    }

    public function test_run_install_reports_success_even_when_a_user_created_listener_fails(): void
    {
        $reflection = new \ReflectionClass(EventDispatcher::class);
        $registryBefore = $reflection->getProperty('eventRegistry')->getValue();
        $patternCacheBefore = $reflection->getProperty('patternMatchCache')->getValue();

        try {
            EventDispatcher::add_event_listener(UserCreated::class, function (): void {
                throw new \RuntimeException('analytics endpoint unreachable');
            });

            $installRepo = $this->make(InstallRepository::class, ['setupDB' => fn (): bool => true]);

            $this->assertTrue($this->makeService(installRepo: $installRepo)->runInstall([
                'email' => 'admin@example.com',
                'firstname' => 'Ada',
                'lastname' => 'Lovelace',
                'company' => 'Analytical Engines',
            ]));
        } finally {
            $reflection->getProperty('eventRegistry')->setValue(null, $registryBefore);
            $reflection->getProperty('patternMatchCache')->setValue(null, $patternCacheBefore);
        }
    }

    public function test_run_install_reports_failure_when_the_database_setup_fails(): void
    {
        $installRepo = $this->make(InstallRepository::class, ['setupDB' => fn (): bool => false]);

        $this->assertFalse($this->makeService(installRepo: $installRepo)->runInstall([
            'email' => 'admin@example.com',
            'firstname' => 'Ada',
            'lastname' => 'Lovelace',
            'company' => 'Analytical Engines',
        ]));
    }
}

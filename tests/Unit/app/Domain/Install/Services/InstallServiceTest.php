<?php

namespace Unit\app\Domain\Install\Services;

use Leantime\Core\Configuration\AppSettings;
use Leantime\Domain\Install\Repositories\Install as InstallRepository;
use Leantime\Domain\Install\Services\Install as InstallService;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Unit\TestCase;

/**
 * Unit tests for the Install service helpers extracted during the
 * thin-controller refactor (validateInstallInput, needsUpdate).
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
}

<?php

namespace Unit\app\Core\Support;

use Leantime\Core\Support\Installation;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;

/**
 * Console runs never pass through the Installed middleware, so the installed check must not
 * depend on the session flag there; it asks the settings repository instead.
 */
class InstallationTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    public function test_console_run_is_installed_when_repository_says_so_without_session_flag(): void
    {
        session()->forget('isInstalled');
        app()->instance(SettingRepository::class, $this->makeEmpty(SettingRepository::class, ['checkIfInstalled' => true]));

        $this->assertTrue(Installation::isInstalled());
    }

    public function test_console_run_is_not_installed_when_repository_says_so(): void
    {
        session(['isInstalled' => true]);
        app()->instance(SettingRepository::class, $this->makeEmpty(SettingRepository::class, ['checkIfInstalled' => false]));

        $this->assertFalse(Installation::isInstalled());
    }

    public function test_console_run_without_usable_database_counts_as_not_installed(): void
    {
        app()->instance(SettingRepository::class, $this->makeEmpty(SettingRepository::class, [
            'checkIfInstalled' => fn () => throw new \TypeError('no database configuration'),
        ]));

        $this->assertFalse(Installation::isInstalled());
    }
}

<?php

namespace Unit\app\Core\Support;

use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Support\Installation;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Unit\TestCase;

/**
 * The scheduler skips its jobs while migrations are pending: the console never runs them, so
 * on a multi-tenant host every tenant not visited since a release ran its jobs against an old
 * schema every minute (e.g. RecurringTasks selecting the new outcomeImpact column).
 */
class InstallationDatabaseBehindCodeTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function recordedVersion(mixed $version): void
    {
        $this->app->instance(SettingRepository::class, $this->make(SettingRepository::class, [
            'getSetting' => fn (string $key) => $key === 'db-version' ? $version : false,
        ]));
    }

    public function test_an_older_recorded_version_is_behind(): void
    {
        $this->recordedVersion('3.5.20');

        $this->assertTrue(Installation::isDatabaseBehindCode());
    }

    public function test_the_current_version_is_not_behind(): void
    {
        $this->recordedVersion((new AppSettings)->dbVersion);

        $this->assertFalse(Installation::isDatabaseBehindCode());
    }

    public function test_versions_compare_numerically_not_as_strings(): void
    {
        $this->recordedVersion('3.5.9');

        $this->assertTrue(Installation::isDatabaseBehindCode(), '3.5.9 is older than 3.5.2x');
    }

    public function test_a_missing_version_fails_closed(): void
    {
        $this->recordedVersion(false);

        $this->assertTrue(Installation::isDatabaseBehindCode());
    }
}

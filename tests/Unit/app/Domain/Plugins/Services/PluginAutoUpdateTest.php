<?php

namespace Unit\app\Domain\Plugins\Services;

use Illuminate\Support\Facades\Http;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Plugins\PluginCompatibility;
use Leantime\Domain\Plugins\Models\InstalledPlugin;
use Leantime\Domain\Plugins\Repositories\Plugins as PluginRepository;
use Leantime\Domain\Plugins\Services\Plugins;
use Leantime\Domain\Setting\Services\Setting as SettingsService;
use Unit\TestCase;

/**
 * Automatic plugin updates are off by default; when on, the daily job installs minor and patch
 * releases only. The update check asks the marketplace in one batch request when it can.
 */
class PluginAutoUpdateTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<int, array{0: int, 1: string}> plugin id and the exact version asked for */
    private array $updatedIds = [];

    private function plugin(int $id, string $folder, string $version): InstalledPlugin
    {
        $plugin = new InstalledPlugin;
        $plugin->id = $id;
        $plugin->name = 'leantime/'.strtolower($folder);
        $plugin->foldername = $folder;
        $plugin->format = 'phar';
        $plugin->version = $version;
        $plugin->enabled = true;
        $plugin->license = 'key';

        return $plugin;
    }

    /**
     * @param  array<string, string>  $available  What checkForUpdates() finds.
     */
    private function service(array $available, bool $autoUpdate, ?callable $updatePlugin = null): Plugins
    {
        return $this->make(Plugins::class, [
            'pluginRepository' => $this->makeEmpty(PluginRepository::class, [
                'getAllPlugins' => fn () => [$this->plugin(1, 'Notes', '1.4.2'), $this->plugin(2, 'PgmPro', '1.3.1')],
            ]),
            'settingsService' => $this->makeEmpty(SettingsService::class, [
                'getSetting' => fn ($key, $default = false) => $key === 'plugins.autoUpdate' ? ($autoUpdate ? '1' : '0') : $default,
            ]),
            'checkForUpdates' => fn () => ['updates' => $available, 'failed' => []],
            'updatePluginTo' => $updatePlugin ?? function (InstalledPlugin $plugin, string $version) {
                $this->updatedIds[] = [$plugin->id, $version];
            },
        ]);
    }

    public function test_minor_and_patch_versions_are_recognised(): void
    {
        $this->assertTrue(Plugins::isMinorOrPatchUpdate('1.4.2', '1.5.0'));
        $this->assertTrue(Plugins::isMinorOrPatchUpdate('v1.4.2', '1.4.3'));
        $this->assertFalse(Plugins::isMinorOrPatchUpdate('1.4.2', '2.0.0'));
        $this->assertFalse(Plugins::isMinorOrPatchUpdate('1.4.2', '1.4.2'));
    }

    public function test_nothing_is_installed_while_automatic_updates_are_off(): void
    {
        $result = $this->service(['Notes' => '1.5.0'], autoUpdate: false)->runScheduledUpdates();

        $this->assertSame(['Notes' => '1.5.0'], $result['available']);
        $this->assertSame([], $result['updated']);
        $this->assertSame([], $this->updatedIds);
    }

    public function test_minor_updates_install_and_major_ones_wait(): void
    {
        $result = $this->service(['Notes' => '1.5.0', 'PgmPro' => '2.0.0'], autoUpdate: true)->runScheduledUpdates();

        $this->assertSame(['Notes' => '1.5.0'], $result['updated']);
        $this->assertSame(['PgmPro' => '2.0.0'], $result['waiting']);
        // Pinned to the version that passed the check.
        $this->assertSame([[1, '1.5.0']], $this->updatedIds);
    }

    public function test_a_failed_automatic_update_is_reported_not_thrown(): void
    {
        $result = $this->service(['Notes' => '1.5.0'], autoUpdate: true, updatePlugin: fn () => throw new \RuntimeException('rolled back: broken release'))
            ->runScheduledUpdates();

        $this->assertSame([], $result['updated']);
        $this->assertSame(['Notes' => 'rolled back: broken release'], $result['failed']);
    }

    public function test_update_check_uses_the_batch_endpoint_when_the_marketplace_has_it(): void
    {
        Http::fake([
            '*/ltmp-api/latest*' => Http::response(['plugins' => [
                'leantime_notes' => [['version_number' => '1.5.0', 'supported_version_from' => '3.0', 'supported_version_to' => 'latest']],
            ]]),
            '*/ltmp-api/details/*' => Http::response([], 500),
        ]);

        $appSettings = new AppSettings;
        $appSettings->appVersion = '3.12.0';
        $plugins = $this->make(Plugins::class, [
            'marketplaceUrl' => 'https://marketplace.test',
            'pluginRepository' => $this->makeEmpty(PluginRepository::class, ['getAllPlugins' => fn () => [$this->plugin(1, 'Notes', '1.4.2')]]),
            'appSettings' => $appSettings,
            'compatibility' => new PluginCompatibility($appSettings),
            'settingsService' => $this->makeEmpty(SettingsService::class, ['getSetting' => fn ($key, $default = false) => str_starts_with($key, 'plugins.updateNotified.') ? '1.5.0' : $default]),
            'clearCache' => null,
        ]);

        $this->assertSame(['updates' => ['Notes' => '1.5.0'], 'failed' => []], $plugins->checkForUpdates());
        Http::assertSent(fn ($request) => str_contains($request->url(), '/ltmp-api/latest') && str_contains($request->url(), 'identifiers=leantime_notes'));
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/ltmp-api/details/'));
    }

    public function test_update_check_falls_back_to_per_plugin_details_without_the_batch_endpoint(): void
    {
        Http::fake([
            '*/ltmp-api/latest*' => Http::response('Not found', 404),
            '*/ltmp-api/details/*' => Http::response(['name' => 'Notes', 'compatibility' => [
                ['version_number' => '1.5.0', 'supported_version_from' => '3.0', 'supported_version_to' => 'latest'],
            ]]),
        ]);

        $appSettings = new AppSettings;
        $appSettings->appVersion = '3.12.0';
        $plugins = $this->make(Plugins::class, [
            'marketplaceUrl' => 'https://marketplace.test',
            'pluginRepository' => $this->makeEmpty(PluginRepository::class, ['getAllPlugins' => fn () => [$this->plugin(1, 'Notes', '1.4.2')]]),
            'appSettings' => $appSettings,
            'compatibility' => new PluginCompatibility($appSettings),
            'settingsService' => $this->makeEmpty(SettingsService::class, ['getSetting' => fn ($key, $default = false) => str_starts_with($key, 'plugins.updateNotified.') ? '1.5.0' : $default]),
            'clearCache' => null,
        ]);

        $this->assertSame(['updates' => ['Notes' => '1.5.0'], 'failed' => []], $plugins->checkForUpdates());
        Http::assertSent(fn ($request) => str_contains($request->url(), '/ltmp-api/details/leantime_notes'));
    }

    public function test_a_plugin_left_out_of_the_batch_answer_is_asked_about_on_its_own(): void
    {
        Http::fake([
            '*/ltmp-api/latest*' => Http::response(['plugins' => ['leantime_other' => []]]),
            '*/ltmp-api/details/leantime_notes*' => Http::response(['name' => 'Notes', 'compatibility' => [
                ['version_number' => '1.5.0', 'supported_version_from' => '3.0', 'supported_version_to' => 'latest'],
            ]]),
        ]);

        $appSettings = new AppSettings;
        $appSettings->appVersion = '3.12.0';
        $plugins = $this->make(Plugins::class, [
            'marketplaceUrl' => 'https://marketplace.test',
            'pluginRepository' => $this->makeEmpty(PluginRepository::class, ['getAllPlugins' => fn () => [$this->plugin(1, 'Notes', '1.4.2')]]),
            'appSettings' => $appSettings,
            'compatibility' => new PluginCompatibility($appSettings),
            'settingsService' => $this->makeEmpty(SettingsService::class, ['getSetting' => fn ($key, $default = false) => str_starts_with($key, 'plugins.updateNotified.') ? '1.5.0' : $default]),
            'clearCache' => null,
        ]);

        $this->assertSame(['updates' => ['Notes' => '1.5.0'], 'failed' => []], $plugins->checkForUpdates());
        Http::assertSent(fn ($request) => str_contains($request->url(), '/ltmp-api/details/leantime_notes'));
    }
}

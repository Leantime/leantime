<?php

namespace Unit\app\Domain\Plugins\Services;

use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Plugins\PluginCompatibility;
use Leantime\Domain\Plugins\Models\InstalledPlugin;
use Leantime\Domain\Plugins\Repositories\Plugins as PluginRepository;
use Leantime\Domain\Plugins\Services\Plugins;
use Leantime\Domain\Setting\Services\Setting as SettingsService;
use Unit\TestCase;

/**
 * Update discovery (newest compatible marketplace release) and the one-row-per-plugin rule for
 * installs and updates.
 */
class PluginUpdatesTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<string, mixed> */
    private array $savedSettings = [];

    private function marketplacePlugin(int $id, string $folder, string $version, string $license = 'lic-123'): InstalledPlugin
    {
        $plugin = new InstalledPlugin;
        $plugin->id = $id;
        $plugin->name = 'leantime/'.strtolower($folder);
        $plugin->foldername = $folder;
        $plugin->format = 'phar';
        $plugin->version = $version;
        $plugin->enabled = true;
        $plugin->license = $license;

        return $plugin;
    }

    /**
     * @param  array<int, array<string, string>>  $releases  The marketplace compatibility list.
     */
    private function service(array $installed, array $releases, array $repositoryCalls = []): Plugins
    {
        $appSettings = new AppSettings;
        $appSettings->appVersion = '3.12.0';

        // The update check asks the marketplace itself (verified TLS): batch "not offered", details answers.
        \Illuminate\Support\Facades\Http::fake([
            '*/ltmp-api/latest*' => \Illuminate\Support\Facades\Http::response('Not found', 404),
            '*/ltmp-api/details/*' => \Illuminate\Support\Facades\Http::response(['compatibility' => $releases]),
        ]);

        return $this->make(Plugins::class, [
            'marketplaceUrl' => 'https://marketplace.test',
            'pluginRepository' => $this->makeEmpty(PluginRepository::class, $repositoryCalls + ['getAllPlugins' => fn () => $installed]),
            'appSettings' => $appSettings,
            'compatibility' => new PluginCompatibility($appSettings),
            'settingsService' => $this->makeEmpty(SettingsService::class, [
                // Already notified about each version, so no notification queries run.
                'getSetting' => fn ($key, $default = false) => str_starts_with($key, 'plugins.updateNotified.') ? '1.5.0' : $default,
                'saveSetting' => function ($key, $value) {
                    $this->savedSettings[$key] = $value;

                    return true;
                },
            ]),
            'clearCache' => null,
        ]);
    }

    public function test_finds_the_newest_release_that_supports_this_version(): void
    {
        $plugins = $this->service([$this->marketplacePlugin(1, 'Notes', '1.4.2')], [
            ['version_number' => '1.4.2', 'supported_version_from' => '3.0', 'supported_version_to' => 'latest'],
            ['version_number' => '1.5.0', 'supported_version_from' => '3.12', 'supported_version_to' => 'latest'],
            // Newer, but for a later Leantime: never offered here.
            ['version_number' => '2.0.0', 'supported_version_from' => '3.13', 'supported_version_to' => 'latest'],
        ]);

        $this->assertSame(['updates' => ['Notes' => '1.5.0'], 'failed' => []], $plugins->checkForUpdates());
        $this->assertSame(json_encode(['Notes' => '1.5.0']), $this->savedSettings['plugins.availableUpdates']);
    }

    public function test_an_up_to_date_plugin_has_no_update(): void
    {
        $plugins = $this->service([$this->marketplacePlugin(1, 'Notes', '1.5.0')], [
            ['version_number' => '1.5.0', 'supported_version_from' => '3.0', 'supported_version_to' => '3.99'],
        ]);

        $this->assertSame(['updates' => [], 'failed' => []], $plugins->checkForUpdates());
    }

    public function test_folder_plugins_are_not_checked(): void
    {
        $folderPlugin = $this->marketplacePlugin(1, 'MyPlugin', '1.0.0');
        $folderPlugin->format = 'folder';

        $plugins = $this->service([$folderPlugin], [
            ['version_number' => '9.0.0', 'supported_version_from' => '3.0', 'supported_version_to' => 'latest'],
        ]);

        $this->assertSame(['updates' => [], 'failed' => []], $plugins->checkForUpdates());
    }

    public function test_an_update_rewrites_the_existing_row_and_removes_duplicates(): void
    {
        $calls = ['added' => 0, 'replaced' => []];
        $plugins = $this->service([], [], [
            'getPluginIdsByFolder' => fn () => [3, 17, 21],
            'addPlugin' => function () use (&$calls) {
                $calls['added']++;

                return '99';
            },
            'replacePluginRows' => function (int $id, InstalledPlugin $plugin, array $duplicateIds) use (&$calls) {
                $calls['replaced'][] = [$id, $plugin->version, $duplicateIds];
            },
        ]);

        $record = new \ReflectionMethod(Plugins::class, 'recordInstalledPlugin');
        $record->invoke($plugins, $this->marketplacePlugin(0, 'Notes', '1.5.0', ''));

        // One transactional call: row 3 rewritten, 17 and 21 removed together.
        $this->assertSame(0, $calls['added']);
        $this->assertSame([[3, '1.5.0', [17, 21]]], $calls['replaced']);
    }

    public function test_an_update_reuses_the_stored_license_key(): void
    {
        $plugins = $this->service([$this->marketplacePlugin(3, 'Notes', '1.4.2', 'stored-key')], []);

        $find = new \ReflectionMethod(Plugins::class, 'findInstalledMarketplacePlugin');
        $installed = $find->invoke($plugins, 'leantime_notes');

        $this->assertSame('stored-key', $installed?->license);
    }

    /** A marketplace outage is a failed check, not "up to date": the earlier result is kept. */
    public function test_an_unanswered_check_keeps_the_previous_result_and_reports_the_failure(): void
    {
        // The marketplace is down: neither the batch call nor details answers.
        \Illuminate\Support\Facades\Http::fake(['*' => \Illuminate\Support\Facades\Http::response('Unavailable', 503)]);

        $plugins = $this->make(Plugins::class, [
            'pluginRepository' => $this->makeEmpty(PluginRepository::class, ['getAllPlugins' => fn () => [$this->marketplacePlugin(1, 'Notes', '1.4.2')]]),
            'appSettings' => new AppSettings,
            'compatibility' => new PluginCompatibility(new AppSettings),
            'settingsService' => $this->makeEmpty(SettingsService::class, [
                'getSetting' => fn ($key, $default = false) => $key === 'plugins.availableUpdates' ? json_encode(['Notes' => '1.5.0']) : $default,
                'saveSetting' => function ($key, $value) {
                    $this->savedSettings[$key] = $value;

                    return true;
                },
            ]),
            'marketplaceUrl' => 'https://marketplace.test',
            'clearCache' => null,
        ]);

        $this->assertSame(['updates' => ['Notes' => '1.5.0'], 'failed' => ['Notes']], $plugins->checkForUpdates());
        $this->assertSame(json_encode(['Notes' => '1.5.0']), $this->savedSettings['plugins.availableUpdates']);
    }
}

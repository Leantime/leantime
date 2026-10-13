<?php

namespace Unit\app\Domain\Plugins\Services;

use Illuminate\Support\Facades\Cache;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Plugins\PluginCompatibility;
use Leantime\Domain\Plugins\Models\InstalledPlugin;
use Leantime\Domain\Plugins\Repositories\Plugins as PluginRepository;
use Leantime\Domain\Plugins\Services\Plugins;
use Leantime\Domain\Setting\Services\Setting as SettingsService;
use Unit\TestCase;

/**
 * A failing plugin-table query must never be cached as "no plugins enabled": that silently ran
 * the scheduler without plugin jobs until the cache was cleared. The console asks for the error.
 */
class EnabledPluginsCacheTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** The list is cached per Leantime version, so a core update rebuilds it. */
    private function cacheKey(): string
    {
        return 'plugins.enabledPlugins.'.app(AppSettings::class)->appVersion;
    }

    protected function setUp(): void
    {
        parent::setUp();

        Cache::store('installation')->forget($this->cacheKey());
    }

    protected function tearDown(): void
    {
        Cache::store('installation')->forget($this->cacheKey());

        parent::tearDown();
    }

    private function service(callable $getAllPlugins): Plugins
    {
        $config = $this->make(Environment::class, [
            'get' => fn ($key, $default = null) => $key === 'debug' ? false : $default,
        ]);

        return $this->make(Plugins::class, [
            'pluginRepository' => $this->makeEmpty(PluginRepository::class, ['getAllPlugins' => $getAllPlugins]),
            'config' => $config,
            'appSettings' => app(AppSettings::class),
            'compatibility' => app(PluginCompatibility::class),
        ]);
    }

    public function test_database_failure_is_not_cached_as_no_plugins(): void
    {
        $plugins = $this->service(fn () => throw new \PDOException('plugin table unavailable'));

        $this->assertSame([], $plugins->getEnabledPlugins());
        $this->assertFalse(Cache::store('installation')->has($this->cacheKey()));
    }

    public function test_database_failure_is_rethrown_when_requested(): void
    {
        $plugins = $this->service(fn () => throw new \PDOException('plugin table unavailable'));

        $this->expectException(\PDOException::class);

        $plugins->getEnabledPlugins(failOnDatabaseError: true);
    }

    public function test_successful_read_is_cached(): void
    {
        $plugins = $this->service(fn () => []);

        $this->assertSame([], $plugins->getEnabledPlugins());
        $this->assertTrue(Cache::store('installation')->has($this->cacheKey()));
    }

    public function test_plugins_that_dont_support_this_version_are_not_loaded(): void
    {
        $pluginDir = sys_get_temp_dir().'/lt-plugin-guard-'.getmypid().'/';
        foreach (['Current' => '>=3.0', 'Future' => '>=99.0'] as $folder => $range) {
            @mkdir($pluginDir.$folder, 0777, true);
            file_put_contents($pluginDir.$folder.'/composer.json', json_encode([
                'name' => 'leantime/'.strtolower($folder),
                'extra' => ['leantime' => ['requires' => $range]],
            ]));
        }

        $rows = [];
        foreach (['Current', 'Future'] as $id => $folder) {
            $plugin = new InstalledPlugin;
            $plugin->id = $id + 1;
            $plugin->name = 'leantime/'.strtolower($folder);
            $plugin->enabled = true;
            $plugin->foldername = $folder;
            $plugin->format = 'folder';
            $plugin->version = '1.0.0';
            $rows[] = $plugin;
        }

        $config = $this->make(Environment::class, [
            'get' => fn ($key, $default = null) => $key === 'debug' ? false : $default,
        ]);
        $plugins = $this->make(Plugins::class, [
            'pluginRepository' => $this->makeEmpty(PluginRepository::class, ['getAllPlugins' => fn () => $rows]),
            'config' => $config,
            'appSettings' => app(AppSettings::class),
            'compatibility' => app(PluginCompatibility::class),
            // Already notified for this version, so the test never reaches the notification queries.
            'settingsService' => $this->makeEmpty(SettingsService::class, [
                'getSetting' => fn () => '1.0.0|'.app(AppSettings::class)->appVersion,
            ]),
        ]);
        $directory = new \ReflectionProperty(Plugins::class, 'pluginDirectory');
        $directory->setValue($plugins, $pluginDir);

        $enabled = $plugins->getEnabledPlugins();

        $this->assertArrayHasKey('Current', $enabled);
        $this->assertArrayNotHasKey('Future', $enabled);

        foreach (['Current', 'Future'] as $folder) {
            @unlink($pluginDir.$folder.'/composer.json');
            @rmdir($pluginDir.$folder);
        }
        @rmdir($pluginDir);
    }

    public function test_incompatible_system_plugins_are_not_loaded_either(): void
    {
        $pluginDir = sys_get_temp_dir().'/lt-system-guard-'.getmypid().'/';
        foreach (['CurrentSystem' => '>=3.0', 'FutureSystem' => '>=99.0'] as $folder => $range) {
            @mkdir($pluginDir.$folder, 0777, true);
            file_put_contents($pluginDir.$folder.'/composer.json', json_encode([
                'name' => 'leantime/'.strtolower($folder),
                'description' => 'test',
                'version' => '1.0.0',
                'homepage' => '',
                'authors' => [],
                'extra' => ['leantime' => ['requires' => $range]],
            ]));
        }

        $config = $this->make(Environment::class, [
            'has' => fn ($key) => $key === 'plugins',
            'get' => fn ($key, $default = null) => match ($key) {
                'debug' => false,
                'plugins' => 'CurrentSystem,FutureSystem',
                default => $default,
            },
        ]);
        $plugins = $this->make(Plugins::class, [
            'pluginRepository' => $this->makeEmpty(PluginRepository::class, ['getAllPlugins' => fn () => []]),
            'config' => $config,
            'appSettings' => app(AppSettings::class),
            'compatibility' => app(PluginCompatibility::class),
            'settingsService' => $this->makeEmpty(SettingsService::class),
        ]);
        $directory = new \ReflectionProperty(Plugins::class, 'pluginDirectory');
        $directory->setValue($plugins, $pluginDir);

        $enabled = $plugins->getEnabledPlugins();

        // Positive control: the system plugin path is exercised at all.
        $this->assertArrayHasKey('CurrentSystem', $enabled);
        $this->assertArrayNotHasKey('FutureSystem', $enabled);

        foreach (['CurrentSystem', 'FutureSystem'] as $folder) {
            @unlink($pluginDir.$folder.'/composer.json');
            @rmdir($pluginDir.$folder);
        }
        @rmdir($pluginDir);
    }
}

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
            'settingsService' => $this->makeEmpty(SettingsService::class),
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

    /** System plugins come from config; whoever configures them owns their compatibility. */
    public function test_system_plugins_are_not_guarded(): void
    {
        $this->assertSystemPluginsLoaded(fn () => []);
    }

    /** The plugin table is down: the system plugins still load. */
    public function test_system_plugins_load_when_the_plugin_table_fails(): void
    {
        $this->assertSystemPluginsLoaded(fn () => throw new \PDOException('plugin table unavailable'));
    }

    private function assertSystemPluginsLoaded(callable $getAllPlugins): void
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
            'pluginRepository' => $this->makeEmpty(PluginRepository::class, ['getAllPlugins' => $getAllPlugins]),
            'config' => $config,
            'appSettings' => app(AppSettings::class),
            'compatibility' => app(PluginCompatibility::class),
            'settingsService' => $this->makeEmpty(SettingsService::class),
        ]);
        $directory = new \ReflectionProperty(Plugins::class, 'pluginDirectory');
        $directory->setValue($plugins, $pluginDir);

        $enabled = $plugins->getEnabledPlugins();

        $this->assertArrayHasKey('CurrentSystem', $enabled);
        $this->assertArrayHasKey('FutureSystem', $enabled);

        foreach (['CurrentSystem', 'FutureSystem'] as $folder) {
            @unlink($pluginDir.$folder.'/composer.json');
            @rmdir($pluginDir.$folder);
        }
        @rmdir($pluginDir);
    }

    /**
     * Without a loaded language (the console loads plugins first) the notice is still sent right away,
     * in English: the filtered list is cached, so a deferred notice would never be retried.
     */
    public function test_the_incompatibility_notice_reads_as_text_without_the_language(): void
    {
        // As in a console boot before the language is initialised: every key comes back as itself.
        $language = app(\Leantime\Core\Language::class);
        app()->instance(\Leantime\Core\Language::class, $this->makeEmpty(\Leantime\Core\Language::class, [
            '__' => fn (string $index, string $default = '') => $index,
        ]));

        $sent = [];
        app()->instance(\Leantime\Domain\Users\Services\Users::class, $this->makeEmpty(\Leantime\Domain\Users\Services\Users::class, [
            'getAll' => fn () => [['id' => 1, 'role' => 50]],
        ]));
        app()->instance(\Leantime\Domain\Notifications\Services\Notifications::class, $this->makeEmpty(\Leantime\Domain\Notifications\Services\Notifications::class, [
            'addNotifications' => function ($notifications) use (&$sent) {
                $sent = $notifications;
            },
        ]));

        $pluginDir = sys_get_temp_dir().'/lt-plugin-notice-'.getmypid().'/';
        @mkdir($pluginDir.'Future', 0777, true);
        file_put_contents($pluginDir.'Future/composer.json', json_encode(['name' => 'leantime/future', 'extra' => ['leantime' => ['requires' => '>=99.0']]]));

        $plugin = new InstalledPlugin;
        $plugin->id = 7;
        $plugin->name = 'leantime/future';
        $plugin->enabled = true;
        $plugin->foldername = 'Future';
        $plugin->format = 'folder';
        $plugin->version = '1.0.0';

        $saved = [];
        $config = $this->make(Environment::class, [
            'get' => fn ($key, $default = null) => $key === 'debug' ? false : $default,
        ]);
        $plugins = $this->make(Plugins::class, [
            'pluginRepository' => $this->makeEmpty(PluginRepository::class, ['getAllPlugins' => fn () => [$plugin]]),
            'config' => $config,
            'appSettings' => app(AppSettings::class),
            'compatibility' => app(PluginCompatibility::class),
            'settingsService' => $this->makeEmpty(SettingsService::class, [
                'getSetting' => fn ($key, $default = false) => $default,
                'saveSetting' => function ($key, $value) use (&$saved) {
                    $saved[$key] = $value;

                    return true;
                },
            ]),
        ]);
        (new \ReflectionProperty(Plugins::class, 'pluginDirectory'))->setValue($plugins, $pluginDir);

        $this->assertArrayNotHasKey('Future', $plugins->getEnabledPlugins());
        // Sent right away (the cached list never retries it), in English rather than as the bare key.
        $this->assertCount(1, $sent);
        $this->assertStringStartsWith("The plugin 'leantime/future' (v1.0.0) doesn't support Leantime", $sent[0]['message']);
        $this->assertArrayHasKey('plugins.incompatibleNotified.Future', $saved);

        app()->instance(\Leantime\Core\Language::class, $language);
        @unlink($pluginDir.'Future/composer.json');
        @rmdir($pluginDir.'Future');
        @rmdir($pluginDir);
    }
}

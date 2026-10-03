<?php

namespace Unit\app\Domain\Plugins\Services;

use Illuminate\Support\Facades\Cache;
use Leantime\Core\Configuration\Environment;
use Leantime\Domain\Plugins\Repositories\Plugins as PluginRepository;
use Leantime\Domain\Plugins\Services\Plugins;
use Unit\TestCase;

/**
 * A failing plugin-table query must never be cached as "no plugins enabled": that silently ran
 * the scheduler without plugin jobs until the cache was cleared. The console asks for the error.
 */
class EnabledPluginsCacheTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::store('installation')->forget('plugins.enabledPlugins');
    }

    protected function tearDown(): void
    {
        Cache::store('installation')->forget('plugins.enabledPlugins');

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
        ]);
    }

    public function test_database_failure_is_not_cached_as_no_plugins(): void
    {
        $plugins = $this->service(fn () => throw new \PDOException('plugin table unavailable'));

        $this->assertSame([], $plugins->getEnabledPlugins());
        $this->assertFalse(Cache::store('installation')->has('plugins.enabledPlugins'));
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
        $this->assertTrue(Cache::store('installation')->has('plugins.enabledPlugins'));
    }
}

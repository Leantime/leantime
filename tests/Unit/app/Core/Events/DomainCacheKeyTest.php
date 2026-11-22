<?php

namespace Unit\app\Core\Events;

use Illuminate\Support\Facades\Cache;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Events\EventDispatcher;
use Unit\TestCase;

/**
 * Discovered domain lists (register.php listeners, routes.php files) are cached forever outside
 * debug mode. The keys carry the Leantime version, so an upgrade that adds a domain (such as
 * Reminders) is found on the next request without a cache clear or a database update.
 */
class DomainCacheKeyTest extends TestCase
{
    protected function tearDown(): void
    {
        app()->forgetInstance(AppSettings::class);
        parent::tearDown();
    }

    private function runningVersion(string $version): void
    {
        $settings = new AppSettings;
        $settings->appVersion = $version;
        app()->instance(AppSettings::class, $settings);
    }

    public function test_the_key_carries_the_running_version(): void
    {
        $this->runningVersion('3.15.3');
        $this->assertSame('domainEvents.3.15.3', EventDispatcher::domainCacheKey('domainEvents'));

        $this->runningVersion('3.16.0');
        $this->assertSame('domainRoutes.3.16.0', EventDispatcher::domainCacheKey('domainRoutes'));
    }

    public function test_a_list_cached_by_the_previous_version_is_not_reused_after_an_upgrade(): void
    {
        $store = Cache::store('array');

        $this->runningVersion('3.15.3');
        $store->rememberForever(EventDispatcher::domainCacheKey('domainEvents'), fn () => ['/app/Domain/Tickets']);

        $this->runningVersion('3.16.0');
        $modules = $store->rememberForever(EventDispatcher::domainCacheKey('domainEvents'), fn () => ['/app/Domain/Tickets', '/app/Domain/Reminders']);

        $this->assertContains('/app/Domain/Reminders', $modules);
    }

    public function test_discovery_and_routes_read_the_versioned_keys(): void
    {
        $events = (string) file_get_contents(APP_ROOT.'/app/Core/Events/EventDispatcher.php');
        $routes = (string) file_get_contents(APP_ROOT.'/app/Core/Routing/RouteLoader.php');

        $this->assertStringContainsString("rememberForever(self::domainCacheKey('domainEvents')", $events);
        $this->assertStringContainsString("rememberForever(EventDispatcher::domainCacheKey('domainRoutes')", $routes);
        $this->assertStringNotContainsString("rememberForever('domainEvents'", $events);
        $this->assertStringNotContainsString("rememberForever('domainRoutes'", $routes);
    }
}

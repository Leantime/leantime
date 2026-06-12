<?php

namespace Unit\app\Core\Controller;

use Illuminate\Support\Facades\Cache;
use Leantime\Core\Auth\Permissions\PermissionEnforcer;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Http\IncomingRequest;
use Unit\TestCase;

/**
 * Regression coverage for stale route-cache entries. Routes resolved by the
 * Frontcontroller are cached across requests in the installation store and can
 * outlive a deploy: a controller whose run() was replaced by get()/post() left
 * a cached ['method' => 'run'] entry behind, and callAction('run') then hit
 * __call() and produced a 500 (seen in production on /calendar/showMyCalendar
 * and /timesheets/showMy). A cached entry must only be trusted if its class and
 * method still exist; otherwise it gets dropped and the route re-resolved.
 */
class FrontcontrollerRouteCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Route caching is skipped entirely when debug is on.
        config(['debug' => false]);
    }

    private function frontcontroller(): Frontcontroller
    {
        // Built by hand: container resolution would pull in the real
        // PermissionEnforcer, which needs a database connection.
        return new Frontcontroller(
            IncomingRequest::create('/calendar/showMyCalendar', 'GET'),
            $this->createMock(PermissionEnforcer::class),
        );
    }

    private function cacheKey(string $module, string $action, string $method): string
    {
        return 'routes.'.$module.'.Controllers.'.$action.'.'.$method;
    }

    public function test_stale_cached_method_is_dropped_and_route_reresolved(): void
    {
        // Simulate a pre-deploy cache entry pointing at the removed run() method.
        $key = $this->cacheKey('Calendar', 'ShowMyCalendar', 'get');
        Cache::store('installation')->set($key, [
            'class' => \Leantime\Domain\Calendar\Controllers\ShowMyCalendar::class,
            'method' => 'run',
        ]);

        $result = $this->frontcontroller()->getValidControllerCall('calendar', 'showMyCalendar', 'get', 'Controllers');

        $this->assertSame('get', $result['method']);
        $this->assertSame(\Leantime\Domain\Calendar\Controllers\ShowMyCalendar::class, $result['class']);

        // The stale entry must have been replaced with the fresh resolution.
        $this->assertSame($result, Cache::store('installation')->get($key));
    }

    public function test_cached_entry_with_missing_class_is_dropped(): void
    {
        $key = $this->cacheKey('Calendar', 'ShowMyCalendar', 'get');
        Cache::store('installation')->set($key, [
            'class' => 'Leantime\\Domain\\Calendar\\Controllers\\NoLongerExists',
            'method' => 'get',
        ]);

        $result = $this->frontcontroller()->getValidControllerCall('calendar', 'showMyCalendar', 'get', 'Controllers');

        $this->assertSame(\Leantime\Domain\Calendar\Controllers\ShowMyCalendar::class, $result['class']);
        $this->assertSame('get', $result['method']);
    }

    public function test_valid_cached_entry_is_returned_as_is(): void
    {
        $key = $this->cacheKey('Calendar', 'ShowMyCalendar', 'get');
        $cached = [
            'class' => \Leantime\Domain\Calendar\Controllers\ShowMyCalendar::class,
            'method' => 'get',
        ];
        Cache::store('installation')->set($key, $cached);

        $result = $this->frontcontroller()->getValidControllerCall('calendar', 'showMyCalendar', 'get', 'Controllers');

        $this->assertSame($cached, $result);
    }
}

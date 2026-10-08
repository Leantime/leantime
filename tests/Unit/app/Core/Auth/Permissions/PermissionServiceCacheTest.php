<?php

namespace Tests\Unit\app\Core\Auth\Permissions;

use Illuminate\Support\Facades\Cache;
use Leantime\Core\Auth\Contracts\ChecksProjectAccess;
use Leantime\Core\Auth\Permissions\PermissionRepository;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Auth\RoleResolver;

/**
 * The role -> permission grant map comes from the instance's own database, so it is cached on the
 * default (instance-scoped) store, never the shared installation store: on a multi-tenant host
 * that one key served every tenant whichever grant map was rebuilt last. An empty map (unseeded
 * role tables) is never cached, so a seed or reseed takes effect on the next request.
 */
class PermissionServiceCacheTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    private int $mapReads = 0;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.stores.installation' => ['driver' => 'array'],
            'cache.stores.instance' => ['driver' => 'array'],
            'cache.default' => 'instance',
        ]);
        Cache::forgetDriver(['installation', 'instance']);
    }

    /**
     * @param  array<string, array<int, string>>  $grantMap
     */
    private function service(array $grantMap): PermissionService
    {
        $repo = $this->make(PermissionRepository::class, [
            'getRolePermissionMap' => function () use ($grantMap): array {
                $this->mapReads++;

                return $grantMap;
            },
        ]);

        return new PermissionService(
            $repo,
            $this->makeEmpty(RoleResolver::class),
            $this->makeEmpty(ChecksProjectAccess::class),
        );
    }

    public function test_the_grant_map_is_cached_on_the_instance_store_not_the_shared_installation_store(): void
    {
        $service = $this->service(['owner' => ['users.view']]);

        $this->assertTrue($service->roleHasPermission('owner', 'users.view'));
        $this->assertTrue($service->roleHasPermission('owner', 'users.view'));

        $this->assertSame(1, $this->mapReads, 'The map is read from the database once, then served from cache');
        $this->assertNotNull(Cache::store()->get('leantime.permissionMap'));
        $this->assertNull(Cache::store('installation')->get('leantime.permissionMap'), 'Never on the store shared by every tenant');
    }

    public function test_an_empty_grant_map_is_not_cached(): void
    {
        $service = $this->service([]);

        $this->assertFalse($service->roleHasPermission('owner', 'users.view'));
        $this->assertFalse($service->roleHasPermission('owner', 'users.view'));

        $this->assertSame(2, $this->mapReads, 'An unseeded install is re-read each time so a reseed takes effect at once');
        $this->assertNull(Cache::store()->get('leantime.permissionMap'));
    }

    public function test_flush_cache_forgets_the_instance_copy(): void
    {
        $service = $this->service(['owner' => ['users.view']]);
        $service->roleHasPermission('owner', 'users.view');

        $service->flushCache();

        $this->assertNull(Cache::store()->get('leantime.permissionMap'));
    }
}

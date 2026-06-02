<?php

namespace Leantime\Core\Auth\Permissions;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Cache;

/**
 * Discovers and aggregates the permission vocabulary declared by every domain (and folder
 * plugin) implementing {@see ProvidesPermissions}.
 *
 * Mirrors {@see \Leantime\Core\Events\EventDispatcher::discoverListeners()}: it globs the
 * conventional locations, caches the discovered provider class list on the shared
 * `installation` store outside debug mode, then instantiates each provider and merges its
 * {@see Permission} declarations into a single keyed catalog. The catalog is the in-memory
 * source of truth that `permissions:sync` writes into the database.
 */
class PermissionRegistry
{
    private const PROVIDER_CACHE_KEY = 'permissionProviders';

    /** @var array<string, Permission>|null */
    private ?array $catalog = null;

    public function __construct(private Container $container) {}

    /**
     * The full catalog keyed by permission key (e.g. 'tickets.create' => Permission).
     *
     * @return array<string, Permission>
     */
    public function all(): array
    {
        if ($this->catalog !== null) {
            return $this->catalog;
        }

        $this->catalog = [];

        foreach ($this->providerClasses() as $class) {
            $provider = $this->container->make($class);

            if (! $provider instanceof ProvidesPermissions) {
                continue;
            }

            foreach ($provider->permissions() as $permission) {
                $this->catalog[$permission->key] = $permission;
            }
        }

        return $this->catalog;
    }

    public function get(string $key): ?Permission
    {
        return $this->all()[$key] ?? null;
    }

    /** Drop the in-memory and cross-request provider caches (call on plugin enable/disable). */
    public function flush(): void
    {
        $this->catalog = null;
        Cache::store('installation')->forget(self::PROVIDER_CACHE_KEY);
    }

    /**
     * The discovered provider class names. Cached on the installation store outside debug
     * mode, exactly like EventDispatcher's 'domainEvents'.
     *
     * @return array<int, string>
     */
    private function providerClasses(): array
    {
        if ((bool) config('debug') === false) {
            return Cache::store('installation')->rememberForever(self::PROVIDER_CACHE_KEY, fn () => $this->scanProviderClasses());
        }

        return $this->scanProviderClasses();
    }

    /**
     * Glob the conventional provider locations and resolve each to a FQCN.
     *
     * @return array<int, string>
     */
    private function scanProviderClasses(): array
    {
        $patterns = [
            APP_ROOT.'/app/Domain/*/Permissions/*Permissions.php',
            APP_ROOT.'/app/Plugins/*/Permissions/*Permissions.php',
        ];

        $classes = [];

        foreach ($patterns as $pattern) {
            foreach ((array) glob($pattern) as $file) {
                $class = $this->classFromPath((string) $file);

                if ($class !== null) {
                    $classes[] = $class;
                }
            }
        }

        return $classes;
    }

    /**
     * Map an app file path to its FQCN under the Leantime namespace
     * (app/Domain/Tickets/Permissions/TicketsPermissions.php ->
     * Leantime\Domain\Tickets\Permissions\TicketsPermissions).
     */
    private function classFromPath(string $file): ?string
    {
        $relative = str_replace(APP_ROOT.'/app/', '', $file);
        $relative = substr($relative, 0, -strlen('.php'));
        $class = 'Leantime\\'.str_replace('/', '\\', $relative);

        return class_exists($class) ? $class : null;
    }
}

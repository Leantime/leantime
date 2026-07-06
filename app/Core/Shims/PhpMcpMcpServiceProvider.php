<?php

declare(strict_types=1);

/*
 * Compatibility shim for upgrades from Leantime <= 3.9.6.
 *
 * Through 3.9.6, core depended on php-mcp/laravel, whose service provider
 * (PhpMcp\Laravel\McpServiceProvider) was auto-discovered by Laravel's package
 * manifest and baked into bootstrap/cache/packages.php. 3.9.7 replaced php-mcp
 * with laravel/mcp and dropped the package.
 *
 * An in-place (unzip) upgrade does not clear bootstrap/cache, so the stale
 * compiled manifest survives and still lists the old provider. Laravel then
 * tries to instantiate PhpMcp\Laravel\McpServiceProvider while registering
 * providers during bootstrap and fatals with "class not found" -- before the
 * Leantime updater (which clears the cache) ever gets a chance to run. The app
 * is fully down (see leantime/leantime#3601).
 *
 * This no-op provider stands in for the removed class so the application boots,
 * then deletes the stale compiled manifests so the next request rebuilds them
 * against the current (php-mcp-free) dependency set and this shim is no longer
 * referenced.
 *
 * Safe to delete once in-place upgrades from <= 3.9.6 are no longer supported.
 */

namespace PhpMcp\Laravel;

use Illuminate\Support\ServiceProvider;

class McpServiceProvider extends ServiceProvider
{
    /**
     * Boot far enough to survive a stale package manifest, then flush it so the
     * next request no longer references the removed php-mcp provider.
     */
    public function register(): void
    {
        $this->flushStalePackageManifest();
    }

    /**
     * Remove the compiled package/service manifests. They were already loaded
     * into memory for this request, so deleting them here is safe and simply
     * forces Laravel to recompile them (without php-mcp) on the next request.
     */
    private function flushStalePackageManifest(): void
    {
        foreach (['packages.php', 'services.php'] as $file) {
            $path = $this->app->bootstrapPath('cache/'.$file);

            if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}

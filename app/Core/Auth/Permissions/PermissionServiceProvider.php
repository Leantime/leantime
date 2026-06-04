<?php

namespace Leantime\Core\Auth\Permissions;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Support\ServiceProvider;
use Leantime\Core\Auth\Contracts\ChecksProjectAccess;
use Leantime\Core\Domains\BaseService;
use Leantime\Domain\Projects\Services\Projects;

/**
 * Wires the native permission engine: singletons, the project-access abstraction binding,
 * the Gate bridge, and dependency injection for {@see BaseService} subclasses.
 *
 * Registered in laravelConfig's provider list. Keeping this separate from
 * AuthenticationServiceProvider keeps authn (guards/tokens) and authz (permissions) cleanly
 * apart.
 */
class PermissionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PermissionService::class);
        $this->app->singleton(PermissionEnforcer::class);
        $this->app->singleton(PermissionRegistry::class);

        // The engine depends on a narrow project-access abstraction, not the Projects
        // god-service. A shared singleton so RoleResolver and PermissionService reuse one
        // instance and we don't construct Projects more than once.
        $this->app->singleton(ChecksProjectAccess::class, fn ($app) => $app->make(Projects::class));
    }

    public function boot(): void
    {
        $this->registerPermissionGate();
        $this->injectBaseServiceDependencies();
    }

    /**
     * Bridge Laravel's authorization Gate to the engine. Leantime's authenticated user is a
     * stdClass (no `->can()`), so a single Gate::before hook resolves every `domain.action`
     * ability through {@see PermissionService::currentUserCan()} — making `@can('tickets.create')`,
     * `Gate::allows()`, and the `can` middleware all speak the one vocabulary. Non-permission
     * abilities (no dot, or not in the synced catalog) return null so other gates still work;
     * resolution is deferred into the closure so Projects isn't constructed at boot.
     */
    protected function registerPermissionGate(): void
    {
        $container = $this->app;

        $this->app->make(GateContract::class)->before(function ($user, string $ability, array $arguments = []) use ($container) {
            if (! str_contains($ability, '.')) {
                return null;
            }

            try {
                $permissions = $container->make(PermissionService::class);

                if (! $permissions->isManagedPermission($ability)) {
                    return null;
                }

                // Only treat the first gate argument as a project id when it is numeric;
                // @can / Gate::allows may pass models or other objects. Otherwise fall back
                // to session scope (null).
                $projectId = isset($arguments[0]) && is_numeric($arguments[0]) ? (int) $arguments[0] : null;

                return $permissions->currentUserCan($ability, $projectId);
            } catch (\Throwable) {
                // Permission tables not ready yet (e.g. pre-migration install) — defer.
                return null;
            }
        });
    }

    /**
     * Wire PermissionService into every service that extends {@see BaseService}, without forcing
     * subclass constructors to wire it. The afterResolving callback fires for any resolved instance
     * that is `instanceof BaseService`.
     *
     * We wire a LAZY resolver, not the instance: a BaseService can sit inside PermissionService's
     * own dependency graph (Files is reached via PermissionService → ChecksProjectAccess → Projects
     * → Files), so eagerly calling `make(PermissionService)` here would re-enter PermissionService's
     * half-built construction and recurse infinitely (stack overflow at boot). Resolving lazily on
     * first authorize()/can() defers it until the singleton has been built.
     */
    protected function injectBaseServiceDependencies(): void
    {
        $this->app->afterResolving(BaseService::class, function (BaseService $service, $app) {
            $service->setPermissionServiceResolver(fn () => $app->make(PermissionService::class));
        });
    }
}

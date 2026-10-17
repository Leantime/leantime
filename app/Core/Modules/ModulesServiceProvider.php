<?php

namespace Leantime\Core\Modules;

use Illuminate\Support\ServiceProvider;
use Leantime\Core\Modules\Contracts\StoresProjectModules;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;

/**
 * Wires per-project modules: the registry, the project state service and the `@module` Blade
 * condition.
 *
 * Like Plugins and WorkStructure this lives in Core because it governs domains rather than being
 * one. The project settings tab and the API live in the Modulemanager domain, which also
 * registers the core modules (app/Domain/Modulemanager/register.php).
 */
class ModulesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleRegistry::class);
        $this->app->singleton(ProjectModules::class);

        // The repository, not the Projects service: the permission engine reads module state on
        // every project-scoped check and must not construct the Projects service graph for it.
        $this->app->singleton(StoresProjectModules::class, fn ($app) => $app->make(ProjectRepository::class));
    }

    /**
     * Register `@module('wiki') … @endmodule`, which renders its body only when the module is on.
     * The project defaults to the current one; pass it when the entity belongs to another project:
     * `@module('timesheets', $ticket->projectId)`.
     */
    public function boot(): void
    {
        $container = $this->app;

        $this->app->make('blade.compiler')->if('module', function (string $moduleId, $projectId = null) use ($container): bool {
            $projectId = is_numeric($projectId) ? (int) $projectId : (int) session('currentProject');

            return $container->make(ProjectModules::class)->isEnabled($moduleId, $projectId);
        });
    }
}

<?php

namespace Leantime\Core\WorkStructure;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Leantime\Core\WorkStructure\Repositories\WorkStructureRepository;
use Leantime\Core\WorkStructure\Services\MappingService;
use Leantime\Core\WorkStructure\Services\StructureRegistry;
use Leantime\Core\WorkStructure\Services\WorkStructureService;

/**
 * Registers the WorkStructure infrastructure and seeds the built-in
 * "Project" system structure.
 *
 * WorkStructure is a meta-model that describes how Leantime entities are
 * composed and how they map across structures (e.g., a Logic Model "output"
 * generates a Project "milestone"). It lives in Core — like Plugins and Auth —
 * because it governs domains rather than being one. Plugins register their own
 * structures and mappings through {@see StructureRegistry}.
 */
class WorkStructureServiceProvider extends ServiceProvider
{
    /**
     * Bind the WorkStructure services as singletons.
     */
    public function register(): void
    {
        $this->app->singleton(WorkStructureRepository::class);
        $this->app->singleton(StructureRegistry::class);
        $this->app->singleton(MappingService::class);
        $this->app->singleton(WorkStructureService::class);
    }

    /**
     * Seed the built-in "Project" structure (idempotent).
     *
     * StructureRegistry::register() is cache-guarded and a no-op once the
     * structure exists, so this costs a cache read in steady state. Wrapped in
     * try/catch because the tables do not exist yet during a fresh install.
     */
    public function boot(): void
    {
        try {
            /** @var StructureRegistry $registry */
            $registry = $this->app->make(StructureRegistry::class);

            $registry->register(
                'Project',
                'system',
                [
                    ['typeKey' => 'milestone', 'label' => 'Milestone', 'domainReference' => 'Leantime\\Domain\\Tickets', 'sortOrder' => 1],
                    ['typeKey' => 'task', 'label' => 'Task', 'domainReference' => 'Leantime\\Domain\\Tickets', 'sortOrder' => 2],
                    ['typeKey' => 'goal', 'label' => 'Goal', 'domainReference' => 'Leantime\\Domain\\Goalcanvas', 'sortOrder' => 3],
                ],
                [
                    ['fromTypeKey' => 'task', 'toTypeKey' => 'milestone', 'relationshipType' => 'belongs_to'],
                    ['fromTypeKey' => 'milestone', 'toTypeKey' => 'goal', 'relationshipType' => 'measures'],
                ]
            );
        } catch (\Throwable $e) {
            // Tables may not exist yet during install/update — degrade gracefully.
            Log::debug('WorkStructure seed skipped: '.$e->getMessage());
        }
    }
}

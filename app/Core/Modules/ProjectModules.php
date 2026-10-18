<?php

namespace Leantime\Core\Modules;

use Leantime\Core\Modules\Contracts\StoresProjectModules;

/**
 * Answers "is module X on in project Y?" and saves a project's choices.
 *
 * A module is on in a project unless the project switched it off, or it is off by default and
 * the project switched it on. Ids that aren't registered (a core feature that isn't a module, a
 * plugin that is disabled for the whole install) always count as on: this only ever hides
 * features a project explicitly turned off.
 *
 * Stored choices are read once per project per request.
 */
class ProjectModules
{
    /** @var array<int, array<string, bool>> Stored overrides per project id. */
    private array $overridesByProject = [];

    /** @var array<int, array<string, bool>>|null Stored overrides of every project that has any. */
    private ?array $allOverrides = null;

    public function __construct(
        private ModuleRegistry $registry,
        private StoresProjectModules $store,
    ) {}

    /**
     * Whether a module is on in a project. Without a project (no project selected, company
     * pages) every module counts as on.
     */
    public function isEnabled(string $moduleId, ?int $projectId): bool
    {
        $module = $this->registry->get($moduleId);

        if ($module === null || $projectId === null || $projectId <= 0) {
            return true;
        }

        $overrides = $this->overridesFor($projectId);

        return $overrides[$module->id] ?? $module->enabledByDefault;
    }

    /**
     * Every registered module with its state in a project.
     *
     * @return array<string, bool> Module id => enabled, in registry order.
     */
    public function statesFor(int $projectId): array
    {
        $states = [];

        foreach ($this->registry->all() as $module) {
            $states[$module->id] = $this->isEnabled($module->id, $projectId);
        }

        return $states;
    }

    /**
     * Save a project's module choices.
     *
     * Only registered modules are changed and only choices that differ from the module's
     * default are stored. Stored choices for modules that aren't registered right now (a plugin
     * that is disabled for the install) are kept, so they come back when the plugin does.
     *
     * @param  array<string, bool>  $states  Module id => enabled. Modules left out keep their state.
     */
    public function save(int $projectId, array $states): void
    {
        $this->store->updateModuleOverrides($projectId, function (array $overrides) use ($states): array {
            foreach ($states as $moduleId => $enabled) {
                $module = $this->registry->get((string) $moduleId);

                if ($module === null) {
                    continue;
                }

                if ((bool) $enabled === $module->enabledByDefault) {
                    unset($overrides[$module->id]);

                    continue;
                }

                $overrides[$module->id] = (bool) $enabled;
            }

            return $overrides;
        });

        unset($this->overridesByProject[$projectId]);
        $this->allOverrides = null;
    }

    /**
     * Copy a project's stored choices to another project (used when a project is duplicated).
     */
    public function copy(int $fromProjectId, int $toProjectId): void
    {
        $sourceOverrides = $this->store->getModuleOverrides($fromProjectId);

        $this->store->updateModuleOverrides($toProjectId, fn (array $overrides): array => $sourceOverrides);
        unset($this->overridesByProject[$toProjectId]);
        $this->allOverrides = null;
    }

    /**
     * Drop the rows of projects that have a module switched off, for reads that span projects
     * (polling, MCP tools calling a service in-process past the permission hook).
     *
     * @param  array<int|string, array<string, mixed>>  $rows  Rows carrying a project id.
     * @return array<int, array<string, mixed>> The remaining rows as a list (stays a JSON array).
     */
    public function withoutSwitchedOffProjects(string $moduleId, array $rows, string $projectKey = 'projectId'): array
    {
        if (! $this->registry->has($moduleId)) {
            return array_values($rows);
        }

        $this->preload(array_column($rows, $projectKey));

        $remaining = array_filter(
            $rows,
            fn (array $row): bool => $this->isEnabled($moduleId, (int) ($row[$projectKey] ?? 0))
        );

        return array_values($remaining);
    }

    /**
     * The projects that have a module switched off, for queries that span every project and
     * exclude them in SQL (search). One read of all stored choices per request.
     *
     * Built from the stored choices, so it is complete for modules that are on by default (all
     * core modules). For a module that is off by default it lists only explicit "off" choices.
     *
     * @return array<int, int>
     */
    public function switchedOffProjectIds(string $moduleId): array
    {
        $module = $this->registry->get($moduleId);

        if ($module === null) {
            return [];
        }

        $this->allOverrides ??= $this->store->getAllModuleOverrides();

        $projectIds = [];

        foreach ($this->allOverrides as $projectId => $overrides) {
            if (($overrides[$module->id] ?? $module->enabledByDefault) === false) {
                $projectIds[] = (int) $projectId;
            }
        }

        return $projectIds;
    }

    /**
     * Load the stored choices of many projects in one read, so filtering rows that span projects
     * doesn't query once per project.
     *
     * @param  array<int, int|string|null>  $projectIds
     */
    public function preload(array $projectIds): void
    {
        $missingIds = [];

        foreach ($projectIds as $projectId) {
            $projectId = (int) $projectId;

            if ($projectId > 0 && ! isset($this->overridesByProject[$projectId])) {
                $missingIds[$projectId] = $projectId;
            }
        }

        if ($missingIds === []) {
            return;
        }

        foreach ($this->store->getModuleOverridesForProjects(array_values($missingIds)) as $projectId => $overrides) {
            $this->overridesByProject[(int) $projectId] = $overrides;
        }
    }

    /**
     * Whether a permission may be granted in a project as far as modules are concerned: false
     * when the permission's domain belongs to a module that is off in that project.
     *
     * @param  string  $permissionKey  A `domain.action` key, e.g. "wiki.edit".
     */
    public function allowsPermission(string $permissionKey, ?int $projectId): bool
    {
        if ($projectId === null || $projectId <= 0 || ! str_contains($permissionKey, '.')) {
            return true;
        }

        $permissionDomain = strstr($permissionKey, '.', true);
        $module = $this->registry->forPermissionDomain($permissionDomain);

        if ($module === null) {
            return true;
        }

        return $this->isEnabled($module->id, $projectId);
    }

    /**
     * The module that is off in a project and owns /{module}/{action}, or null when the request
     * may go ahead.
     */
    public function blockingRoute(string $module, string $action, ?int $projectId): ?Module
    {
        $owner = $this->registry->forRoute($module, $action);

        if ($owner === null || $this->isEnabled($owner->id, $projectId)) {
            return null;
        }

        return $owner;
    }

    /**
     * @return array<string, bool>
     */
    private function overridesFor(int $projectId): array
    {
        if (! isset($this->overridesByProject[$projectId])) {
            $this->overridesByProject[$projectId] = $this->store->getModuleOverrides($projectId);
        }

        return $this->overridesByProject[$projectId];
    }
}

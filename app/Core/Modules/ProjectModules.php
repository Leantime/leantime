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
    }

    /**
     * Copy a project's stored choices to another project (used when a project is duplicated).
     */
    public function copy(int $fromProjectId, int $toProjectId): void
    {
        $sourceOverrides = $this->store->getModuleOverrides($fromProjectId);

        $this->store->updateModuleOverrides($toProjectId, fn (array $overrides): array => $sourceOverrides);
        unset($this->overridesByProject[$toProjectId]);
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

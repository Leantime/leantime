<?php

/**
 * Module Manager
 */

namespace Leantime\Domain\Modulemanager\Services;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Domains\BaseService;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Modules\ModuleRegistry;
use Leantime\Core\Modules\ProjectModules;
use Leantime\Domain\Plugins\Services\Plugins;
use Leantime\Domain\Projects\Permissions\ProjectsPermissions;

/**
 * Which features are available: across the install (installed and enabled plugins) and per
 * project (Project settings → Modules). The per-project machinery lives in Core
 * ({@see ProjectModules}); this service is the API and settings surface for it.
 */
class Modulemanager extends BaseService
{
    // Used directly (not only via BaseService) so this class keeps its own event context: the
    // trait caches the context in a private static, which BaseService subclasses would otherwise
    // share, and plugins listen for leantime.domain.modulemanager.…moduleAvailability.
    use DispatchesEvents;

    public function __construct(
        private Plugins $pluginService,
        private ModuleRegistry $moduleRegistry,
        private ProjectModules $projectModules,
        private LanguageCore $language,
    ) {}

    /**
     * Checks if a module is available and enabled.
     * This also checks plugins and whether they are installed and enabled
     *
     * @param  string  $module  The name of the module to check availability for.
     * @return bool Returns true if the module is available, false otherwise.
     */
    public function isModuleAvailable(string $module): bool
    {
        $available = false;

        $plugins = $this->pluginService->getEnabledPlugins();

        $filtered = collect($plugins)->filter(function ($plugin) use ($module) {
            return strtolower($plugin->foldername) == strtolower($module);
        });

        if ($filtered->count() > 0) {
            $available = true;
        }

        $available = static::dispatch_filter('moduleAvailability', $available, ['module' => $module]);

        return $available;
    }

    /**
     * Whether a module is switched on in a project. Unknown module ids count as on.
     *
     * @param  string  $moduleId  The module id, e.g. "wiki".
     * @param  int  $projectId  The project to check.
     */
    public function isModuleEnabledForProject(string $moduleId, int $projectId): bool
    {
        return $this->projectModules->isEnabled($moduleId, $projectId);
    }

    /**
     * The modules a project can switch on or off, with their state in that project.
     *
     * @param  int  $projectId  The project to read.
     * @return array<int, array{id: string, title: string, description: string, icon: string, enabled: bool, plugin: string|null}>
     *
     * @api
     */
    #[RequiresPermission(ProjectsPermissions::VIEW, projectIdParam: 'projectId')]
    public function getProjectModules(int $projectId): array
    {
        // Mirrors the attribute in-body: MCP tools and other in-process callers bypass the gate.
        $this->authorize(ProjectsPermissions::VIEW, $projectId);

        $states = $this->projectModules->statesFor($projectId);
        $modules = [];

        foreach ($this->moduleRegistry->all() as $module) {
            $id = $module->id;
            $modules[] = [
                'id' => $id,
                'title' => $this->language->__($module->title),
                'description' => $module->description !== '' ? $this->language->__($module->description) : '',
                'icon' => $module->icon,
                'enabled' => $states[$id],
                'plugin' => $module->source,
            ];
        }

        return $modules;
    }

    /**
     * Switch modules on or off in a project. Modules left out keep their state; unknown ids are
     * ignored.
     *
     * @param  int  $projectId  The project to change.
     * @param  array<string, bool|int|string>  $modules  Module id => enabled.
     *
     * @api
     */
    #[RequiresPermission(ProjectsPermissions::EDIT, global: true)]
    public function saveProjectModules(int $projectId, array $modules): bool
    {
        // Project settings are a manager+ company action (projects.edit), limited to projects
        // the caller can see. Checked in-body too, for in-process callers that bypass the gate.
        $this->authorize(ProjectsPermissions::EDIT, null, true);
        $this->authorize(ProjectsPermissions::VIEW, $projectId);

        $states = [];

        foreach ($modules as $moduleId => $enabled) {
            $states[(string) $moduleId] = filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
        }

        $this->projectModules->save($projectId, $states);

        static::dispatch_event('projectModulesSaved', ['projectId' => $projectId, 'modules' => $states]);

        return true;
    }
}

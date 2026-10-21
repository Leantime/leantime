<?php

namespace Leantime\Core\Modules;

/**
 * The modules a project can switch on or off.
 *
 * Core modules are registered by the Modulemanager domain (app/Domain/Modulemanager/register.php),
 * plugin modules through {@see \Leantime\Domain\Plugins\Services\Registration::registerModule()}.
 * A plugin's register.php only runs while the plugin is enabled, so a plugin that is disabled for
 * the whole install is simply not in the registry and its project settings are left untouched.
 */
class ModuleRegistry
{
    /** @var array<string, Module> Keyed by lower-cased id: plugin menu items carry the plugin folder name. */
    private array $modules = [];

    /**
     * Add a module. Registering an id again (in any letter case) replaces the earlier definition.
     */
    public function register(Module $module): void
    {
        $this->modules[strtolower($module->id)] = $module;
    }

    /**
     * All registered modules, keyed by lower-cased id, in registration order.
     *
     * @return array<string, Module>
     */
    public function all(): array
    {
        return $this->modules;
    }

    /**
     * The module with this id, in any letter case.
     */
    public function get(string $id): ?Module
    {
        return $this->modules[strtolower($id)] ?? null;
    }

    public function has(string $id): bool
    {
        return isset($this->modules[strtolower($id)]);
    }

    /**
     * The module that owns /{module}/{action}, if any.
     */
    public function forRoute(string $module, string $action): ?Module
    {
        foreach ($this->modules as $candidate) {
            if ($candidate->claimsRoute($module, $action)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * The module that owns a permission domain ("wiki" for wiki.view), if any.
     */
    public function forPermissionDomain(string $permissionDomain): ?Module
    {
        foreach ($this->modules as $candidate) {
            if ($candidate->permissionDomain === $permissionDomain) {
                return $candidate;
            }
        }

        return null;
    }
}

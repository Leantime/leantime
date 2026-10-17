<?php

namespace Leantime\Core\Modules;

use Illuminate\Support\Str;

/**
 * A feature that can be switched on or off per project (Wiki, Ideas, a plugin's board, …).
 *
 * The switch is a blanket one: when a module is off in a project nobody in that project can
 * use it, whatever their role. It is enforced in up to three places:
 *  - the project menu hides items whose `module` key matches {@see $id};
 *  - {@see Middleware\ProjectModuleGate} blocks requests to {@see $routes};
 *  - {@see \Leantime\Core\Auth\Permissions\PermissionService} denies project-scoped
 *    permissions of {@see $permissionDomain} in that project (this covers JSON-RPC and MCP).
 */
final class Module
{
    /**
     * @param  string  $id  Unique module id. Must match the `module` key of its menu items.
     * @param  string  $title  Language key (or plain text) shown in the project settings.
     * @param  string  $description  Language key (or plain text) for the one-line explanation.
     * @param  string  $icon  Font Awesome classes for the settings list.
     * @param  bool  $enabledByDefault  Whether projects without an explicit choice have it on.
     * @param  array<int, string>  $routes  Route patterns that belong to the module: "wiki" claims every
     *                                      /wiki/* (and /hx/wiki/*) request, "files.browse" only that action.
     * @param  string|null  $permissionDomain  Permission domain ("wiki" for wiki.view, wiki.edit, …) denied in
     *                                         projects that have the module off. Leave null when the domain
     *                                         is shared with features that must keep working (Files backs
     *                                         ticket attachments, Blueprints backs every canvas type).
     * @param  string|null  $source  Plugin id for plugin modules, null for core modules.
     */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $description = '',
        public readonly string $icon = 'fa fa-puzzle-piece',
        public readonly bool $enabledByDefault = true,
        public readonly array $routes = [],
        public readonly ?string $permissionDomain = null,
        public readonly ?string $source = null,
    ) {}

    /**
     * Whether a request to /{module}/{action} belongs to this module.
     *
     * Segments are compared the way the Frontcontroller resolves controllers (Str::studly), so
     * /files/show-all, /files/show_all, /files/show%20all and /files/showAll all count as
     * files.showAll.
     */
    public function claimsRoute(string $module, string $action): bool
    {
        $module = self::normalizeSegment($module);
        $action = self::normalizeSegment($action);

        foreach ($this->routes as $route) {
            [$routeModule, $routeAction] = array_pad(explode('.', $route, 2), 2, null);

            if (self::normalizeSegment($routeModule) !== $module) {
                continue;
            }

            if ($routeAction === null || self::normalizeSegment($routeAction) === $action) {
                return true;
            }
        }

        return false;
    }

    private static function normalizeSegment(string $segment): string
    {
        return strtolower(Str::studly($segment));
    }
}

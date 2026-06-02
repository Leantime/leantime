<?php

namespace Leantime\Core\Auth\Permissions;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces #[RequiresPermission] for native Laravel-routed controllers (Blueprints, the
 * relocated image/upload controllers, etc.) — the controllers that do NOT go through
 * Frontcontroller. Applied to all domain/plugin routes by {@see \Leantime\Core\Routing\RouteLoader}.
 *
 * Reads the attribute off the matched route's controller@method via the shared
 * {@see PermissionEnforcer} (injected, not resolved through the app() helper), so the
 * attribute remains the single source of truth — no per-route `can:` duplication. A method
 * without the attribute is a no-op.
 */
class CheckPermissions
{
    public function __construct(private PermissionEnforcer $enforcer) {}

    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();

        if ($route !== null) {
            $controller = $route->getControllerClass();
            $method = $route->getActionMethod();

            // Skip closure routes (no controller) and invokable/closure actions where the
            // "method" resolves to the class name itself.
            if (is_string($controller) && $controller !== '' && is_string($method) && $method !== $controller) {
                $this->enforcer->enforce($controller, $method, $request->all());
            }
        }

        return $next($request);
    }
}

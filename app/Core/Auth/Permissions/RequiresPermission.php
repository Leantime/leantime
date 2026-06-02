<?php

namespace Leantime\Core\Auth\Permissions;

use Attribute;

/**
 * Declares the permission required to invoke a controller action or an `@api`
 * service method.
 *
 * The same declaration is enforced at every entry point to the service layer, all reading
 * it through {@see PermissionEnforcer}:
 *  - Legacy convention routes: {@see \Leantime\Core\Controller\Frontcontroller::executeAction()}.
 *  - Native Laravel routes: the {@see CheckPermissions} middleware.
 *  - JSON-RPC: {@see \Leantime\Domain\Api\Controllers\Jsonrpc::executeApiRequest()} on the
 *    resolved service method (RPC bypasses the controller gate, so this is what secures it).
 *
 * On denial an {@see \Leantime\Core\Exceptions\AuthorizationException} is thrown, which the
 * global handler renders as 403 on the web and `JsonRpcErrorResponse::fromException`
 * maps to RPC error -32001.
 *
 * For project-scoped permissions, set {@see $projectIdParam} to the name of the
 * request/method parameter carrying the project id so the check resolves the role
 * against the entity's project rather than the session project. When omitted, the
 * enforcer falls back to the current session project (`session('currentProject')`).
 *
 * Contextual checks that depend on runtime data (ownership, cross-project ids resolved
 * from a loaded entity) belong in the method body via `$this->authorize(...)`, not here.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class RequiresPermission
{
    /**
     * @param  string  $permission  The required `domain.action` key (use a domain
     *                              permission constant, e.g. `TicketsPermissions::CREATE`).
     * @param  string|null  $projectIdParam  Name of the parameter holding the project id
     *                                       for project-scoped checks; null = session project.
     */
    public function __construct(
        public readonly string $permission,
        public readonly ?string $projectIdParam = null,
    ) {}
}

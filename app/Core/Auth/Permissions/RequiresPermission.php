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
 * How the project scope is resolved (mutually informative):
 *  - `projectIdParam: 'projectId'` — the enforcer reads that request param and runs the
 *    full project-scoped check. Use when the project id is a clean top-level argument.
 *  - `global: true` — a company-wide capability (users/clients/settings); the enforcer
 *    checks against the global role, not a project.
 *  - `entityScoped: true` — the project comes from an entity the method loads itself
 *    (e.g. `$ticket->projectId`), which the enforcer can't see beforehand. The attribute is
 *    then a declared-coverage marker and the method body MUST call
 *    `$this->authorize($perm, $entity->projectId)` to do the precise check.
 *  - none of the above — falls back to the current session project (`session('currentProject')`),
 *    appropriate for session-scoped views.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class RequiresPermission
{
    /**
     * @param  string  $permission  The required `domain.action` key (use a domain
     *                              permission constant, e.g. `TicketsPermissions::CREATE`).
     * @param  string|null  $projectIdParam  Name of the request param holding the project id.
     * @param  bool  $global  Company-wide capability — check the global role, not a project.
     * @param  bool  $entityScoped  Project is derived from an entity the method loads; the
     *                              enforcer defers and the method self-authorizes in its body.
     */
    public function __construct(
        public readonly string $permission,
        public readonly ?string $projectIdParam = null,
        public readonly bool $global = false,
        public readonly bool $entityScoped = false,
    ) {}
}

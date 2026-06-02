<?php

namespace Leantime\Core\Auth\Permissions;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Exceptions\AuthorizationException;
use ReflectionException;
use ReflectionMethod;

/**
 * Reads the {@see RequiresPermission} attribute off a resolved action/method and enforces it.
 *
 * Shared by the entry points so the declaration means the same everywhere:
 *  - {@see \Leantime\Core\Controller\Frontcontroller::executeAction()} — legacy convention routes,
 *  - {@see CheckPermissions} middleware — native Laravel routes,
 *  - {@see \Leantime\Domain\Api\Controllers\Jsonrpc::executeApiRequest()} — JSON-RPC.
 *
 * Safety properties:
 *  - A method WITHOUT the attribute is a complete no-op — it never touches the session, DB,
 *    or permission engine. So wiring the hooks in is inert until methods are annotated.
 *  - Audit mode (the default, `config('permissions.enforce')` falsy) only LOGS would-be
 *    denials instead of blocking, so enforcement can be rolled out and observed per domain
 *    before flipping to blocking.
 */
class PermissionEnforcer
{
    /** @var array<string, RequiresPermission|null> Memoized attribute lookups per class::method. */
    private array $cache = [];

    public function __construct(private PermissionService $permissions) {}

    /**
     * Enforce the permission required by $class::$method, if any.
     *
     * @param  object|class-string  $class  The controller instance or service class name.
     * @param  array<string, mixed>  $params  Request/method parameters (for project-id resolution).
     *
     * @throws AuthorizationException When denied and not in audit mode.
     */
    public function enforce(object|string $class, string $method, array $params = []): void
    {
        $attribute = $this->attributeFor($class, $method);

        if ($attribute === null) {
            return;
        }

        // Entity-scoped: the method loads the entity and authorizes its project in its own
        // body (the enforcer can't see the entity's project here). The attribute is just the
        // declared-coverage marker; defer to the in-method $this->authorize() call.
        if ($attribute->entityScoped) {
            return;
        }

        $allowed = $attribute->global
            ? $this->permissions->currentUserCan($attribute->permission, null, true)
            : $this->permissions->currentUserCan($attribute->permission, $this->resolveProjectId($attribute, $params));

        if ($allowed) {
            return;
        }

        $target = (is_object($class) ? $class::class : $class).'::'.$method;
        $user = session('userdata.id') ?? 'guest';

        if (! $this->shouldBlock()) {
            Log::info(sprintf('[permissions:audit] would deny "%s" on %s for user %s', $attribute->permission, $target, $user));

            return;
        }

        // Log the permission key server-side for audit; the thrown exception stays generic
        // so the authorization vocabulary is never exposed to the client.
        Log::info(sprintf('Authorization denied: "%s" on %s for user %s', $attribute->permission, $target, $user));

        throw new AuthorizationException;
    }

    /**
     * The RequiresPermission attribute on $class::$method, or null. Memoized; tolerant of
     * missing methods (returns null) so it can guard any dispatch target.
     */
    private function attributeFor(object|string $class, string $method): ?RequiresPermission
    {
        $className = is_object($class) ? $class::class : $class;
        $key = $className.'::'.$method;

        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $attribute = null;

        try {
            $attributes = (new ReflectionMethod($className, $method))->getAttributes(RequiresPermission::class);

            if ($attributes !== []) {
                $attribute = $attributes[0]->newInstance();
            }
        } catch (ReflectionException) {
            $attribute = null;
        }

        return $this->cache[$key] = $attribute;
    }

    /**
     * Project id for a project-scoped check: the named parameter if the attribute declares
     * one, else the current session project. Returns null when neither resolves.
     *
     * @param  array<string, mixed>  $params
     */
    private function resolveProjectId(RequiresPermission $attribute, array $params): ?int
    {
        if ($attribute->projectIdParam !== null && isset($params[$attribute->projectIdParam])) {
            return (int) $params[$attribute->projectIdParam];
        }

        $current = session('currentProject');

        return ($current === null || (int) $current === 0) ? null : (int) $current;
    }

    /** Whether denials block (true) or are only logged (false, the default — audit mode). */
    private function shouldBlock(): bool
    {
        try {
            return (bool) config('permissions.enforce', false);
        } catch (\Throwable) {
            return false;
        }
    }
}

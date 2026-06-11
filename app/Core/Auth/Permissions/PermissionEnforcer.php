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

    /** @var array<string, bool> Memoized "is this param mandatory" lookups per class::method::param. */
    private array $mandatoryParamCache = [];

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

        $reason = '';

        if ($attribute->global) {
            $allowed = $this->permissions->currentUserCan($attribute->permission, null, true);
        } else {
            $projectId = $this->resolveProjectId($attribute, $class, $method, $params);

            if ($projectId === false) {
                // A declared projectIdParam that can't be resolved to a concrete project, on a
                // method whose signature makes that param mandatory, fails closed: we cannot
                // identify which project to authorize against, and silently falling back to the
                // session project would authorize the wrong one. Optional params keep the
                // session fallback (see resolveProjectId).
                $allowed = false;
                $reason = sprintf(' (unresolved mandatory project param "%s")', $attribute->projectIdParam);
            } else {
                $allowed = $this->permissions->currentUserCan($attribute->permission, $projectId);
            }
        }

        if ($allowed) {
            return;
        }

        $target = (is_object($class) ? $class::class : $class).'::'.$method;
        $user = session('userdata.id') ?? 'guest';

        if (! $this->shouldBlock()) {
            Log::info(sprintf('[permissions:audit] would deny "%s" on %s for user %s%s', $attribute->permission, $target, $user, $reason));

            return;
        }

        // Log the permission key server-side for audit; the thrown exception stays generic
        // so the authorization vocabulary is never exposed to the client.
        Log::info(sprintf('Authorization denied: "%s" on %s for user %s%s', $attribute->permission, $target, $user, $reason));

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
     * Resolve the project id for a project-scoped check.
     *
     * Three outcomes:
     *  - int   — a concrete project to authorize against (the declared param, or the session
     *            project for attributes that declare no param);
     *  - null  — no concrete project and none required (the session project was empty on an
     *            attribute that allows the fallback): the engine checks capability only;
     *  - false — DENY. The attribute declares a projectIdParam, the value is absent/null/zero,
     *            AND the target method's signature makes that param mandatory (no default).
     *            We can't identify the project and the method can't run without it, so we fail
     *            closed instead of authorizing against the unrelated session project.
     *
     * @param  array<string, mixed>  $params
     */
    private function resolveProjectId(RequiresPermission $attribute, object|string $class, string $method, array $params): int|false|null
    {
        // No declared param: scope to the ambient session project (session-scoped views).
        if ($attribute->projectIdParam === null) {
            return $this->sessionProject();
        }

        $name = $attribute->projectIdParam;

        // Declared param present with a concrete, non-zero value: scope to it.
        if (array_key_exists($name, $params) && $params[$name] !== null && (int) $params[$name] !== 0) {
            return (int) $params[$name];
        }

        // Declared but absent/null/zero. Fail closed only when the method proves the project is
        // mandatory; methods that default the project (e.g. poll/dashboard "current project"
        // endpoints) legitimately mean "the session project" and keep the fallback.
        if ($this->paramIsMandatory($class, $method, $name)) {
            return false;
        }

        return $this->sessionProject();
    }

    /** The current session project as an int, or null when none/zero is set. */
    private function sessionProject(): ?int
    {
        $current = session('currentProject');

        return ($current === null || (int) $current === 0) ? null : (int) $current;
    }

    /**
     * Whether $paramName on $class::$method is mandatory — i.e. has no default value, so a
     * caller cannot legitimately omit it. Mirrors the JSON-RPC dispatcher's own "required"
     * definition ({@see \Leantime\Domain\Api\Controllers\Jsonrpc::prepareParameters()}:
     * `! isDefaultValueAvailable()`) so the two stay in lockstep. Memoized; tolerant of a
     * missing method/param (returns false → keep the session fallback) so it never over-denies
     * a target it can't reflect (e.g. a controller action taking a single $params array).
     */
    private function paramIsMandatory(object|string $class, string $method, string $paramName): bool
    {
        $className = is_object($class) ? $class::class : $class;
        $key = $className.'::'.$method.'::'.$paramName;

        if (array_key_exists($key, $this->mandatoryParamCache)) {
            return $this->mandatoryParamCache[$key];
        }

        $mandatory = false;

        try {
            foreach ((new ReflectionMethod($className, $method))->getParameters() as $param) {
                if ($param->getName() === $paramName) {
                    $mandatory = ! $param->isDefaultValueAvailable();
                    break;
                }
            }
        } catch (ReflectionException) {
            $mandatory = false;
        }

        return $this->mandatoryParamCache[$key] = $mandatory;
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

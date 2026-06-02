<?php

namespace Leantime\Core\Domains;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Exceptions\ValidationException;

/**
 * Base class for domain services, providing the cross-cutting authorization and validation
 * helpers a service needs. All services should extend this (it also carries the
 * {@see DomainService} marker and the {@see DispatchesEvents} trait, so event behavior is
 * unchanged).
 *
 * Dependency wiring is handled by {@see \Leantime\Core\Auth\Permissions\PermissionServiceProvider}:
 * an `afterResolving(BaseService::class, ...)` hook calls {@see setPermissionService()} on
 * every container-resolved subclass. That keeps the engine injected with zero constructor
 * boilerplate in subclasses (which all have their own repo-injecting constructors) and
 * without reaching for the `app()` helper inside service methods.
 */
abstract class BaseService implements DomainService
{
    use DispatchesEvents;

    protected PermissionService $permissions;

    /** Wired by PermissionServiceProvider when the service is resolved from the container. */
    public function setPermissionService(PermissionService $permissions): void
    {
        $this->permissions = $permissions;
    }

    /**
     * Authorize the current user for a `domain.action` permission or throw. Replaces the
     * silent `return false` pattern — a denial becomes an
     * {@see \Leantime\Core\Exceptions\AuthorizationException} (403 web / RPC -32001).
     *
     * @throws \Leantime\Core\Exceptions\AuthorizationException
     */
    protected function authorize(string $permission, ?int $projectId = null, ?bool $forceGlobal = null): void
    {
        $this->permissions->authorize($permission, $projectId, $forceGlobal);
    }

    /** Non-throwing capability check, for branching. */
    protected function can(string $permission, ?int $projectId = null, ?bool $forceGlobal = null): bool
    {
        return $this->permissions->currentUserCan($permission, $projectId, $forceGlobal);
    }

    /** The authenticated user's id, or null when there is no session user. */
    protected function currentUserId(): ?int
    {
        $id = session('userdata.id');

        return ($id === null || $id === '') ? null : (int) $id;
    }

    /**
     * Validate input against Laravel rules, returning the validated (whitelisted) subset or
     * throwing a {@see ValidationException} (422 web / RPC -32602 with field errors). Works
     * identically whether input arrived via a controller or JSON-RPC.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $rules
     * @param  array<string, string>  $messages
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    protected function validate(array $data, array $rules, array $messages = []): array
    {
        return ValidationException::validate($data, $rules, $messages);
    }
}

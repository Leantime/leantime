<?php

namespace Leantime\Core\Auth\Permissions;

use Illuminate\Support\Str;

/**
 * Immutable description of a single capability in the permission vocabulary.
 *
 * A permission is just a named, dotted `domain.action` verb (e.g. `tickets.create`,
 * `company.settings.edit`) plus presentation/scope metadata. Crucially it carries **no
 * role information** — which roles hold a permission is a separate, centrally-managed
 * concern (see {@see DefaultRolePermissions} for the built-in defaults and the
 * `zp_role_permissions` table / admin UI for runtime assignments). A domain declares only
 * *what verbs exist*; it never declares *who gets them*.
 *
 * `projectScoped` distinguishes capabilities evaluated against a specific project's role
 * (most content actions) from company-wide capabilities (user/client management, company
 * settings) that resolve against the global role.
 */
final class Permission
{
    /**
     * @param  string  $key  Dotted `domain.action` identifier, e.g. `tickets.create`.
     * @param  string  $displayName  Human-readable label shown in the role/permission UI.
     * @param  bool  $projectScoped  Whether this capability is evaluated per-project (true)
     *                               or company-wide against the global role (false).
     */
    public function __construct(
        public readonly string $key,
        public readonly string $displayName,
        public readonly bool $projectScoped = true,
    ) {}

    /**
     * The owning domain — the first dotted segment of the key (e.g. `company` for
     * `company.settings.edit`).
     */
    public function domain(): string
    {
        return Str::before($this->key, '.');
    }

    /**
     * The action — everything after the first dotted segment (e.g. `settings.edit`
     * for `company.settings.edit`, `create` for `tickets.create`).
     */
    public function action(): string
    {
        return Str::after($this->key, '.');
    }
}

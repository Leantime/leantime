<?php

namespace Leantime\Domain\Plugins\Permissions;

use Leantime\Core\Auth\Permissions\Permission;
use Leantime\Core\Auth\Permissions\ProvidesPermissions;

/**
 * The Plugins (extension management) permission vocabulary — the verbs only.
 *
 * Managing plugins (installing from the marketplace or a folder, enabling, disabling, updating,
 * removing, and discovering new ones) is an installation-wide administrative capability, not a
 * per-project one. So the single verb below is COMPANY-WIDE (`projectScoped = false`) and call
 * sites gate with `#[RequiresPermission(PluginsPermissions::MANAGE, global: true)]`, evaluated
 * against the user's GLOBAL role. By the default role map it lands on admin/owner only (the
 * `scope:any verbs:['*']` admin rule), matching the existing marketplace/my-apps UI gate.
 *
 * Reading which plugins are enabled (boot, menu, composers) is NOT gated by this — that is
 * internal plumbing every request needs and carries no `plugins.*` requirement.
 */
final class PluginsPermissions implements ProvidesPermissions
{
    /** Install, enable, disable, update, remove, or discover plugins (company-wide). */
    public const MANAGE = 'plugins.manage';

    public function domain(): string
    {
        return 'plugins';
    }

    public function permissions(): array
    {
        return [
            new Permission(self::MANAGE, 'Manage plugins', false),
        ];
    }
}

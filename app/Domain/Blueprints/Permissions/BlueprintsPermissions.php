<?php

namespace Leantime\Domain\Blueprints\Permissions;

use Leantime\Core\Auth\Permissions\Permission;
use Leantime\Core\Auth\Permissions\ProvidesPermissions;

/**
 * The Blueprints (canvas) permission vocabulary — the verbs only.
 *
 * Blueprints is the consolidated canvas system: every canvas variant (SWOT, Lean, Value, …)
 * is a row in the shared `zp_canvas`/`zp_canvas_items` tables, distinguished by a `type`
 * column, and each board belongs to exactly one project. Capabilities are therefore
 * PROJECT-scoped (projectScoped = true, the default) — evaluated against the user's role IN
 * the board's project.
 *
 * One vocabulary covers the whole canvas family (Blueprints + the deprecated Canvas shim, and
 * later Goalcanvas/Logicmodelcanvas): a "canvas" capability is the same regardless of variant.
 *
 * The standard verbs auto-grant via the central matrix (readonly = view; editor =
 * create/edit/delete; manager+ = all), so no {@see \Leantime\Core\Auth\Permissions\DefaultRolePermissions}
 * change is required.
 */
final class BlueprintsPermissions implements ProvidesPermissions
{
    public const VIEW = 'blueprints.view';

    public const CREATE = 'blueprints.create';

    public const EDIT = 'blueprints.edit';

    public const DELETE = 'blueprints.delete';

    public function domain(): string
    {
        return 'blueprints';
    }

    public function permissions(): array
    {
        return [
            new Permission(self::VIEW, 'View canvas boards'),
            new Permission(self::CREATE, 'Create canvas boards and items'),
            new Permission(self::EDIT, 'Edit canvas boards and items'),
            new Permission(self::DELETE, 'Delete canvas boards and items'),
        ];
    }
}

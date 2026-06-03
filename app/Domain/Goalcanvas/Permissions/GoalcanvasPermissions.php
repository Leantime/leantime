<?php

namespace Leantime\Domain\Goalcanvas\Permissions;

use Leantime\Core\Auth\Permissions\Permission;
use Leantime\Core\Auth\Permissions\ProvidesPermissions;

/**
 * The Goalcanvas (Goals / OKRs) permission vocabulary — the verbs only.
 *
 * Goals are a distinct headline feature (OKR-style goal boards), even though they are stored
 * in the shared `zp_canvas` (type `goalcanvas`) / `zp_canvas_items` (box `goal`) tables. They
 * get their own `goals.*` vocabulary — separate from the generic `blueprints.*` strategy
 * canvases — so a role/permission admin can grant goal access independently.
 *
 * Goal boards/items are PROJECT-scoped (each board belongs to one project), so every capability
 * is evaluated against the user's role IN the board's project (projectScoped = true, the
 * default). The standard verbs auto-grant via the central matrix (readonly = view; editor =
 * create/edit/delete; manager+ = all), so no
 * {@see \Leantime\Core\Auth\Permissions\DefaultRolePermissions} change is required.
 */
final class GoalcanvasPermissions implements ProvidesPermissions
{
    public const VIEW = 'goals.view';

    public const CREATE = 'goals.create';

    public const EDIT = 'goals.edit';

    public const DELETE = 'goals.delete';

    public function domain(): string
    {
        return 'goals';
    }

    public function permissions(): array
    {
        return [
            new Permission(self::VIEW, 'View goals'),
            new Permission(self::CREATE, 'Create goals and goal boards'),
            new Permission(self::EDIT, 'Edit goals and goal boards'),
            new Permission(self::DELETE, 'Delete goals and goal boards'),
        ];
    }
}

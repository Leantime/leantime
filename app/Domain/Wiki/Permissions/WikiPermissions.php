<?php

namespace Leantime\Domain\Wiki\Permissions;

use Leantime\Core\Auth\Permissions\Permission;
use Leantime\Core\Auth\Permissions\ProvidesPermissions;

/**
 * The Wiki permission vocabulary — the verbs only.
 *
 * Wiki articles and wikis are PROJECT-scoped (each belongs to one project), so every capability is
 * evaluated against the user's role IN that project (projectScoped = true, the default). The
 * standard verbs auto-grant via the central matrix (readonly = view; editor = create/edit/delete;
 * manager+ = all), so no {@see \Leantime\Core\Auth\Permissions\DefaultRolePermissions} change is
 * required.
 */
final class WikiPermissions implements ProvidesPermissions
{
    public const VIEW = 'wiki.view';

    public const CREATE = 'wiki.create';

    public const EDIT = 'wiki.edit';

    public const DELETE = 'wiki.delete';

    public function domain(): string
    {
        return 'wiki';
    }

    public function permissions(): array
    {
        return [
            new Permission(self::VIEW, 'View wiki articles'),
            new Permission(self::CREATE, 'Create wiki articles'),
            new Permission(self::EDIT, 'Edit wiki articles'),
            new Permission(self::DELETE, 'Delete wiki articles'),
        ];
    }
}

<?php

namespace Leantime\Core\Auth\Permissions;

/**
 * Contract implemented by each domain's (and plugin's) permission catalog.
 *
 * Implementations live at `app/Domain/{Domain}/Permissions/{Domain}Permissions.php`
 * (and the plugin equivalent) and are auto-discovered at boot by
 * {@see PermissionRegistry}, mirroring how `register.php` event listeners are
 * discovered. The declared {@see Permission} objects are the single source of truth
 * for the `domain.action` vocabulary — `permissions:sync` writes them into the
 * `zp_permissions` table so an administrator can assign them to roles.
 *
 * Concrete implementations should also expose typed string constants
 * (e.g. `const CREATE = 'tickets.create';`) so call sites reference constants
 * rather than magic strings.
 */
interface ProvidesPermissions
{
    /**
     * The capabilities this provider contributes to the vocabulary.
     *
     * @return array<int, Permission>
     */
    public function permissions(): array;

    /**
     * The domain key these permissions belong to (e.g. `tickets`). Used for grouping
     * in the admin UI and as the `zp_permissions.domain` column value.
     */
    public function domain(): string;
}

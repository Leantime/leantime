<?php

namespace Leantime\Core\Auth;

use Leantime\Domain\Auth\Models\Roles;

/**
 * Role ceiling for account management: a user may only hand out roles at or below their own
 * GLOBAL role. A manager cannot mint admins or owners, an admin cannot mint owners or take over
 * an owner account.
 *
 * Applies wherever a role is given to an account — user create/invite/edit and API keys — on
 * top of the `users.*` / `api.*` permission checks, which only answer "may this caller create
 * or edit accounts at all?".
 *
 * Without an authenticated caller (CLI, system jobs, the invite onboarding flow) there is no
 * ceiling to apply; every user-reachable entry point is permission-gated and so authenticated.
 */
class RoleCeiling
{
    /**
     * Whether the current user may give $role to an account. An empty role (no role) is always
     * assignable.
     *
     * @param  mixed  $role  Requested role key (50, '50') or role name ('owner').
     */
    public function canAssign(mixed $role): bool
    {
        if ($role === null || $role === '') {
            return true;
        }

        if (! $this->hasAuthenticatedCaller()) {
            return true;
        }

        $callerLevel = $this->callerLevel();
        $requestedLevel = Roles::getRoleLevel($role);

        if ($callerLevel === false || $requestedLevel === false) {
            return false;
        }

        return $requestedLevel <= $callerLevel;
    }

    /**
     * The roles the current user may assign, for role dropdowns.
     *
     * @return array<int, string> Role names keyed by role level.
     */
    public function assignableRoles(): array
    {
        return array_filter(
            Roles::getRoles(),
            fn ($roleName, $roleLevel) => $this->canAssign($roleLevel),
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * Whether an authenticated caller ranks below $role. False without an authenticated caller;
     * true when the caller's role cannot be resolved (fail closed).
     *
     * @param  string  $role  Role name to compare against, e.g. Roles::$admin.
     */
    public function callerIsBelow(string $role): bool
    {
        if (! $this->hasAuthenticatedCaller()) {
            return false;
        }

        $callerLevel = $this->callerLevel();
        $compareLevel = Roles::getRoleLevel($role);

        return $callerLevel === false || $compareLevel === false || $callerLevel < $compareLevel;
    }

    /**
     * Whether a user is authenticated in the current session/request.
     */
    private function hasAuthenticatedCaller(): bool
    {
        return ! empty(session('userdata.id'));
    }

    /**
     * The current user's global role level, or false when it cannot be resolved.
     */
    private function callerLevel(): int|false
    {
        return Roles::getRoleLevel(session('userdata.role') ?? '');
    }
}

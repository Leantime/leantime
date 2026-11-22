<?php

namespace Leantime\Domain\Reminders\Services;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Reminders\Repositories\Reminders as ReminderRepository;

/**
 * Whether a person who isn't signed in (the scheduler has no session) holds a permission in a
 * project: their effective role there, then that role's permissions. Same resolution as the
 * "assigned to you" notification (AssignmentNotifier::canViewTodos()): manager and above keep
 * their company role; below that an assignable project role wins.
 *
 * Seeing the project (team, company-wide role, shared with everyone) is checked separately;
 * this answers the role question, e.g. a team member whose role lacks tickets.view.
 */
class RecipientAccess
{
    /** @var array<string, bool> "userId|projectId|permission" → allowed */
    private array $cache = [];

    public function __construct(
        private ReminderRepository $reminders,
        private PermissionService $permissions,
    ) {}

    public function can(int $userId, int $projectId, string $permission): bool
    {
        return $this->cache[$userId.'|'.$projectId.'|'.$permission] ??= $this->resolve($userId, $projectId, $permission);
    }

    private function resolve(int $userId, int $projectId, string $permission): bool
    {
        $roles = $this->reminders->rolesIn($userId, $projectId);
        if ($roles === null) {
            return false;
        }

        $globalRole = Roles::getRoleString(ctype_digit($roles['role']) ? (int) $roles['role'] : $roles['role']);
        if ($globalRole === false) {
            return false;
        }

        $role = $globalRole;
        if (! Roles::isAtLeast($globalRole, Roles::$manager)) {
            $projectRole = $roles['projectRole'];
            if (ctype_digit($projectRole) && isset(Roles::getProjectAssignableRoles()[(int) $projectRole])) {
                $role = Roles::getProjectAssignableRoles()[(int) $projectRole];
            } elseif ($projectRole !== '' && in_array($projectRole, Roles::getRoles(), true)) {
                $role = $projectRole;
            }
        }

        return $this->permissions->roleHasPermission($role, $permission);
    }
}

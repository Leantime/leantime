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

    /** @var array<int, string|false> user id → company role name (false: unknown person or role) */
    private array $companyRoles = [];

    /** @var array<string, string> "userId|projectId" → effective role name */
    private array $effectiveRoles = [];

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
        $role = $this->effectiveRole($userId, $projectId);

        return $role !== false && $this->permissions->roleHasPermission($role, $permission);
    }

    /**
     * The person's role in the project. One query per person for the company role, and one per
     * project only for people below manager, whose project role can change it.
     */
    private function effectiveRole(int $userId, int $projectId): string|false
    {
        $globalRole = $this->companyRoles[$userId] ??= $this->companyRole($userId);
        if ($globalRole === false) {
            return false;
        }
        if (Roles::isAtLeast($globalRole, Roles::$manager)) {
            return $globalRole;
        }

        return $this->effectiveRoles[$userId.'|'.$projectId] ??= $this->withProjectRole($globalRole, $this->reminders->projectRole($userId, $projectId));
    }

    private function companyRole(int $userId): string|false
    {
        $stored = $this->reminders->userRole($userId);
        if ($stored === null) {
            return false;
        }

        $role = Roles::getRoleString(ctype_digit($stored) ? (int) $stored : $stored);

        return is_string($role) ? $role : false;
    }

    private function withProjectRole(string $globalRole, string $projectRole): string
    {
        if (ctype_digit($projectRole) && isset(Roles::getProjectAssignableRoles()[(int) $projectRole])) {
            return Roles::getProjectAssignableRoles()[(int) $projectRole];
        }
        if ($projectRole !== '' && in_array($projectRole, Roles::getRoles(), true)) {
            return $projectRole;
        }

        return $globalRole;
    }
}

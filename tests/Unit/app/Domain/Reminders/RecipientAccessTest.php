<?php

namespace Unit\app\Domain\Reminders;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Domain\Reminders\Repositories\Reminders as ReminderRepository;
use Leantime\Domain\Reminders\Services\RecipientAccess;
use Unit\TestCase;

/**
 * The scheduler has no session, so a summary's recipient is checked by their own effective role
 * in the project: manager and above keep their company role, below that an assignable project
 * role wins (as for the "assigned to you" notification).
 */
class RecipientAccessTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;
    use ReminderDatabase;

    /** @var array<int, string> roles asked about */
    private array $asked = [];

    private function access(array $roles): RecipientAccess
    {
        return new RecipientAccess(
            $this->make(ReminderRepository::class, ['rolesIn' => fn (int $userId, int $projectId) => $roles[$userId] ?? null]),
            $this->make(PermissionService::class, [
                'roleHasPermission' => function (string $role, string $permission) {
                    $this->asked[] = $role;

                    return $permission === 'tickets.view' && $role !== 'commenter';
                },
            ]),
        );
    }

    public function test_the_effective_role_decides(): void
    {
        $access = $this->access([
            1 => ['role' => '20', 'projectRole' => ''],    // editor, plain member
            2 => ['role' => '20', 'projectRole' => '10'],  // editor, commenter in this project
            3 => ['role' => '40', 'projectRole' => '10'],  // admin keeps admin
            4 => ['role' => '999', 'projectRole' => ''],   // unknown role
        ]);

        $this->assertTrue($access->can(1, 5, 'tickets.view'));
        $this->assertFalse($access->can(2, 5, 'tickets.view'), 'the project role lowers an editor');
        $this->assertTrue($access->can(3, 5, 'tickets.view'), 'manager and above keep their company role');
        $this->assertFalse($access->can(4, 5, 'tickets.view'));
        $this->assertFalse($access->can(5, 5, 'tickets.view'), 'someone who doesn\'t exist');
        $this->assertSame(['editor', 'commenter', 'admin'], $this->asked);
    }

    public function test_roles_are_read_from_the_user_and_the_team_without_a_session(): void
    {
        $this->createReminderDatabase();
        $this->addUser(7, role: '20');
        $this->addMember(7, 5, projectRole: '10');

        $this->assertSame(['role' => '20', 'projectRole' => '10'], $this->repository()->rolesIn(7, 5));
        $this->assertSame(['role' => '20', 'projectRole' => ''], $this->repository()->rolesIn(7, 6), 'not on that team');
        $this->assertNull($this->repository()->rolesIn(8, 5));
    }

    public function test_answers_are_remembered_per_person_project_and_permission(): void
    {
        $access = $this->access([1 => ['role' => '20', 'projectRole' => '']]);

        $access->can(1, 5, 'tickets.view');
        $access->can(1, 5, 'tickets.view');
        $access->can(1, 5, 'projects.view');

        $this->assertCount(2, $this->asked);
    }
}

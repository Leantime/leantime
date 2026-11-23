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

    /** @var array<int, string> database lookups made */
    private array $queries = [];

    private function access(array $roles): RecipientAccess
    {
        $this->queries = [];

        return new RecipientAccess(
            $this->make(ReminderRepository::class, [
                'userRole' => function (int $userId) use ($roles) {
                    $this->queries[] = 'user '.$userId;

                    return $roles[$userId]['role'] ?? null;
                },
                'projectRole' => function (int $userId, int $projectId) use ($roles) {
                    $this->queries[] = 'team '.$userId.'|'.$projectId;

                    return $roles[$userId]['projectRole'] ?? '';
                },
            ]),
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

        $this->assertSame('20', $this->repository()->userRole(7));
        $this->assertNull($this->repository()->userRole(8));
        $this->assertSame('10', $this->repository()->projectRole(7, 5));
        $this->assertSame('', $this->repository()->projectRole(7, 6), 'not on that team');
    }

    public function test_answers_are_remembered_per_person_project_and_permission(): void
    {
        $access = $this->access([1 => ['role' => '20', 'projectRole' => ''], 2 => ['role' => '40', 'projectRole' => '']]);

        $access->can(1, 5, 'tickets.view');
        $access->can(1, 5, 'tickets.view');
        $access->can(1, 5, 'projects.view');
        $access->can(1, 6, 'tickets.view');
        $access->can(2, 5, 'tickets.view');
        $access->can(2, 6, 'tickets.view');

        $this->assertCount(5, $this->asked, 'one permission answer per person, project and permission');
        $this->assertSame(['user 1', 'team 1|5', 'team 1|6', 'user 2'], $this->queries, 'a company role once per person; a team role only below manager, once per project');
    }

    public function test_the_builder_keeps_one_checker_for_its_run(): void
    {
        $builder = new \Leantime\Domain\Reminders\Services\SummaryBuilder(
            $this->makeEmpty(ReminderRepository::class),
            $this->makeEmpty(\Leantime\Domain\Tickets\Repositories\Tickets::class),
            $this->makeEmpty(\Leantime\Domain\Projects\Repositories\Projects::class),
            $this->makeEmpty(\Leantime\Core\Http\TrustedAppUrl::class),
        );
        $made = 0;
        app()->bind(RecipientAccess::class, function () use (&$made) {
            $made++;

            return $this->access([1 => ['role' => '20', 'projectRole' => '']]);
        });

        $mayView = new \ReflectionMethod($builder, 'mayView');
        foreach ([5, 5, 6, 5] as $projectId) {
            $mayView->invoke($builder, 1, $projectId, 'tickets.view');
        }

        $this->assertSame(1, $made);
        $this->assertSame(['user 1', 'team 1|5', 'team 1|6'], $this->queries);
        app()->offsetUnset(RecipientAccess::class);
    }
}

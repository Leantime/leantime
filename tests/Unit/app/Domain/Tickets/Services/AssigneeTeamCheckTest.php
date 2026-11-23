<?php

namespace Unit\app\Domain\Tickets\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Events\EventDispatcher;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Core\Support\DateTimeHelper;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Comments\Services\Comments as CommentService;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Sprints\Services\Sprints as SprintService;
use Leantime\Domain\Tickets\Exceptions\AssigneeCannotSeeProjectException;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Tickets\Repositories\TicketHistory;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Tickets\Services\Tickets as TicketsService;
use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\TestCase;

/**
 * A task can only be assigned to (or shared with) someone who can see its project. Someone
 * outside the team can't open the task and is never told about it.
 *
 * Project 7 ("Customer Service") has users 1 (the caller) and 2 on its team; user 64 exists but
 * isn't on it; user 999 doesn't exist.
 */
class AssigneeTeamCheckTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private const TEAM = [1, 2];

    private const STRINGS = [
        'notifications.assignee_not_on_project_team' => "%1\$s can't see the %2\$s project because they aren't on its team. Add them to the team first, or pick someone who is on it.",
        'notifications.collaborator_not_on_project_team' => "%1\$s can't see the %2\$s project because they aren't on its team. Add them to the team first, or leave them off.",
        'notifications.ticket_user_not_found' => 'There is no user with ID %s.',
        'notifications.ticket_user_id_invalid' => "'%s' is not a user ID. Use a user's ID number, or 0 for no one.",
    ];

    /** @var array<int, array<string, mixed>> Every patchTicket() write that reached the repository. */
    private array $patches = [];

    /** @var array<int, array<string, mixed>> Every addTicket() write that reached the repository. */
    private array $adds = [];

    /** @var array<int, array<string, mixed>> Every updateTicket() write that reached the repository. */
    private array $updates = [];

    /** @var array<int, array<int, mixed>> The collaborator lists patch() wrote. */
    private array $collaboratorWrites = [];

    private array $dispatcherSnapshot = [];

    private const DISPATCHER_PROPS = [
        'eventRegistry',
        'filterRegistry',
        'available_hooks',
        'patternMatchCache',
        'compiledPatternCache',
        'eventRegistryVersion',
        'filterRegistryVersion',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        session(['usersettings.timezone' => 'UTC']);
        session(['usersettings.language' => 'en-US']);
        session(['usersettings.date_format' => 'Y-m-d']);
        session(['usersettings.time_format' => 'H:i']);
        session(['userdata' => ['id' => 1, 'role' => 'editor', 'name' => 'Caller']]);
        session(['currentProject' => 7]);

        app()->instance(EnvironmentCore::class, $this->make(EnvironmentCore::class, [
            'defaultTimezone' => 'UTC',
            'language' => 'en-US',
        ]));
        app()->instance(LanguageCore::class, $this->language());
        app()->instance(UserRepository::class, $this->make(UserRepository::class, [
            'getUser' => fn ($id) => match ((int) $id) {
                1 => ['id' => 1, 'firstname' => 'Gloria', 'lastname' => 'F'],
                2 => ['id' => 2, 'firstname' => 'Ana', 'lastname' => 'B'],
                64 => ['id' => 64, 'firstname' => 'Marcel', 'lastname' => 'H'],
                default => false,
            },
        ]));

        CarbonImmutable::mixin(new CarbonMacros('UTC', 'en-US', 'Y-m-d', 'H:i'));

        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach (self::DISPATCHER_PROPS as $prop) {
            $this->dispatcherSnapshot[$prop] = $reflection->getProperty($prop)->getValue();
        }

        $this->patches = [];
        $this->adds = [];
        $this->updates = [];
        $this->collaboratorWrites = [];
    }

    protected function tearDown(): void
    {
        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach ($this->dispatcherSnapshot as $prop => $value) {
            $reflection->getProperty($prop)->setValue(null, $value);
        }

        parent::tearDown();
    }

    public function test_assigning_someone_outside_the_team_is_refused_and_nothing_is_written(): void
    {
        try {
            $this->buildService()->patchTicket(5, ['editorId' => 64]);
            $this->fail('Expected the assignment to be refused.');
        } catch (AssigneeCannotSeeProjectException $e) {
            $this->assertSame('editorId', $e->field);
            $this->assertSame(64, $e->userId);
            $this->assertSame('Marcel H', $e->userName);
            $this->assertSame(7, $e->projectId);
            $this->assertSame('Customer Service', $e->projectName);
            $this->assertSame(
                "Marcel H can't see the Customer Service project because they aren't on its team. Add them to the team first, or pick someone who is on it.",
                $e->getClientMessage(),
            );
        }

        $this->assertSame([], $this->patches);
    }

    public function test_assigning_a_team_member_or_unassigning_is_allowed(): void
    {
        $service = $this->buildService();

        $this->assertTrue($service->patchTicket(5, ['editorId' => 2]));
        $this->assertTrue($service->patchTicket(5, ['editorId' => 0]));
        $this->assertTrue($service->patchTicket(5, ['editorId' => '']));
        $this->assertCount(3, $this->patches);
    }

    public function test_an_old_task_already_assigned_outside_the_team_still_saves(): void
    {
        $service = $this->buildService(storedAssignee: 64, storedCollaborators: [64]);

        // Re-sending the same assignee, or editing something else, is not a new assignment.
        $this->assertTrue($service->patchTicket(5, ['editorId' => 64, 'headline' => 'Renamed']));
        $this->assertTrue($service->patchTicket(5, ['headline' => 'Renamed again']));
        $this->assertTrue($service->patchTicket(5, ['collaborators' => [64, 2]]));
    }

    public function test_adding_a_collaborator_outside_the_team_is_refused(): void
    {
        try {
            $this->buildService()->patchTicket(5, ['collaborators' => [2, 64]]);
            $this->fail('Expected the collaborator to be refused.');
        } catch (AssigneeCannotSeeProjectException $e) {
            $this->assertSame('collaborators', $e->field);
            $this->assertSame(64, $e->userId);
            $this->assertStringEndsWith('or leave them off.', $e->getClientMessage());
        }
    }

    public function test_an_unchanged_outside_assignee_is_not_added_as_a_collaborator(): void
    {
        // An old task assigned outside the team keeps its assignee, but that doesn't make the
        // outsider a new collaborator: the collaborator relationship is new and is checked.
        $service = $this->buildService(storedAssignee: 64);

        try {
            $service->patchTicket(5, ['editorId' => 64, 'collaborators' => [64]]);
            $this->fail('Expected the collaborator to be refused.');
        } catch (AssigneeCannotSeeProjectException $e) {
            $this->assertSame('collaborators', $e->field);
            $this->assertSame(64, $e->userId);
        }

        $this->assertSame([], $this->patches);
    }

    public function test_a_new_team_assignee_can_also_be_a_collaborator(): void
    {
        $this->assertTrue($this->buildService()->patchTicket(5, ['editorId' => 2, 'collaborators' => [2]]));
    }

    public function test_a_user_id_that_does_not_exist_is_named(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('There is no user with ID 999.');

        $this->buildService()->patchTicket(5, ['editorId' => 999]);
    }

    /**
     * The repository writes the value as sent, so anything but a whole, non-negative id must be
     * refused rather than cast (true would be stored as user 1, 2.5 checked as user 2).
     */
    public function test_malformed_assignee_ids_are_refused_not_cast(): void
    {
        $service = $this->buildService();

        foreach ([[true, "'true' is not a user ID."], [2.5, "'2.5' is not a user ID."], [-3, "'-3' is not a user ID."], ['2abc', "'2abc' is not a user ID."], ['02', "'02' is not a user ID."], [[2], "'array' is not a user ID."]] as [$value, $start]) {
            try {
                $service->patchTicket(5, ['editorId' => $value]);
                $this->fail('Expected '.var_export($value, true).' to be refused.');
            } catch (ValidationException $e) {
                $this->assertNotInstanceOf(AssigneeCannotSeeProjectException::class, $e);
                $this->assertStringStartsWith($start, $e->getClientMessage());
            }
        }

        $this->assertSame([], $this->patches);
        $this->assertTrue($service->patchTicket(5, ['editorId' => '2']));
        $this->assertTrue($service->patchTicket(5, ['editorId' => '0']));
        $this->assertTrue($service->patchTicket(5, ['editorId' => null]));
    }

    public function test_malformed_collaborator_ids_are_refused(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("'1.5' is not a user ID.");

        $this->buildService()->patchTicket(5, ['collaborators' => [2, 1.5]]);
    }

    public function test_collaborators_as_a_comma_list_are_checked_too(): void
    {
        $this->expectException(AssigneeCannotSeeProjectException::class);

        $this->buildService()->patchTicket(5, ['collaborators' => '2, 64']);
    }

    /**
     * A comma list was only parsed for the check: patch() then cleared the collaborators and
     * the create paths handed the repository a string. The parsed ids are what gets written.
     */
    public function test_a_comma_list_of_collaborators_is_saved_on_every_path(): void
    {
        $service = $this->buildService();

        $this->assertTrue($service->patchTicket(5, ['collaborators' => '2, 1']));
        $this->assertSame([[2, 1]], $this->collaboratorWrites);

        $this->assertSame(77, $service->quickAddTicket(['headline' => 'Quick', 'projectId' => 7, 'collaborators' => '2,1']));
        $this->assertSame([2, 1], $this->adds[0]['collaborators']);

        $this->assertSame(77, $service->addTicket(['headline' => 'Full', 'projectId' => 7, 'editorId' => 1, 'collaborators' => '2']));
        $this->assertSame([2], $this->adds[1]['collaborators']);

        $this->assertTrue($service->updateTicket(['id' => 5, 'headline' => 'Edited', 'collaborators' => '2, 1,']));
        $this->assertSame([2, 1], $this->updates[0]['collaborators']);
    }

    public function test_quick_add_refuses_an_outside_assignee_and_defaults_to_the_caller(): void
    {
        $service = $this->buildService();

        try {
            $service->quickAddTicket(['headline' => 'Reply to customer', 'projectId' => 7, 'editorId' => 64]);
            $this->fail('Expected the assignment to be refused.');
        } catch (AssigneeCannotSeeProjectException $e) {
            $this->assertSame(64, $e->userId);
        }
        $this->assertSame([], $this->adds);

        $this->assertSame(77, $service->quickAddTicket(['headline' => 'Reply to customer', 'projectId' => 7]));
        $this->assertSame(1, $this->adds[0]['editorId']);
    }

    public function test_the_board_quick_add_shows_the_refusal_instead_of_failing(): void
    {
        $result = $this->buildService()->quickAddTicketFromKanban(
            ['headline' => 'Reply to customer', 'projectId' => 7, 'status' => 3],
            '64',
            'editorId',
        );

        $this->assertFalse($result['success']);
        $this->assertStringStartsWith("Marcel H can't see the Customer Service project", $result['message']);
        $this->assertSame([], $this->adds);
    }

    public function test_the_board_quick_add_shows_an_unknown_user_the_same_way(): void
    {
        $result = $this->buildService()->quickAddTicketFromKanban(
            ['headline' => 'Reply to customer', 'projectId' => 7, 'status' => 3],
            '999',
            'editorId',
        );

        $this->assertFalse($result['success']);
        $this->assertSame('There is no user with ID 999.', $result['message']);
        $this->assertSame('There is no user with ID 999.', session('quickadd_reopen')['error']);
        $this->assertSame([], $this->adds);
    }

    public function test_add_ticket_refuses_an_outside_collaborator(): void
    {
        $this->expectException(AssigneeCannotSeeProjectException::class);

        $this->buildService()->addTicket([
            'headline' => 'Reply to customer',
            'projectId' => 7,
            'editorId' => 2,
            'collaborators' => [64],
        ]);
    }

    private function language(): LanguageCore
    {
        return $this->make(LanguageCore::class, ['__' => fn ($key) => self::STRINGS[$key] ?? $key]);
    }

    /**
     * A service whose tickets all live in project 7; writes always succeed.
     *
     * @param  array<int, int>  $storedCollaborators
     */
    private function buildService(int $storedAssignee = 1, array $storedCollaborators = []): TicketsService
    {
        $ticketRepository = $this->make(TicketRepository::class, [
            'getTicket' => fn ($id) => $this->make(TicketModel::class, [
                'id' => (int) $id,
                'projectId' => 7,
                'headline' => 'T'.$id,
                'type' => 'task',
                'status' => 3,
                'editorId' => $storedAssignee,
                'collaborators' => $storedCollaborators,
                'dependingTicketId' => 0,
            ]),
            'patchTicket' => function ($id, $values) {
                $this->patches[] = $values;

                return true;
            },
            'addTicket' => function ($values) {
                $this->adds[] = $values;

                return 77;
            },
            'updateTicket' => function ($values) {
                $this->updates[] = $values;

                return true;
            },
            'removeCollaborators' => fn () => true,
            'addCollaborators' => function ($id, $collaborators) {
                $this->collaboratorWrites[] = $collaborators;

                return true;
            },
            'getStateLabels' => fn () => [
                0 => ['name' => 'Done', 'statusType' => 'DONE'],
                3 => ['name' => 'New', 'statusType' => 'NEW'],
            ],
        ]);

        $service = new TicketsService(
            language: $this->language(),
            ticketRepository: $ticketRepository,
            timesheetsRepo: $this->make(TimesheetRepository::class),
            settingsRepo: $this->make(SettingRepository::class),
            projectService: $this->make(ProjectService::class, [
                'isUserAssignedToProject' => fn ($userId, $projectId) => (int) $projectId === 7 && in_array((int) $userId, self::TEAM, true),
                'getProject' => fn ($id) => ['id' => (int) $id, 'name' => 'Customer Service'],
                'getProjectRole' => fn () => '',
                'notifyProjectUsers' => fn () => null,
            ]),
            timesheetService: $this->make(TimesheetService::class, ['isClocked' => fn () => false]),
            sprintService: $this->make(SprintService::class),
            ticketHistoryRepo: $this->make(TicketHistory::class),
            goalcanvasService: $this->make(Goalcanvas::class),
            dateTimeHelper: $this->make(DateTimeHelper::class, ['userNow' => fn () => CarbonImmutable::now('UTC')]),
            commentService: $this->make(CommentService::class),
            clientService: $this->make(ClientService::class),
            projectModules: $this->allModulesOn(),
        );
        $service->setPermissionService($this->make(PermissionService::class, [
            'currentUserCan' => fn () => true,
            'authorize' => fn () => null,
        ]));

        return $service;
    }
}

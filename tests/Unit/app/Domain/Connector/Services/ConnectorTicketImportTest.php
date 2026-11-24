<?php

namespace Unit\app\Domain\Connector\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Language;
use Leantime\Domain\Connector\Models\ImportResult;
use Leantime\Domain\Connector\Services\Connector;
use Leantime\Domain\Connector\Services\ReviewedRows;
use Leantime\Domain\Connector\Services\ReviewFixes;
use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas;
use Leantime\Domain\Ideas\Repositories\Ideas;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Users\Services\Users;
use Unit\TestCase;

/**
 * To-do imports: what is sent to the ticket service per row, and how failed rows are reported.
 */
class ConnectorTicketImportTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * A Language stub that returns "key|arg1|..." once sprintf'd, so messages can be asserted
     * by key and arguments. One-argument messages get one placeholder (sprintf throws on
     * missing arguments).
     */
    private function language(): Language
    {
        $noArgument = ['notification.import_choose_project'];

        $oneArgument = ['notification.import_row_save_uncertain', 'notification.import_row_id_not_found', 'notification.import_column_required', 'notification.import_row_project_empty'];

        $twoArguments = ['notification.import_mapped_twice', 'notification.import_row_unknown_email', 'notification.import_row_unknown_person', 'notification.import_row_person_ambiguous', 'notification.import_row_unknown_project'];

        return $this->make(Language::class, [
            // A cell message takes the same arguments as its row message.
            '__' => fn (string $index, string $default = '') => match (true) {
                in_array($index, $noArgument, true) => $index,
                in_array(str_replace('import_cell_', 'import_row_', $index), $oneArgument, true) => $index.'|%1$s',
                in_array(str_replace('import_cell_', 'import_row_', $index), $twoArguments, true) => $index.'|%1$s|%2$s',
                default => $index.'|%1$s|%2$s|%3$s',
            },
        ]);
    }

    /**
     * A stored to-do or milestone.
     */
    private function ticket(int $id, string $type): TicketModel
    {
        $ticket = new TicketModel;
        $ticket->id = $id;
        $ticket->type = $type;

        return $ticket;
    }

    /**
     * @param  array<int, array{0: string, 1: array<string, mixed>}>  $calls
     * @param  array<string, mixed>  $ticketOverrides  Extra stubbed Tickets service methods.
     * @param  array<string, mixed>  $projectOverrides  Stubbed Projects service methods.
     */
    private function connector(array &$calls, array $ticketOverrides = [], array $projectOverrides = [], array $userOverrides = [], array $goalOverrides = [], array $ideaOverrides = [], array $ticketRepoOverrides = []): Connector
    {
        $tickets = $this->make(Tickets::class, array_merge([
            'updateTicket' => function ($values) use (&$calls) {
                $calls[] = ['update', $values];

                return true;
            },
            'addTicket' => function ($values) use (&$calls) {
                $calls[] = ['add', $values];

                return 1;
            },
            'getTicketTypes' => fn () => ['task', 'subtask', 'story', 'bug'],
            // Every Id names a to-do the person can see, unless a test says otherwise.
            'getTicket' => fn ($id) => $this->ticket((int) $id, 'task'),
        ], $ticketOverrides));

        return new Connector(
            $this->make(Users::class, $userOverrides),
            $this->make(Projects::class, $projectOverrides),
            $tickets,
            $this->make(TicketRepository::class, $ticketRepoOverrides),
            $this->make(Goalcanvas::class, $goalOverrides),
            $this->make(Ideas::class, $ideaOverrides),
            $this->language(),
        );
    }

    public function test_unresolved_status_is_omitted_and_resolved_status_is_kept(): void
    {
        $calls = [];
        $fields = [
            ['sourceField' => 'ID', 'leantimeField' => 'id'],
            ['sourceField' => 'Title', 'leantimeField' => 'headline'],
            ['sourceField' => 'State', 'leantimeField' => 'status'],
            ['sourceField' => 'Due', 'leantimeField' => 'dateToFinish'],
            ['sourceField' => 'Created', 'leantimeField' => 'date'],
        ];
        $base = ['Due' => '', 'Created' => '', 'projectId' => 9, 'editorId' => 1];
        $values = [
            // Existing ticket, status name did not resolve (parseTickets left it null).
            $base + ['ID' => '5', 'Title' => 'Existing', 'State' => 'Unknown', 'status' => null],
            // New ticket, status did not resolve.
            $base + ['ID' => '', 'Title' => 'New', 'State' => 'Unknown', 'status' => null],
            // Existing ticket with a resolved status.
            $base + ['ID' => '6', 'Title' => 'Resolved', 'State' => 'Doing', 'status' => 11],
        ];

        $result = $this->connector($calls)->importValues($fields, $values, 'tickets');

        $this->assertInstanceOf(ImportResult::class, $result);
        $this->assertTrue($result->isSuccessful());
        $this->assertSame(3, $result->imported);
        $this->assertSame('update', $calls[0][0]);
        $this->assertArrayNotHasKey('status', $calls[0][1], 'the stored status of an existing ticket is kept');
        $this->assertSame('add', $calls[1][0]);
        $this->assertArrayNotHasKey('status', $calls[1][1], 'a new ticket gets its project NEW status');
        $this->assertArrayNotHasKey('id', $calls[1][1], 'a blank id is not sent when adding');
        $this->assertSame(11, $calls[2][1]['status']);
    }

    public function test_mapped_type_is_used_and_defaults_to_task(): void
    {
        $calls = [];
        $fields = [
            ['sourceField' => 'Summary', 'leantimeField' => 'headline'],
            ['sourceField' => 'Issue Type', 'leantimeField' => 'type'],
        ];
        $values = [
            ['Summary' => 'A bug', 'Issue Type' => 'Bug', 'projectId' => 9, 'editorId' => 1],
            ['Summary' => 'No type', 'Issue Type' => '', 'projectId' => 9, 'editorId' => 1],
        ];

        $this->connector($calls)->importValues($fields, $values, 'tickets');

        $this->assertSame('bug', $calls[0][1]['type']);
        $this->assertSame('task', $calls[1][1]['type']);
    }

    public function test_work_dates_come_from_their_own_columns_and_unmapped_dates_are_left_out(): void
    {
        $calls = [];
        $mapped = [
            ['sourceField' => 'Title', 'leantimeField' => 'headline'],
            ['sourceField' => 'Due', 'leantimeField' => 'dateToFinish'],
            ['sourceField' => 'Start', 'leantimeField' => 'editFrom'],
            ['sourceField' => 'End', 'leantimeField' => 'editTo'],
        ];
        $values = [
            ['Title' => 'Planned', 'Due' => '2026-12-05', 'Start' => '2026-11-25T09:00:00Z', 'End' => ' ', 'projectId' => 9, 'editorId' => 1],
            ['Title' => 'Whole days', 'Due' => '', 'Start' => '2026-11-25', 'End' => '2026-11-26', 'projectId' => 9, 'editorId' => 1],
            ['Title' => 'Compact', 'Due' => '', 'Start' => '20261125T090000Z', 'End' => '', 'projectId' => 9, 'editorId' => 1],
        ];

        $this->connector($calls)->importValues($mapped, $values, 'tickets');

        $start = $calls[0][1]['editFrom'];
        $this->assertInstanceOf(CarbonImmutable::class, $start, 'a cell with a time keeps it');
        $this->assertSame('2026-11-25 09:00', $start->setTimezone('UTC')->format('Y-m-d H:i'), 'Edit From is its own value, not the due date');
        $this->assertArrayNotHasKey('editTo', $calls[0][1], 'a blank Edit To is left out');

        // Date-only cells go to the ticket service as dates, so it applies start / end of day
        // instead of the current time of day.
        $this->assertIsString($calls[1][1]['editFrom']);
        $this->assertSame(dtHelper()->parseUserDateTime('2026-11-26')->formatDateForUser(), $calls[1][1]['editTo']);

        $compact = $calls[2][1]['editFrom'];
        $this->assertInstanceOf(CarbonImmutable::class, $compact, 'a compact ISO 8601 time (20261125T090000Z) is a time too');
        $this->assertTrue($compact->eq(dtHelper()->parseUserDateTime('20261125T090000Z')), 'the parsed time is passed on, not a day boundary');

        $calls = [];
        $this->connector($calls)->importValues(
            [['sourceField' => 'ID', 'leantimeField' => 'id'], ['sourceField' => 'Title', 'leantimeField' => 'headline']],
            [['ID' => '12', 'Title' => 'Renamed', 'projectId' => 9, 'editorId' => 1]],
            'tickets'
        );

        $this->assertSame('update', $calls[0][0]);
        foreach (['dateToFinish', 'editFrom', 'editTo'] as $unmapped) {
            $this->assertArrayNotHasKey($unmapped, $calls[0][1], "an unmapped {$unmapped} keeps the stored value");
        }
    }

    public function test_failed_rows_are_reported_with_their_row_number_and_do_not_stop_the_import(): void
    {
        $calls = [];
        $connector = $this->connector($calls, [
            'addTicket' => function ($values) use (&$calls) {
                $calls[] = ['add', $values];

                return match ($values['headline']) {
                    '' => ['msg' => 'notifications.ticket_save_error_no_headline', 'type' => 'error'],
                    'Throws' => throw new \RuntimeException('Ben Ortiz is not on the team'),
                    'False' => false,
                    default => 7,
                };
            },
        ]);

        $fields = [['sourceField' => 'Title', 'leantimeField' => 'headline']];
        $values = [
            ['Title' => 'Fine', 'projectId' => 9, 'editorId' => 1],
            ['Title' => '', 'projectId' => 9, 'editorId' => 1],
            ['Title' => 'Throws', 'projectId' => 9, 'editorId' => 1],
            ['Title' => 'False', 'projectId' => 9, 'editorId' => 1],
            ['Title' => 'Also fine', 'projectId' => 9, 'editorId' => 1],
        ];

        $result = $connector->importValues($fields, $values, 'tickets');

        $this->assertCount(5, $calls, 'every row is attempted');
        $this->assertSame(2, $result->imported);
        $this->assertSame(3, $result->failedCount());
        $this->assertSame(3, $result->failures[0]['row'], 'the second record is spreadsheet row 3');
        $this->assertStringStartsWith('notifications.ticket_save_error_no_headline', $result->failures[0]['message']);
        $this->assertSame(4, $result->failures[1]['row']);
        $this->assertSame('notification.import_row_save_uncertain|Ben Ortiz is not on the team', $result->failures[1]['message'], 'a thrown error may come after the save, so the person is told to check first');
        $this->assertSame(5, $result->failures[2]['row']);
        $this->assertStringStartsWith('notification.import_row_not_saved', $result->failures[2]['message']);
    }

    public function test_update_errors_are_reported(): void
    {
        $calls = [];
        $connector = $this->connector($calls, [
            'updateTicket' => fn () => ['msg' => 'notifications.ticket_save_error_no_access', 'type' => 'error'],
        ]);

        $result = $connector->importValues(
            [['sourceField' => 'ID', 'leantimeField' => 'id'], ['sourceField' => 'Title', 'leantimeField' => 'headline']],
            [['ID' => '12', 'Title' => 'Not mine', 'projectId' => 9, 'editorId' => 1]],
            'tickets'
        );

        $this->assertFalse($result->isSuccessful());
        $this->assertSame(0, $result->imported);
    }

    public function test_review_flags_unknown_types_every_bad_date_column_and_assignees_off_the_team(): void
    {
        $calls = [];
        $connector = $this->connector(
            $calls,
            ['getStatusLabels' => fn () => []],
            [
                'getAllProjects' => fn () => [['id' => 9, 'name' => 'Website']],
                'getProjectIdByName' => fn ($all, $name) => $name === 'Website' ? 9 : false,
                'isUserAssignedToProject' => fn (int $userId, int $projectId) => $userId !== 2,
            ],
            ['getUserByEmail' => fn ($email) => ['id' => $email === 'ben@example.com' ? 2 : 1]],
        );

        $fields = [
            ['sourceField' => 'Title', 'leantimeField' => 'headline'],
            ['sourceField' => 'Project', 'leantimeField' => 'projectName'],
            ['sourceField' => 'Assignee', 'leantimeField' => 'editorId'],
            ['sourceField' => 'Type', 'leantimeField' => 'type'],
            ['sourceField' => 'Created', 'leantimeField' => 'date'],
            ['sourceField' => 'Due', 'leantimeField' => 'dateToFinish'],
        ];
        $values = [
            ['Title' => 'One', 'Project' => 'Website', 'Assignee' => 'ada@example.com', 'Type' => 'Story', 'Created' => '2026-11-01', 'Due' => '2026-12-01'],
            ['Title' => 'Two', 'Project' => 'Website', 'Assignee' => 'ben@example.com', 'Type' => 'Epic', 'Created' => '2026-11-01', 'Due' => '01/Dec/26 9:00 AM'],
            ['Title' => 'Three', 'Project' => 'Website', 'Assignee' => 'ada@example.com', 'Type' => 'Subtask', 'Created' => '', 'Due' => ''],
        ];

        $flags = $connector->parseValues($fields, $values, 'tickets');
        $joined = implode("\n", $flags);

        $this->assertStringContainsString('notification.import_row_invalid_type|3|Epic|task, story, bug', $joined);
        $this->assertStringContainsString('notification.import_row_invalid_type|4|Subtask|', $joined, 'subtasks need a parent');
        $this->assertStringContainsString('notification.import_row_invalid_date|3|Due|01/Dec/26 9:00 AM', $joined, 'the second date column is checked too');
        $this->assertStringContainsString('notification.import_row_assignee_not_on_team|3|ben@example.com|Website', $joined);
        $this->assertStringNotContainsString('|2|', $joined, 'the valid first row is not flagged');
    }

    public function test_a_flagged_review_leaves_nothing_to_import(): void
    {
        // An earlier clean review of another file left rows waiting.
        session(['serValues' => serialize([['Title' => 'Old']]), 'serFields' => serialize([['sourceField' => 'Title', 'leantimeField' => 'headline']]), 'serImportToken' => 'earlier']);

        $calls = [];
        $flags = $this->connector($calls)->parseValues(
            [['sourceField' => 'Title', 'leantimeField' => 'headline']],
            [['Title' => 'No project column']],
            'tickets'
        );

        $this->assertNotEmpty($flags);
        $this->assertNull(session('serValues'), 'rows the review rejected are not stored for the import step');
        $this->assertNull(session('serImportToken'));
    }

    public function test_an_id_that_matches_nothing_is_reported_instead_of_counted(): void
    {
        $calls = [];
        $edited = [];
        $connector = $this->connector(
            $calls,
            [],
            [
                'getProject' => fn (int $id) => $id === 3 ? ['id' => 3] : false,
                'editProject' => function ($values, $id) use (&$edited) {
                    $edited[] = ['project', $id];
                },
            ],
            [],
            [
                'getSingleCanvasItem' => fn (int $id) => match ($id) {
                    20 => ['canvasId' => 5, 'box' => 'goal'],
                    21 => ['canvasId' => 5, 'box' => 'idea'],
                    default => false,
                },
                'editCanvasItem' => function ($values) use (&$edited) {
                    $edited[] = ['goal', $values['itemId']];
                },
            ],
            [
                'getSingleCanvasItem' => fn (int $id) => $id === 30 ? ['canvasId' => 7] : false,
                'editCanvasItem' => function ($values) use (&$edited) {
                    $edited[] = ['idea', $values['itemId']];
                },
            ],
        );

        $projects = $connector->importValues(
            [['sourceField' => 'ID', 'leantimeField' => 'id'], ['sourceField' => 'Name', 'leantimeField' => 'name']],
            [['ID' => '3', 'Name' => 'Kept'], ['ID' => '404', 'Name' => 'Missing']],
            'projects'
        );
        $this->assertSame(1, $projects->imported);
        $this->assertSame([['row' => 3, 'message' => 'notification.import_row_id_not_found|404']], $projects->failures);

        $goalFields = [['sourceField' => 'ID', 'leantimeField' => 'itemId'], ['sourceField' => 'Board', 'leantimeField' => 'canvasId']];
        $goals = $connector->importValues($goalFields, [
            ['ID' => '20', 'Board' => '5'],   // a goal on that board
            ['ID' => '21', 'Board' => '5'],   // not a goal
            ['ID' => '20', 'Board' => '6'],   // a goal, but on another board
            ['ID' => '0', 'Board' => '5'],    // not an id
        ], 'goals');
        $this->assertSame(1, $goals->imported);
        $this->assertSame([3, 4, 5], array_column($goals->failures, 'row'));

        $ideas = $connector->importValues($goalFields, [['ID' => '30', 'Board' => '7'], ['ID' => '30', 'Board' => '8']], 'ideas');
        $this->assertSame(1, $ideas->imported);
        $this->assertSame([3], array_column($ideas->failures, 'row'));

        $this->assertSame([['project', 3], ['goal', '20'], ['idea', '30']], $edited, 'only items that exist on the named board are edited');
    }

    public function test_a_to_do_or_milestone_id_must_name_one_of_its_kind(): void
    {
        $calls = [];
        $connector = $this->connector($calls, [
            'getTicket' => fn ($id) => match ((int) $id) {
                12 => $this->ticket(12, 'task'),
                40 => $this->ticket(40, 'milestone'),
                default => false,   // doesn't exist, or the person can't see it
            },
        ]);
        $fields = [['sourceField' => 'ID', 'leantimeField' => 'id'], ['sourceField' => 'Title', 'leantimeField' => 'headline']];

        $todos = $connector->importValues($fields, [
            ['ID' => '12', 'Title' => 'A to-do'],
            ['ID' => '404', 'Title' => 'Missing'],
            ['ID' => '40', 'Title' => 'A milestone'],
            ['ID' => '0', 'Title' => 'Not an id'],
        ], 'tickets');
        $this->assertSame(1, $todos->imported);
        $this->assertSame([
            ['row' => 3, 'message' => 'notification.import_row_id_not_found|404'],
            ['row' => 4, 'message' => 'notification.import_row_id_not_found|40'],
            ['row' => 5, 'message' => 'notification.import_row_id_not_found|0'],
        ], $todos->failures, 'a milestone is never turned into a to-do');

        $milestones = $connector->importValues($fields, [
            ['ID' => '40', 'Title' => 'A milestone'],
            ['ID' => '12', 'Title' => 'A to-do'],
        ], 'milestones');
        $this->assertSame(1, $milestones->imported);
        $this->assertSame([3], array_column($milestones->failures, 'row'), 'a to-do is never turned into a milestone');

        $this->assertSame(['12', '40'], array_map(fn ($call) => (string) $call[1]['id'], $calls), 'only Ids of the right kind are updated');
    }

    public function test_an_update_that_keeps_an_assignee_who_left_the_team_is_not_flagged(): void
    {
        $calls = [];
        $connector = $this->connector(
            $calls,
            ['getStatusLabels' => fn () => []],
            [
                'getAllProjects' => fn () => [['id' => 9, 'name' => 'Website']],
                'getProjectIdByName' => fn ($all, $name) => $name === 'Website' ? 9 : false,
                // Ben (2) has left the team.
                'isUserAssignedToProject' => fn (int $userId, int $projectId) => $userId !== 2,
            ],
            ['getUserByEmail' => fn ($email) => ['id' => $email === 'ben@example.com' ? 2 : 1]],
            [],
            [],
            ['getTicket' => function ($id) {
                $ticket = new TicketModel;
                $ticket->id = (int) $id;
                $ticket->editorId = (int) $id === 12 ? 2 : 1;

                return $ticket;
            }],
        );

        $fields = [
            ['sourceField' => 'ID', 'leantimeField' => 'id'],
            ['sourceField' => 'Title', 'leantimeField' => 'headline'],
            ['sourceField' => 'Project', 'leantimeField' => 'projectName'],
            ['sourceField' => 'Assignee', 'leantimeField' => 'editorId'],
        ];
        $values = [
            // To-do 12 is already Ben's: renaming it is fine.
            ['ID' => '12', 'Title' => 'Renamed', 'Project' => 'Website', 'Assignee' => 'ben@example.com'],
            // To-do 13 is Ada's: giving it to Ben adds someone off the team.
            ['ID' => '13', 'Title' => 'Moved to Ben', 'Project' => 'Website', 'Assignee' => 'ben@example.com'],
        ];

        $joined = implode("\n", $connector->parseValues($fields, $values, 'tickets'));

        $this->assertStringNotContainsString('import_row_assignee_not_on_team|2|', $joined);
        $this->assertStringContainsString('notification.import_row_assignee_not_on_team|3|ben@example.com|Website', $joined);
    }

    public function test_a_clean_review_stores_its_entity_with_the_rows(): void
    {
        $calls = [];
        $connector = $this->connector(
            $calls,
            ['getStatusLabels' => fn () => []],
            [
                'getAllProjects' => fn () => [['id' => 9, 'name' => 'Website']],
                'getProjectIdByName' => fn ($all, $name) => 9,
                'isUserAssignedToProject' => fn () => true,
            ],
        );

        $flags = $connector->parseValues(
            [['sourceField' => 'Title', 'leantimeField' => 'headline'], ['sourceField' => 'Project', 'leantimeField' => 'projectName']],
            [['Title' => 'One', 'Project' => 'Website']],
            'tickets'
        );

        $this->assertSame([], $flags);
        $this->assertSame('tickets', session('serImportEntity'));
        $this->assertNotEmpty(session('serImportToken'));
    }

    public function test_several_columns_mapped_to_tags_are_combined(): void
    {
        $calls = [];
        $fields = [
            ['sourceField' => 'Summary', 'leantimeField' => 'headline'],
            ['sourceField' => 'Labels', 'leantimeField' => 'tags'],
            ['sourceField' => 'Labels (2)', 'leantimeField' => 'tags'],
            ['sourceField' => 'Labels (3)', 'leantimeField' => 'tags'],
        ];
        $values = [['Summary' => 'Fix login', 'Labels' => 'auth', 'Labels (2)' => '', 'Labels (3)' => 'urgent', 'projectId' => 9, 'editorId' => 1]];

        $this->connector($calls)->importValues($fields, $values, 'tickets');

        $this->assertSame('auth,urgent', $calls[0][1]['tags']);
    }

    public function test_a_field_that_takes_one_value_mapped_from_two_columns_is_flagged(): void
    {
        $calls = [];
        $connector = $this->connector(
            $calls,
            ['getStatusLabels' => fn () => []],
            [
                'getAllProjects' => fn () => [['id' => 9, 'name' => 'Website']],
                'getProjectIdByName' => fn ($all, $name) => 9,
                'isUserAssignedToProject' => fn () => true,
            ],
        );

        $flags = $connector->parseValues([
            ['sourceField' => 'Summary', 'leantimeField' => 'headline'],
            ['sourceField' => 'Issue key', 'leantimeField' => 'headline'],
            ['sourceField' => 'Labels', 'leantimeField' => 'tags'],
            ['sourceField' => 'Labels (2)', 'leantimeField' => 'tags'],
            ['sourceField' => 'Project', 'leantimeField' => 'projectName'],
        ], [['Summary' => 'One', 'Issue key' => 'WEB-1', 'Labels' => 'a', 'Labels (2)' => 'b', 'Project' => 'Website']], 'tickets');

        $this->assertSame(['notification.import_mapped_twice|Title|Summary, Issue key'], $flags, 'Tags may come from several columns; Title may not');
        $this->assertSame('', (new \Leantime\Domain\Connector\Services\ReviewedRows)->token(), 'nothing is kept for import');
    }

    public function test_messages_use_the_row_number_of_the_spreadsheet(): void
    {
        $calls = [];
        $connector = $this->connector($calls, [
            'addTicket' => fn ($values) => $values['headline'] === '' ? ['msg' => 'notifications.ticket_save_error_no_headline', 'type' => 'error'] : 7,
        ]);

        // Headings on row 4 and an empty row 6 that was skipped: the records are rows 5 and 7.
        $result = $connector->importValues(
            [['sourceField' => 'Title', 'leantimeField' => 'headline']],
            [
                ['Title' => 'Fine', Connector::ROW_KEY => '5', 'projectId' => 9, 'editorId' => 1],
                ['Title' => '', Connector::ROW_KEY => '7', 'projectId' => 9, 'editorId' => 1],
            ],
            'tickets'
        );

        $this->assertSame(7, $result->failures[0]['row']);
    }

    public function test_without_an_assignee_column_new_rows_are_mine_and_updates_keep_their_assignee(): void
    {
        session(['userdata.id' => 5]);
        $calls = [];
        $connector = $this->connector($calls, [], [
            'getAllProjects' => fn () => [['id' => 9, 'name' => 'Website']],
            'getProjectIdByName' => fn ($all, $name) => $name === 'Website' ? 9 : false,
            'isUserAssignedToProject' => fn () => true,
        ]);

        $fields = [
            ['sourceField' => 'ID', 'leantimeField' => 'id'],
            ['sourceField' => 'Title', 'leantimeField' => 'headline'],
            ['sourceField' => 'Project', 'leantimeField' => 'projectName'],
        ];
        $flags = $connector->parseValues($fields, [
            ['ID' => '', 'Title' => 'New', 'Project' => 'Website'],
            ['ID' => '12', 'Title' => 'Renamed', 'Project' => ''],
        ], 'tickets');
        $this->assertSame([], $flags);

        $rows = (new ReviewedRows)->load()['values'];
        $connector->importValues($fields, $rows, 'tickets');

        $this->assertSame(['add', 5], [$calls[0][0], $calls[0][1]['editorId']]);
        $this->assertSame(9, $calls[0][1]['projectId']);
        $this->assertSame('update', $calls[1][0]);
        $this->assertArrayNotHasKey('editorId', $calls[1][1], 'an update without an Assigned To column keeps its assignee');
        $this->assertArrayNotHasKey('projectId', $calls[1][1], 'an update with an empty Project cell stays in its project');
    }

    public function test_leave_unassigned_clears_the_assignee_of_an_update_and_an_empty_cell_keeps_it(): void
    {
        $calls = [];
        $connector = $this->connector($calls, [], [
            'getAllProjects' => fn () => [['id' => 9, 'name' => 'Website']],
            'getProjectIdByName' => fn ($all, $name) => $name === 'Website' ? 9 : false,
            'isUserAssignedToProject' => fn () => true,
        ]);

        $fields = [
            ['sourceField' => 'ID', 'leantimeField' => 'id'],
            ['sourceField' => 'Title', 'leantimeField' => 'headline'],
            ['sourceField' => 'Assignee', 'leantimeField' => 'editorId'],
        ];
        // "Leave unassigned" chosen on the check step for "Zed Nobody" in row 2; row 3's cell is empty.
        $flags = $connector->parseValues($fields, [
            ['ID' => '12', 'Title' => 'Was Zed', 'Assignee' => ReviewFixes::EMPTY],
            ['ID' => '13', 'Title' => 'Empty cell', 'Assignee' => ''],
        ], 'tickets');
        $this->assertSame([], $flags);

        $rows = (new ReviewedRows)->load()['values'];
        $connector->importValues($fields, $rows, 'tickets');

        $this->assertSame(['update', ''], [$calls[0][0], $calls[0][1]['editorId']], '"Leave unassigned" clears the assignee');
        $this->assertArrayNotHasKey('editorId', $calls[1][1], 'an empty cell on an update keeps the assignee');
    }

    public function test_a_milestone_update_without_an_assignee_column_keeps_its_assignee(): void
    {
        session(['userdata.id' => 5]);
        $calls = [];
        $connector = $this->connector($calls, [
            'getTicket' => fn ($id) => $this->ticket((int) $id, 'milestone'),
        ], [
            'getAllProjects' => fn () => [['id' => 9, 'name' => 'Website']],
            'getProjectIdByName' => fn ($all, $name) => $name === 'Website' ? 9 : false,
            'isUserAssignedToProject' => fn () => true,
        ]);

        $fields = [
            ['sourceField' => 'ID', 'leantimeField' => 'id'],
            ['sourceField' => 'Title', 'leantimeField' => 'headline'],
            ['sourceField' => 'Project', 'leantimeField' => 'projectName'],
            ['sourceField' => 'From', 'leantimeField' => 'editFrom'],
            ['sourceField' => 'To', 'leantimeField' => 'editTo'],
        ];
        $flags = $connector->parseValues($fields, [
            ['ID' => '', 'Title' => 'Launch', 'Project' => 'Website', 'From' => '2026-12-01', 'To' => '2026-12-05'],
            ['ID' => '40', 'Title' => 'Renamed', 'Project' => 'Website', 'From' => '2026-12-01', 'To' => '2026-12-05'],
        ], 'milestones');
        $this->assertSame([], $flags);

        $connector->importValues($fields, (new ReviewedRows)->load()['values'], 'milestones');

        $this->assertSame(['add', 5], [$calls[0][0], $calls[0][1]['editorId']]);
        $this->assertSame('update', $calls[1][0]);
        $this->assertArrayNotHasKey('editorId', $calls[1][1], 'a milestone update without an Assigned To column keeps its assignee');
    }

    public function test_people_are_found_by_email_or_full_name_and_an_empty_cell_leaves_a_new_row_unassigned(): void
    {
        $calls = [];
        $connector = $this->connector($calls, [], [
            'getAllProjects' => fn () => [['id' => 9, 'name' => 'Website']],
            'getProjectIdByName' => fn ($all, $name) => $name === 'Website' ? 9 : false,
            'isUserAssignedToProject' => fn () => true,
        ], [
            'getUserByEmail' => fn ($email) => $email === 'ada@example.com' ? ['id' => 1] : false,
            'getAll' => fn () => [
                ['id' => 1, 'firstname' => 'Ada', 'lastname' => 'Lovelace'],
                ['id' => 2, 'firstname' => 'Ben', 'lastname' => 'Ortiz'],
                ['id' => 3, 'firstname' => 'Sam', 'lastname' => 'Lee'],
                ['id' => 4, 'firstname' => 'Sam', 'lastname' => 'Lee'],
            ],
        ]);

        $fields = [
            ['sourceField' => 'Title', 'leantimeField' => 'headline'],
            ['sourceField' => 'Project', 'leantimeField' => 'projectName'],
            ['sourceField' => 'Assignee', 'leantimeField' => 'editorId'],
        ];
        $values = [
            ['Title' => 'By email', 'Project' => 'Website', 'Assignee' => 'ada@example.com'],
            ['Title' => 'By name', 'Project' => 'Website', 'Assignee' => '  ben   ortiz '],
            ['Title' => 'Nobody', 'Project' => 'Website', 'Assignee' => ''],
        ];
        $this->assertSame([], $connector->parseValues($fields, $values, 'tickets'));

        $rows = (new ReviewedRows)->load()['values'];
        $this->assertSame([1, 2, ''], array_column($rows, 'editorId'));

        $flags = implode("\n", $connector->parseValues($fields, [
            ['Title' => 'Unknown', 'Project' => 'Website', 'Assignee' => 'Cleo Park'],
            ['Title' => 'Twice', 'Project' => 'Website', 'Assignee' => 'Sam Lee'],
            ['Title' => 'Bad email', 'Project' => 'Website', 'Assignee' => 'cleo@example.com'],
        ], 'tickets'));
        $this->assertStringContainsString('notification.import_row_unknown_person|2|Cleo Park', $flags);
        $this->assertStringContainsString('notification.import_row_person_ambiguous|3|Sam Lee', $flags);
        $this->assertStringContainsString('notification.import_row_unknown_email|4|cleo@example.com', $flags);
    }

    public function test_a_chosen_project_takes_new_rows_that_name_none(): void
    {
        $calls = [];
        $connector = $this->connector($calls, [], [
            'getProject' => fn (int $id) => $id === 9 ? ['id' => 9, 'name' => 'Website'] : false,
            'getAllProjects' => fn () => [['id' => 9, 'name' => 'Website'], ['id' => 11, 'name' => 'Launch']],
            'getProjectIdByName' => fn ($all, $name) => $name === 'Launch' ? 11 : false,
            'isUserAssignedToProject' => fn () => true,
        ]);

        $titleOnly = [['sourceField' => 'Title', 'leantimeField' => 'headline']];

        $this->assertSame(['notification.import_choose_project'], $connector->parseValues($titleOnly, [['Title' => 'A']], 'tickets'));

        $this->assertSame([], $connector->parseValues($titleOnly, [['Title' => 'A'], ['Title' => 'B']], 'tickets', 9));
        $this->assertSame([9, 9], array_column((new ReviewedRows)->load()['values'], 'projectId'));

        // A Project column goes first; its empty cells take the chosen project.
        $withColumn = array_merge($titleOnly, [['sourceField' => 'Project', 'leantimeField' => 'projectName']]);
        $this->assertSame([], $connector->parseValues($withColumn, [['Title' => 'A', 'Project' => 'Launch'], ['Title' => 'B', 'Project' => '']], 'tickets', 9));
        $this->assertSame([11, 9], array_column((new ReviewedRows)->load()['values'], 'projectId'));

        // Without a chosen project, an empty cell is flagged with its row.
        $flags = $connector->parseValues($withColumn, [['Title' => 'A', 'Project' => '']], 'tickets');
        $this->assertSame(['notification.import_row_project_empty|2'], $flags);

        // A project that doesn't exist is not used.
        $this->assertSame(['notification.import_choose_project'], $connector->parseValues($titleOnly, [['Title' => 'A']], 'tickets', 404));
    }

    public function test_priority_names_become_numbers_and_unknown_priorities_are_flagged(): void
    {
        $calls = [];
        $connector = $this->connector($calls, [
            'getPriorityLabels' => fn () => ['1' => 'Critical', '2' => 'High', '3' => 'Medium', '4' => 'Low', '5' => 'Lowest'],
        ], [
            'getProject' => fn (int $id) => ['id' => 9, 'name' => 'Website'],
            'isUserAssignedToProject' => fn () => true,
        ]);

        $fields = [
            ['sourceField' => 'Title', 'leantimeField' => 'headline'],
            ['sourceField' => 'Priority', 'leantimeField' => 'priority'],
        ];
        $this->assertSame([], $connector->parseValues($fields, [
            ['Title' => 'A', 'Priority' => 'high'],
            ['Title' => 'B', 'Priority' => '4'],
            ['Title' => 'C', 'Priority' => ''],
        ], 'tickets', 9));
        $this->assertSame(['2', '4', ''], array_column((new ReviewedRows)->load()['values'], 'Priority'));

        $flags = $connector->parseValues($fields, [['Title' => 'A', 'Priority' => 'Urgent']], 'tickets', 9);
        $this->assertSame(['notification.import_row_invalid_priority|2|Urgent|1 Critical, 2 High, 3 Medium, 4 Low, 5 Lowest'], $flags);
    }

    public function test_hours_with_a_decimal_comma_are_read_and_other_text_is_flagged(): void
    {
        $this->assertSame('1.5', Connector::normalisedNumber('1,5'));
        $this->assertSame('1234.5', Connector::normalisedNumber('1.234,5'));
        $this->assertSame('1234.5', Connector::normalisedNumber('1,234.5'));
        $this->assertSame('2', Connector::normalisedNumber(' 2 '));
        $this->assertSame('1500', Connector::normalisedNumber("1\u{00A0}500"));
        $this->assertNull(Connector::normalisedNumber('2h'));

        $calls = [];
        $connector = $this->connector($calls, [], [
            'getProject' => fn (int $id) => ['id' => 9, 'name' => 'Website'],
            'isUserAssignedToProject' => fn () => true,
        ]);
        $fields = [
            ['sourceField' => 'Title', 'leantimeField' => 'headline'],
            ['sourceField' => 'Hours', 'leantimeField' => 'planHours'],
        ];

        $this->assertSame([], $connector->parseValues($fields, [['Title' => 'A', 'Hours' => '2,5']], 'tickets', 9));
        $this->assertSame('2.5', (new ReviewedRows)->load()['values'][0]['Hours']);

        $flags = $connector->parseValues($fields, [['Title' => 'A', 'Hours' => 'two']], 'tickets', 9);
        $this->assertSame(['notification.import_row_not_a_number|2|two|Planned Hours'], $flags);
    }

    public function test_dates_with_milliseconds_are_read(): void
    {
        $this->assertSame('2026-01-25T09:00:00Z', Connector::withoutFractionalSeconds('2026-01-25T09:00:00.000Z'));
        $this->assertSame('2026-01-25T09:00:00+01:00', Connector::withoutFractionalSeconds('2026-01-25T09:00:00.123456+01:00'));
        $this->assertSame('2026-01-25', Connector::withoutFractionalSeconds('2026-01-25'));
        $this->assertSame('1.5', Connector::withoutFractionalSeconds('1.5'));

        $calls = [];
        $connector = $this->connector($calls, [], [
            'getProject' => fn (int $id) => ['id' => 9, 'name' => 'Website'],
            'isUserAssignedToProject' => fn () => true,
        ]);
        $fields = [
            ['sourceField' => 'Card Name', 'leantimeField' => 'headline'],
            ['sourceField' => 'Due Date', 'leantimeField' => 'dateToFinish'],
        ];

        $this->assertSame([], $connector->parseValues($fields, [['Card Name' => 'A', 'Due Date' => '2026-01-25T17:00:00.000Z']], 'tickets', 9));
        $this->assertSame('2026-01-25T17:00:00Z', (new ReviewedRows)->load()['values'][0]['Due Date']);
    }

    public function test_review_messages_are_listed_by_row(): void
    {
        $calls = [];
        $connector = $this->connector($calls, [
            'getPriorityLabels' => fn () => ['1' => 'Critical', '2' => 'High', '3' => 'Medium', '4' => 'Low', '5' => 'Lowest'],
        ], [
            'getAllProjects' => fn () => [['id' => 9, 'name' => 'Website']],
            'getProjectIdByName' => fn ($all, $name) => $name === 'Website' ? 9 : false,
            'isUserAssignedToProject' => fn () => true,
        ]);

        $flags = $connector->parseValues([
            ['sourceField' => 'Title', 'leantimeField' => 'headline'],
            ['sourceField' => 'Project', 'leantimeField' => 'projectName'],
            ['sourceField' => 'Priority', 'leantimeField' => 'priority'],
            ['sourceField' => 'Hours', 'leantimeField' => 'planHours'],
        ], [
            ['Title' => 'A', 'Project' => 'Website', 'Priority' => 'Urgent', 'Hours' => 'abc'],
            ['Title' => 'B', 'Project' => 'Nowhere', 'Priority' => '', 'Hours' => ''],
        ], 'tickets');

        // The project check runs first, but row 2's problems come before row 3's.
        $this->assertSame([2, 2, 3], array_map(fn (string $flag) => (int) explode('|', $flag)[1], $flags));
        $this->assertStringStartsWith('notification.import_row_unknown_project|3|', $flags[2]);
    }

    public function test_cell_problems_are_kept_per_row_and_column_and_file_problems_stay_general(): void
    {
        $calls = [];
        $connector = $this->connector($calls, [
            'getPriorityLabels' => fn () => ['1' => 'Critical', '2' => 'High', '3' => 'Medium', '4' => 'Low', '5' => 'Lowest'],
        ], [
            'getAllProjects' => fn () => [['id' => 9, 'name' => 'Website']],
            'getProjectIdByName' => fn ($all, $name) => $name === 'Website' ? 9 : false,
            'isUserAssignedToProject' => fn () => true,
        ]);

        $flags = $connector->parseValues([
            ['sourceField' => 'Project', 'leantimeField' => 'projectName'],
            ['sourceField' => 'Priority', 'leantimeField' => 'priority'],
            ['sourceField' => 'Hours', 'leantimeField' => 'planHours'],
        ], [
            ['Project' => 'Website', 'Priority' => 'Urgent', 'Hours' => 'abc'],
            ['Project' => 'Nowhere', 'Priority' => 'high', 'Hours' => '1,5'],
        ], 'tickets');

        $problems = $connector->reviewProblems($flags);

        $this->assertSame(['Priority', 'Hours'], array_keys($problems['cells'][2]));
        $this->assertStringStartsWith('notification.import_cell_invalid_priority|2|Urgent|', $problems['cells'][2]['Priority'][0]);
        $this->assertSame(['Project'], array_keys($problems['cells'][3]));
        // The missing Title column is about the file, not a cell.
        $this->assertSame(['notification.import_column_required|Title'], $problems['general']);

        // The review keeps the rows as it read them: the priority name became its number.
        $this->assertSame('2', $connector->reviewedValues()[1]['Priority']);
        $this->assertSame('1.5', $connector->reviewedValues()[1]['Hours']);
    }
}

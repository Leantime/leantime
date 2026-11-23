<?php

namespace Unit\app\Domain\Connector\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Language;
use Leantime\Domain\Connector\Models\ImportResult;
use Leantime\Domain\Connector\Services\Connector;
use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas;
use Leantime\Domain\Ideas\Repositories\Ideas;
use Leantime\Domain\Projects\Services\Projects;
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
     * A Language stub that returns "key|arg1|arg2|arg3" once sprintf'd, so messages can be
     * asserted by key and arguments.
     */
    private function language(): Language
    {
        return $this->make(Language::class, [
            '__' => fn (string $index, string $default = '') => $index.'|%1$s|%2$s|%3$s',
        ]);
    }

    /**
     * @param  array<int, array{0: string, 1: array<string, mixed>}>  $calls
     * @param  array<string, mixed>  $ticketOverrides  Extra stubbed Tickets service methods.
     * @param  array<string, mixed>  $projectOverrides  Stubbed Projects service methods.
     */
    private function connector(array &$calls, array $ticketOverrides = [], array $projectOverrides = [], array $userOverrides = []): Connector
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
        ], $ticketOverrides));

        return new Connector(
            $this->make(Users::class, $userOverrides),
            $this->make(Projects::class, $projectOverrides),
            $tickets,
            $this->make(TicketRepository::class),
            $this->make(Goalcanvas::class),
            $this->make(Ideas::class),
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
        $this->assertSame('Ben Ortiz is not on the team', $result->failures[1]['message']);
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
}

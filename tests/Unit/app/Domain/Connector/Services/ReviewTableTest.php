<?php

namespace Unit\app\Domain\Connector\Services;

use Leantime\Core\Language;
use Leantime\Domain\Connector\Services\ReviewTable;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Users\Services\Users;
use Unit\TestCase;

/**
 * The check step's table: values as they will be saved, where they came from, problems per cell.
 */
class ReviewTableTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function table(): ReviewTable
    {
        $texts = [
            'text.import_note_in_file' => '%s in your file',
            'text.import_value_hours' => '%s h',
            'text.import_value_update' => 'Updates #%s',
            'text.import_default_project' => 'Every row goes into %s.',
        ];

        return new ReviewTable(
            $this->make(Users::class, ['getAll' => fn () => [
                ['id' => 2, 'firstname' => 'Ben', 'lastname' => 'Ortiz'],
                ['id' => 3, 'firstname' => 'Chloe', 'lastname' => 'Nguyen'],
            ]]),
            $this->make(Tickets::class, [
                'getPriorityLabels' => fn () => ['1' => 'Critical', '2' => 'High', '3' => 'Medium', '4' => 'Low', '5' => 'Lowest'],
                'getStatusLabels' => fn () => [3 => ['name' => 'New'], 4 => ['name' => 'In Progress']],
            ]),
            $this->make(Projects::class, ['getProject' => fn (int $id) => ['id' => $id, 'name' => 'Website']]),
            $this->make(Language::class, ['__' => fn (string $key) => $texts[$key] ?? $key]),
        );
    }

    public function test_values_are_shown_as_they_will_be_saved_with_the_file_value_when_it_differs(): void
    {
        $fields = [
            ['sourceField' => 'Title', 'leantimeField' => 'headline'],
            ['sourceField' => 'Owner', 'leantimeField' => 'editorId'],
            ['sourceField' => 'Priority', 'leantimeField' => 'priority'],
            ['sourceField' => 'Hours', 'leantimeField' => 'planHours'],
            ['sourceField' => 'State', 'leantimeField' => 'status'],
        ];
        $raw = [
            ['Title' => 'One', 'Owner' => 'Ben Ortiz', 'Priority' => 'High', 'Hours' => '1,5', 'State' => 'in progress', '__sheetRow' => 2],
            ['Title' => 'Two', 'Owner' => 'chloe@example.com', 'Priority' => '3', 'Hours' => '2', 'State' => 'Waiting', '__sheetRow' => 3],
            ['Title' => 'Three', 'Owner' => '', 'Priority' => '', 'Hours' => '', 'State' => '', '__sheetRow' => 4],
        ];
        $reviewed = [
            ['Title' => 'One', 'Owner' => 'Ben Ortiz', 'Priority' => '2', 'Hours' => '1.5', 'State' => 'in progress', 'editorId' => 2, 'projectId' => 9, 'status' => 4],
            ['Title' => 'Two', 'Owner' => 'chloe@example.com', 'Priority' => '3', 'Hours' => '2', 'State' => 'Waiting', 'editorId' => 3, 'projectId' => 9, 'status' => null],
            ['Title' => 'Three', 'Owner' => '', 'Priority' => '', 'Hours' => '', 'State' => '', 'editorId' => '', 'projectId' => 9],
        ];
        $leantimeFields = ['headline' => ['name' => 'Title'], 'editorId' => ['name' => 'Assigned To'], 'priority' => ['name' => 'Priority'], 'planHours' => ['name' => 'Planned Hours'], 'status' => ['name' => 'Status']];

        $table = $this->table()->build('tickets', $fields, $raw, $reviewed, ['cells' => [], 'general' => []], $leantimeFields, 9);

        [$one, $two, $three] = array_map(fn (array $row) => $row['cells'], $table['rows']);

        // The person found by name needs no note; found by email, the email is the note.
        $this->assertSame(['Ben Ortiz', null, 2], [$one[1]['value'], $one[1]['note'], $one[1]['userId']]);
        $this->assertSame(['Chloe Nguyen', 'chloe@example.com in your file'], [$two[1]['value'], $two[1]['note']]);
        $this->assertSame('text.import_value_unassigned', $three[1]['value']);
        $this->assertTrue($three[1]['muted']);

        $this->assertSame(['High', null], [$one[2]['value'], $one[2]['note']]);
        $this->assertSame(['Medium', '3 in your file'], [$two[2]['value'], $two[2]['note']]);
        $this->assertSame(['1.5 h', '1,5 in your file'], [$one[3]['value'], $one[3]['note']]);
        $this->assertSame(['In Progress', null], [$one[4]['value'], $one[4]['note']]);
        $this->assertSame('text.import_note_status_start', $two[4]['note'], 'an unknown status says what happens instead');

        $this->assertSame(['new' => 3, 'updates' => 0, 'assigned' => 2, 'unassigned' => 1], $table['counts']);
        $this->assertSame(['Website'], $table['projects']);
        // No Project, Type columns: where those values come from is said.
        $this->assertContains('Every row goes into Website.', $table['defaults']);
        $this->assertContains('text.import_default_type', $table['defaults']);
    }

    public function test_with_problems_only_the_rows_that_need_a_fix_are_shown_with_the_file_value(): void
    {
        $fields = [['sourceField' => 'Title', 'leantimeField' => 'headline'], ['sourceField' => 'Priority', 'leantimeField' => 'priority']];
        $raw = [
            ['Title' => 'Fine', 'Priority' => 'Low', '__sheetRow' => 2],
            ['Title' => 'Broken', 'Priority' => 'Urgent', '__sheetRow' => 3],
        ];
        $problems = ['cells' => [3 => ['Priority' => ['Not a priority.']]], 'general' => []];

        $table = $this->table()->build('tickets', $fields, $raw, $raw, $problems, ['headline' => ['name' => 'Title'], 'priority' => ['name' => 'Priority']]);

        $this->assertSame([3], array_column($table['rows'], 'row'));
        $this->assertSame(1, $table['hiddenRows']);
        $this->assertSame(['Urgent', ['Not a priority.']], [$table['rows'][0]['cells'][1]['value'], $table['rows'][0]['cells'][1]['problems']]);
    }
}

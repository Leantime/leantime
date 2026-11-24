<?php

namespace Unit\app\Domain\Connector\Services;

use Leantime\Core\Language;
use Leantime\Domain\Connector\Services\ReviewFixes;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Users\Services\Users;
use Unit\TestCase;

/**
 * Choices on the check step that change every row with the same value.
 */
class ReviewFixesTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function fixes(): ReviewFixes
    {
        return new ReviewFixes(
            $this->make(Users::class, ['getAll' => fn () => [
                ['id' => 2, 'firstname' => 'Ben', 'lastname' => 'Ortiz', 'username' => 'ben@example.com'],
            ]]),
            $this->make(Projects::class, ['getAllProjects' => fn () => [['id' => 9, 'name' => 'Website']]]),
            $this->make(Tickets::class, [
                'getPriorityLabels' => fn () => ['1' => 'Critical', '2' => 'High'],
                'getTicketTypes' => fn () => ['task', 'subtask', 'story', 'bug'],
            ]),
            $this->make(Language::class, ['__' => fn (string $key) => match ($key) {
                'label.import_fix_value' => '"%1$s" in %2$s',
                'label.import_fix_empty' => 'Empty %s',
                default => $key,
            }]),
        );
    }

    public function test_a_choice_changes_every_row_with_that_value_and_an_undone_choice_is_dropped(): void
    {
        $fixes = $this->fixes();
        $chosen = $fixes->fromRequest(['fixes' => [
            'a' => ['column' => 'Owner', 'from' => 'Zed Nobody', 'to' => 'ben@example.com'],
            'b' => ['column' => 'Due', 'from' => '', 'to' => '12/24/2026'],
            'c' => ['column' => 'Priority', 'from' => 'Urgent', 'to' => ''],
        ]]);

        $this->assertCount(2, $chosen, 'a choice left at Choose… changes nothing');

        $rows = $fixes->apply([
            ['Owner' => 'Zed Nobody', 'Due' => '', 'Priority' => 'Urgent'],
            ['Owner' => ' Zed Nobody ', 'Due' => '2026-12-01', 'Priority' => 'Low'],
            ['Owner' => 'Ada', 'Due' => '', 'Priority' => 'Urgent'],
        ], $chosen);
        $this->assertSame(['ben@example.com', 'ben@example.com', 'Ada'], array_column($rows, 'Owner'));
        $this->assertSame(['12/24/2026', '2026-12-01', '12/24/2026'], array_column($rows, 'Due'));

        $undone = $fixes->fromRequest(['fixes' => [
            'a' => ['column' => 'Owner', 'from' => 'Zed Nobody', 'to' => 'ben@example.com'],
        ], 'undoFix' => ReviewFixes::fixKey('Owner', 'Zed Nobody')]);
        $this->assertSame([], $undone);
    }

    public function test_choices_match_the_file_values_so_they_cannot_overwrite_each_other(): void
    {
        $fixes = $this->fixes();
        $chosen = $fixes->fromRequest(['fixes' => [
            'a' => ['column' => 'Owner', 'from' => 'Zed Nobody', 'to' => ReviewFixes::EMPTY],
            'b' => ['column' => 'Owner', 'from' => '', 'to' => 'ben@example.com'],
            'c' => ['column' => 'Priority', 'from' => 'Urgent', 'to' => 'High'],
            'd' => ['column' => 'Priority', 'from' => 'High', 'to' => '1'],
        ]]);

        $rows = $fixes->apply([
            ['Owner' => 'Zed Nobody', 'Priority' => 'Urgent'],
            ['Owner' => '', 'Priority' => 'High'],
        ], $chosen);

        $this->assertSame([ReviewFixes::EMPTY, 'ben@example.com'], array_column($rows, 'Owner'), 'Zed stays unassigned; only the empty cell gets Ben');
        $this->assertSame(['High', '1'], array_column($rows, 'Priority'), 'Urgent becomes High, not Critical');

        // Leaving empty cells empty changes nothing.
        $rows = $fixes->apply([['Owner' => '']], ['x' => ['column' => 'Owner', 'from' => '', 'to' => ReviewFixes::EMPTY]]);
        $this->assertSame('', $rows[0]['Owner']);
    }

    public function test_choices_are_offered_per_problem_value_and_for_empty_assignees_and_due_dates(): void
    {
        $fields = [
            ['sourceField' => 'Owner', 'leantimeField' => 'editorId'],
            ['sourceField' => 'Priority', 'leantimeField' => 'priority'],
            ['sourceField' => 'Due', 'leantimeField' => 'dateToFinish'],
        ];
        $raw = [
            ['Owner' => 'Zed Nobody', 'Priority' => 'Urgent', 'Due' => ''],
            ['Owner' => 'Zed Nobody', 'Priority' => 'Urgent', 'Due' => ''],
            ['Owner' => '', 'Priority' => 'High', 'Due' => '2026-12-01'],
        ];
        $fixable = [
            'Owner' => ['notification.import_row_unknown_person' => ['Zed Nobody' => [2, 3]]],
            'Priority' => ['notification.import_row_invalid_priority' => ['Urgent' => [2, 3]]],
        ];
        $names = ['editorId' => ['name' => 'Assigned To'], 'priority' => ['name' => 'Priority'], 'dateToFinish' => ['name' => 'Due Date']];

        $choices = $this->fixes()->choices('tickets', $fields, $raw, $fixable, [], $names);
        $open = array_column($choices['open'], null, 'label');

        $this->assertSame(['"Zed Nobody" in Assigned To', '"Urgent" in Priority', 'Empty Assigned To', 'Empty Due Date'], array_keys($open));
        $this->assertSame(2, $open['"Zed Nobody" in Assigned To']['rows']);
        $this->assertSame([ReviewFixes::EMPTY => 'label.import_fix_unassigned', 'ben@example.com' => 'Ben Ortiz'], $open['"Zed Nobody" in Assigned To']['options']);
        $this->assertSame(['1' => 'Critical', '2' => 'High'], $open['"Urgent" in Priority']['options']);
        $this->assertSame(['ben@example.com' => 'Ben Ortiz'], $open['Empty Assigned To']['options'], 'empty cells are already unassigned');
        $this->assertSame(['date', 2], [$open['Empty Due Date']['kind'], $open['Empty Due Date']['rows']]);

        // A choice already made is listed with what it changed to, and not offered again.
        $applied = ['k' => ['column' => 'Due', 'from' => '', 'to' => '12/24/2026']];
        $choices = $this->fixes()->choices('tickets', $fields, $raw, [], [ReviewFixes::fixKey('Due', '') => $applied['k']], $names);
        $this->assertSame(['Empty Assigned To'], array_column($choices['open'], 'label'));
        $this->assertSame(['Empty Due Date', '12/24/2026'], [$choices['applied'][0]['label'], $choices['applied'][0]['toLabel']]);
    }
}

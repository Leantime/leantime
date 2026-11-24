<?php

namespace Unit\app\Domain\CsvImport\Services;

use Leantime\Domain\CsvImport\Services\Presets;
use Unit\TestCase;

/**
 * Presets for other tools' exports: recognising a file, suggesting column matches, value choices.
 */
class PresetsTest extends TestCase
{
    /** Headings of a raw Jira export after the reader numbered the repeats. */
    private const JIRA = ['Summary', 'Issue key', 'Issue id', 'Issue Type', 'Status', 'Priority', 'Assignee', 'Reporter', 'Created', 'Due date', 'Labels', 'Labels (2)', 'Sprint', 'Description'];

    /** The to-do fields, as LeantimeEntities names them. */
    private const FIELDS = [
        'sprint' => ['name' => 'Sprint'],
        'id' => ['name' => 'Id'], 'headline' => ['name' => 'Title'], 'description' => ['name' => 'Description'],
        'type' => ['name' => 'Type'], 'editorId' => ['name' => 'Assigned To'], 'priority' => ['name' => 'Priority'],
        'date' => ['name' => 'Created On'], 'dateToFinish' => ['name' => 'Due Date'], 'status' => ['name' => 'Status'],
        'tags' => ['name' => 'Tags'], 'projectName' => ['name' => 'Project'], 'planHours' => ['name' => 'Planned Hours'],
    ];

    public function test_a_jira_export_is_recognised_and_a_plain_sheet_is_not(): void
    {
        $presets = new Presets;

        $this->assertSame('jira', $presets->recognise(self::JIRA)['id'] ?? null);
        $this->assertSame('jira', $presets->recognise(self::JIRA, 'tickets')['id'] ?? null);
        $this->assertNull($presets->recognise(self::JIRA, 'users'), 'a Jira preset only imports to-dos');
        $this->assertNull($presets->recognise(['Title', 'Status', 'Assigned To']));
    }

    public function test_jira_columns_are_suggested_each_field_once_never_the_id_nor_a_leftover(): void
    {
        $presets = new Presets;
        $suggested = $presets->suggest(self::JIRA, self::FIELDS, $presets->recognise(self::JIRA), 'tickets');

        $this->assertSame([
            'Summary' => 'headline',
            'Issue Type' => 'type',
            'Status' => 'status',
            'Priority' => 'priority',
            'Assignee' => 'editorId',
            'Due date' => 'dateToFinish',
            'Labels' => 'tags',
            'Labels (2)' => 'tags',
            'Description' => 'description',
        ], $suggested);

        // Without a preset, usual headings and field names still match.
        $this->assertSame(['Task name' => 'headline', 'Owner' => 'editorId', 'Deadline' => 'dateToFinish'],
            $presets->suggest(['Task name', 'Owner', 'Deadline', 'Id', 'Colour'], self::FIELDS, null, 'tickets'));
    }

    public function test_other_entities_get_their_own_field_names_not_the_to_do_headings(): void
    {
        $presets = new Presets;

        // An idea's "description" field is its Title, and its "data" field is its Description.
        $ideas = ['itemId' => ['name' => 'Id'], 'description' => ['name' => 'Title'], 'data' => ['name' => 'Description'], 'author' => ['name' => 'Author']];
        $this->assertSame(['Title' => 'description', 'Description' => 'data', 'Author' => 'author'],
            $presets->suggest(['Title', 'Description', 'Author', 'Notes'], $ideas, null, 'ideas'));

        $projects = ['id' => ['name' => 'Id'], 'name' => ['name' => 'Project Name'], 'start' => ['name' => 'Start Date'], 'end' => ['name' => 'End Date']];
        $this->assertSame(['Project Name' => 'name', 'Start Date' => 'start', 'End Date' => 'end'],
            $presets->suggest(['Project Name', 'Start Date', 'End Date', 'Owner'], $projects, null, 'projects'));
    }

    public function test_jira_values_become_choices_for_the_values_the_file_has(): void
    {
        $presets = new Presets;
        $fields = [
            ['sourceField' => 'Status', 'leantimeField' => 'status'],
            ['sourceField' => 'Priority', 'leantimeField' => 'priority'],
            ['sourceField' => 'Issue Type', 'leantimeField' => 'type'],
        ];
        $rows = [
            ['Status' => 'To Do', 'Priority' => 'Highest', 'Issue Type' => 'Epic'],
            ['Status' => 'to do', 'Priority' => 'Medium', 'Issue Type' => 'Story'],
            ['Status' => 'Waiting for customer', 'Priority' => '', 'Issue Type' => 'Story'],
        ];

        $choices = $presets->valueChoices($presets->all()['jira'], $fields, $rows);

        $this->assertContains(['column' => 'Status', 'from' => 'To Do', 'to' => 'kind:new'], $choices);
        $this->assertContains(['column' => 'Status', 'from' => 'to do', 'to' => 'kind:new'], $choices);
        $this->assertContains(['column' => 'Priority', 'from' => 'Highest', 'to' => '1'], $choices);
        $this->assertContains(['column' => 'Issue Type', 'from' => 'Epic', 'to' => 'task'], $choices);
        $this->assertNotContains('Waiting for customer', array_column($choices, 'from'), 'a value the preset doesn\'t know is left for the check step');
        $this->assertNotContains('Medium', array_column($choices, 'from'), 'a value Leantime reads already needs no translation');
        $this->assertNotContains('Story', array_column($choices, 'from'));
    }
}

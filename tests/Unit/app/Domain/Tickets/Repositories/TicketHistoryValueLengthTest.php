<?php

namespace Unit\app\Domain\Tickets\Repositories;

use Illuminate\Database\SQLiteConnection;
use Leantime\Domain\Tickets\Repositories\Tickets;
use Unit\TestCase;

/**
 * addTicketChange() must fit every history value into zp_tickethistory.changeValue (#4005).
 *
 * The column is varchar(150). It recorded the full new description, which MySQL in non-strict
 * mode cut silently but PostgreSQL refused ("value too long for type character varying(150)"),
 * so saving a to-do with a longer description (e.g. ticking a checklist item) failed with a 500.
 *
 * Runs against a real in-memory SQLite database (which does not enforce the length, so the
 * test checks the written value itself).
 */
class TicketHistoryValueLengthTest extends TestCase
{
    private SQLiteConnection $connection;

    private Tickets $tickets;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = new SQLiteConnection(new \PDO('sqlite::memory:'));
        $this->connection->statement('CREATE TABLE zp_tickets (
            id INTEGER PRIMARY KEY, headline TEXT, type TEXT, description TEXT, projectId INTEGER,
            status INTEGER, dateToFinish TEXT, priority TEXT, planHours INTEGER, editorId TEXT,
            editFrom TEXT, editTo TEXT, staging TEXT, production TEXT
        )');
        $this->connection->statement('CREATE TABLE zp_tickethistory (
            id INTEGER PRIMARY KEY, userId INTEGER NULL, ticketId INTEGER, changeType TEXT,
            changeValue TEXT, dateModified TEXT
        )');

        $this->connection->table('zp_tickets')->insert([
            'id' => 140, 'headline' => 'Migrate site', 'type' => 'task', 'description' => '<p>Old</p>',
            'projectId' => 2, 'status' => 3, 'dateToFinish' => null, 'priority' => '', 'planHours' => 0,
            'editorId' => '2', 'editFrom' => null, 'editTo' => null, 'staging' => '', 'production' => '',
        ]);

        $this->tickets = (new \ReflectionClass(Tickets::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(Tickets::class, 'connection'))->setValue($this->tickets, $this->connection);
    }

    private function recordedValue(string $changeType): ?string
    {
        return $this->connection->table('zp_tickethistory')
            ->where('ticketId', 140)
            ->where('changeType', $changeType)
            ->value('changeValue');
    }

    public function test_a_long_description_is_cut_to_the_column_length(): void
    {
        $checklist = str_repeat('<li data-checked="true" data-type="taskItem"><p>Creazione backup</p></li>', 10);

        $this->tickets->addTicketChange(2, 140, ['description' => '<ul data-type="taskList">'.$checklist.'</ul>']);

        $recorded = $this->recordedValue('description');
        $this->assertNotNull($recorded, 'The description change is still recorded');
        $this->assertSame(Tickets::HISTORY_VALUE_MAX_LENGTH, mb_strlen($recorded));
        $this->assertStringStartsWith('<ul data-type="taskList"><li data-checked="true"', $recorded);
    }

    public function test_multibyte_text_is_cut_by_characters_without_splitting_one(): void
    {
        $description = str_repeat('Größe ✓ 漢字 😀 ', 30);

        $this->tickets->addTicketChange(2, 140, ['description' => $description]);

        $recorded = $this->recordedValue('description');
        $this->assertSame(Tickets::HISTORY_VALUE_MAX_LENGTH, mb_strlen($recorded));
        $this->assertTrue(mb_check_encoding($recorded, 'UTF-8'), 'No multibyte character is split');
        $this->assertSame(mb_substr($description, 0, Tickets::HISTORY_VALUE_MAX_LENGTH), $recorded);
    }

    public function test_short_values_are_recorded_unchanged(): void
    {
        $this->tickets->addTicketChange(2, 140, ['headline' => 'Migrate site to NS', 'status' => 0]);

        $this->assertSame('Migrate site to NS', $this->recordedValue('headline'));
        $this->assertSame('0', (string) $this->recordedValue('status'));
    }
}

<?php

namespace Unit\app\Domain\Tickets\Repositories;

use Illuminate\Database\SQLiteConnection;
use Leantime\Domain\Tickets\Repositories\Tickets;
use Unit\TestCase;

/**
 * updateTicket() under cron (no session user) — the RecurringTasks incident.
 *
 * It credited collaborators to session('userdata.id'), null under cron, so addCollaborators()
 * threw AFTER the ticket was updated and its collaborators deleted. The caller never recorded
 * the run and retried every minute, pushing due dates centuries ahead and wiping collaborators.
 * Now the ticket's author is credited, and history + update + collaborator rewrite are one
 * transaction: a failure leaves the ticket, its collaborators and its history untouched.
 *
 * Runs against a real in-memory SQLite database.
 */
class UpdateTicketWithoutSessionUserTest extends TestCase
{
    private SQLiteConnection $connection;

    private Tickets $tickets;

    protected function setUp(): void
    {
        parent::setUp();

        session()->forget('userdata');

        $this->connection = new SQLiteConnection(new \PDO('sqlite::memory:'));
        $this->connection->statement('CREATE TABLE zp_tickets (
            id INTEGER PRIMARY KEY, headline TEXT, type TEXT, description TEXT, projectId INTEGER,
            status INTEGER, date TEXT, dateToFinish TEXT, sprint INTEGER, storypoints INTEGER,
            priority TEXT, hourRemaining INTEGER, planHours INTEGER, tags TEXT, editorId TEXT,
            editFrom TEXT, editTo TEXT, acceptanceCriteria TEXT, dependingTicketId INTEGER,
            milestoneid INTEGER, modified TEXT, staging TEXT, production TEXT, userId INTEGER
        )');
        $this->connection->statement('CREATE TABLE zp_tickethistory (
            id INTEGER PRIMARY KEY, userId INTEGER NULL, ticketId INTEGER, changeType TEXT,
            changeValue TEXT, dateModified TEXT
        )');
        $this->connection->statement('CREATE TABLE zp_entity_relationship (
            id INTEGER PRIMARY KEY, entityA INTEGER, entityAType TEXT, entityB INTEGER,
            entityBType TEXT, relationship TEXT, createdOn TEXT, createdBy INTEGER NULL
        )');

        $this->connection->table('zp_tickets')->insert([
            'id' => 17, 'headline' => 'Monthly report', 'type' => 'task', 'description' => '',
            'projectId' => 2, 'status' => 0, 'date' => '2026-01-01 00:00:00',
            'dateToFinish' => '2026-10-01 06:59:00', 'sprint' => 0, 'storypoints' => 0,
            'priority' => '', 'hourRemaining' => 0, 'planHours' => 0, 'tags' => '',
            'editorId' => '4', 'editFrom' => null, 'editTo' => null, 'acceptanceCriteria' => '',
            'dependingTicketId' => 0, 'milestoneid' => 0, 'modified' => '2026-10-01 00:00:00',
            'userId' => 9,
        ]);
        $this->connection->table('zp_entity_relationship')->insert([
            'entityA' => 17, 'entityAType' => 'Ticket', 'entityB' => 5, 'entityBType' => 'User',
            'relationship' => 'Collaborator', 'createdOn' => '2026-10-01 00:00:00', 'createdBy' => 9,
        ]);

        $this->tickets = (new \ReflectionClass(Tickets::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(Tickets::class, 'connection'))->setValue($this->tickets, $this->connection);
    }

    /**
     * The ticket as RecurringTasks::resetTask() passes it: getTicket() row with collaborators,
     * reset to status 3 and the due date moved one cycle.
     *
     * @return array<string, mixed>
     */
    private function resetValues(): array
    {
        $values = (array) $this->connection->table('zp_tickets')->where('id', 17)->first();
        $values['status'] = 3;
        $values['dateToFinish'] = '2026-11-01 06:59:00';
        $values['collaborators'] = [5];

        return $values;
    }

    public function test_without_a_session_user_the_ticket_author_is_credited(): void
    {
        $this->assertTrue($this->tickets->updateTicket($this->resetValues(), 17));

        $ticket = $this->connection->table('zp_tickets')->where('id', 17)->first();
        $this->assertSame('2026-11-01 06:59:00', $ticket->dateToFinish);
        $this->assertSame(3, (int) $ticket->status);

        $collaborator = $this->connection->table('zp_entity_relationship')->where('entityA', 17)->first();
        $this->assertSame(5, (int) $collaborator->entityB, 'The collaborator survives the reset');
        $this->assertSame(9, (int) $collaborator->createdBy, 'Credited to the ticket author, not null');

        $this->assertSame(
            ['deadline' => 9, 'status' => 9],
            array_map('intval', $this->connection->table('zp_tickethistory')->orderBy('changeType')->pluck('userId', 'changeType')->all()),
            'The status and due-date history rows are attributed to the same acting user'
        );
    }

    public function test_a_failure_rolls_back_the_ticket_its_collaborators_and_its_history(): void
    {
        $this->connection->statement("CREATE TRIGGER fail_collaborator_insert BEFORE INSERT ON zp_entity_relationship
            BEGIN SELECT RAISE(ABORT, 'collaborator insert failed'); END");

        try {
            $this->tickets->updateTicket($this->resetValues(), 17);
            $this->fail('The forced collaborator insert failure must surface');
        } catch (\Throwable $e) {
            $this->assertStringContainsString('collaborator insert failed', $e->getMessage());
        }

        $ticket = $this->connection->table('zp_tickets')->where('id', 17)->first();
        $this->assertSame('2026-10-01 06:59:00', $ticket->dateToFinish, 'The due date is not moved');
        $this->assertSame(0, (int) $ticket->status);
        $this->assertSame(1, $this->connection->table('zp_entity_relationship')->where('entityA', 17)->count(), 'The existing collaborator is not deleted');
        $this->assertSame(0, $this->connection->table('zp_tickethistory')->count(), 'No history for a change that never happened');
    }
}

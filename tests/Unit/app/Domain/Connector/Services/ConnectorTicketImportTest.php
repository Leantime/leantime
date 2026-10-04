<?php

namespace Unit\app\Domain\Connector\Services;

use Leantime\Domain\Connector\Services\Connector;
use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas;
use Leantime\Domain\Ideas\Repositories\Ideas;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Users\Services\Users;
use Unit\TestCase;

/**
 * Ticket imports must not send a default status id: an existing ticket keeps its stored status
 * and a new one gets its project's NEW status when the status column did not resolve.
 */
class ConnectorTicketImportTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * @param  array<int, array{0: string, 1: array<string, mixed>}>  $calls
     */
    private function connector(array &$calls): Connector
    {
        $tickets = $this->make(Tickets::class, [
            'updateTicket' => function ($values) use (&$calls) {
                $calls[] = ['update', $values];

                return true;
            },
            'addTicket' => function ($values) use (&$calls) {
                $calls[] = ['add', $values];

                return 1;
            },
        ]);

        return new Connector(
            $this->make(Users::class),
            $this->make(Projects::class),
            $tickets,
            $this->make(TicketRepository::class),
            $this->make(Goalcanvas::class),
            $this->make(Ideas::class),
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

        $this->assertTrue($this->connector($calls)->importValues($fields, $values, 'tickets'));

        $this->assertSame('update', $calls[0][0]);
        $this->assertArrayNotHasKey('status', $calls[0][1], 'the stored status of an existing ticket is kept');
        $this->assertSame('add', $calls[1][0]);
        $this->assertArrayNotHasKey('status', $calls[1][1], 'a new ticket gets its project NEW status');
        $this->assertSame(11, $calls[2][1]['status']);
    }
}

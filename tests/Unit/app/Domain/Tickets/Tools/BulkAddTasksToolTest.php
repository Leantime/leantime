<?php

namespace Unit\app\Domain\Tickets\Tools;

use Laravel\Mcp\Request;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Tickets\Tools\AddTaskTool;
use Leantime\Domain\Tickets\Tools\BulkAddTasksTool;
use Leantime\Domain\Tickets\Tools\FindTasksTool;
use Unit\TestCase;

/**
 * quickAddTicket() reports validation failures as an array; the bulk tool must count those as
 * failures instead of "success" with an array id.
 */
class BulkAddTasksToolTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    public function test_array_results_are_reported_as_failures(): void
    {
        $tickets = $this->make(Tickets::class, [
            'quickAddTicket' => fn ($params) => $params['headline'] === ''
                ? ['status' => 'error', 'message' => 'Headline Missing']
                : 42,
        ]);

        $text = (string) (new BulkAddTasksTool($tickets))->handle(new Request(['tasks' => [
            ['headline' => 'Valid', 'projectId' => 9],
            ['headline' => '', 'projectId' => 9],
        ]]))->content();

        $this->assertStringContainsString('Success: 1, Failed: 1', $text);
        $this->assertStringContainsString('Headline Missing', $text);
    }

    public function test_single_and_bulk_creation_forward_tags_priority_and_effort(): void
    {
        $created = [];
        $tickets = $this->make(Tickets::class, [
            'quickAddTicket' => function ($params) use (&$created) {
                $created[] = $params;

                return 42;
            },
        ]);
        $task = ['headline' => 'Agent task', 'projectId' => 9, 'tags' => 'agent,review', 'priority' => 2, 'storypoints' => 0.5];

        (new AddTaskTool($tickets))->handle(new Request($task));
        (new BulkAddTasksTool($tickets))->handle(new Request(['tasks' => [$task]]));

        foreach ($created as $params) {
            $this->assertSame('agent,review', $params['tags']);
            $this->assertSame(2, $params['priority']);
            $this->assertSame(0.5, $params['storypoints']);
        }
        $this->assertCount(2, $created);
    }

    public function test_find_tasks_passes_tags_and_open_status_to_service(): void
    {
        $criteria = null;
        $tickets = $this->make(Tickets::class, [
            'getAll' => function ($filters, $limit) use (&$criteria) {
                $criteria = $filters;

                return [];
            },
        ]);

        (new FindTasksTool($tickets))->handle(new Request(['tags' => 'Agent', 'status' => 'open']));

        $this->assertSame('Agent', $criteria['tags']);
        $this->assertSame('NOT_DONE', $criteria['statusType']);
    }
}

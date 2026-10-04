<?php

namespace Unit\app\Domain\Tickets\Tools;

use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Tickets\Tools\BulkAddTasksTool;
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

        $text = (new BulkAddTasksTool($tickets))->handle(['tasks' => [
            ['headline' => 'Valid', 'projectId' => 9],
            ['headline' => '', 'projectId' => 9],
        ]])->toArray()['content'][0]['text'];

        $this->assertStringContainsString('Success: 1, Failed: 1', $text);
        $this->assertStringContainsString('Headline Missing', $text);
    }
}

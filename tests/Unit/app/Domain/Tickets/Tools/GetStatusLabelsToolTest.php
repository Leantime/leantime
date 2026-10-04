<?php

namespace Unit\app\Domain\Tickets\Tools;

use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Tickets\Tools\GetStatusLabelsTool;
use Unit\TestCase;

/**
 * The MCP tool must not expose the status configuration of a project the caller cannot view.
 */
class GetStatusLabelsToolTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    public function test_refuses_a_project_the_caller_cannot_view(): void
    {
        $tickets = $this->make(Tickets::class, [
            'getStatusLabels' => function () {
                throw new \RuntimeException('must not read labels of a foreign project');
            },
        ]);
        $projects = $this->make(Projects::class, ['getProject' => fn () => false]);

        $result = (new GetStatusLabelsTool($tickets, $projects))->handle(['projectId' => 77])->toArray();

        $this->assertTrue($result['isError']);
    }

    public function test_returns_labels_of_a_visible_project(): void
    {
        $tickets = $this->make(Tickets::class, [
            'getStatusLabels' => fn () => [3 => ['name' => 'New', 'statusType' => 'NEW', 'kanbanCol' => '1']],
        ]);
        $projects = $this->make(Projects::class, ['getProject' => fn () => ['id' => 9]]);

        $result = (new GetStatusLabelsTool($tickets, $projects))->handle(['projectId' => 9])->toArray();

        $this->assertFalse($result['isError']);
        $this->assertStringContainsString('NEW', $result['content'][0]['text']);
    }
}

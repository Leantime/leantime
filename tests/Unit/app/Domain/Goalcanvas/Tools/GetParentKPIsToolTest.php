<?php

namespace Unit\app\Domain\Goalcanvas\Tools;

use Leantime\Domain\Goalcanvas\Services\Goalcanvas;
use Leantime\Domain\Goalcanvas\Tools\GetParentKPIsTool;
use Leantime\Domain\Projects\Services\Projects;
use Unit\TestCase;

/**
 * The MCP tool must not expose the KPIs of a project the caller cannot view.
 */
class GetParentKPIsToolTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    public function test_refuses_a_project_the_caller_cannot_view(): void
    {
        $goalcanvas = $this->make(Goalcanvas::class, [
            'getParentKPIs' => function () {
                throw new \RuntimeException('must not read KPIs of a foreign project');
            },
        ]);
        $projects = $this->make(Projects::class, ['getProject' => fn () => false]);

        $result = (new GetParentKPIsTool($goalcanvas, $projects))->handle(['projectId' => 77])->toArray();

        $this->assertTrue($result['isError']);
    }

    public function test_returns_kpis_of_a_visible_project(): void
    {
        $goalcanvas = $this->make(Goalcanvas::class, [
            'getParentKPIs' => fn () => [['id' => 3, 'description' => 'Revenue', 'project' => 'P', 'board' => 'B']],
        ]);
        $projects = $this->make(Projects::class, ['getProject' => fn () => ['id' => 9]]);

        $result = (new GetParentKPIsTool($goalcanvas, $projects))->handle(['projectId' => 9])->toArray();

        $this->assertFalse($result['isError']);
        $this->assertStringContainsString('Revenue', $result['content'][0]['text']);
    }
}

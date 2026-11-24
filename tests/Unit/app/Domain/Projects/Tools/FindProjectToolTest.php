<?php

namespace Unit\app\Domain\Projects\Tools;

use Laravel\Mcp\Request;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Projects\Tools\FindProjectTool;
use Unit\TestCase;

class FindProjectToolTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    public function test_missing_term_returns_a_useful_error(): void
    {
        $response = (new FindProjectTool($this->make(Projects::class)))->handle(new Request([]));

        $this->assertTrue($response->isError());
        $this->assertStringContainsString('term', (string) $response->content());
    }

    public function test_project_results_have_separate_ids_and_records(): void
    {
        $projects = $this->make(Projects::class, [
            'findProject' => fn () => [
                ['id' => 2, 'name' => 'First', 'clientName' => 'Client', 'type' => 'project', 'state' => 1],
                ['id' => 3, 'name' => 'Second', 'clientName' => 'Client', 'type' => 'project', 'state' => 1],
            ],
        ]);

        $text = (string) (new FindProjectTool($projects))->handle(new Request(['term' => 'project']))->content();

        $this->assertStringContainsString("**Id:**2\n\n", $text);
        $this->assertStringContainsString("**Id:**3\n\n", $text);
        $this->assertStringContainsString("**State:**1\n\n**Id:**3", $text);
    }
}

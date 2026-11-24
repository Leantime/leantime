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
}

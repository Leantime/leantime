<?php

namespace Unit\app\Domain\Projects\Tools;

use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Projects\Tools\GetUsersAssignedToProjectTool;
use Unit\TestCase;

/**
 * MCP tools call services directly (no RPC permission gate), so the tool must refuse a project
 * the caller cannot view instead of listing its members, emails and roles.
 */
class GetUsersAssignedToProjectToolTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    public function test_refuses_a_project_the_caller_cannot_view(): void
    {
        $projects = $this->make(Projects::class, [
            'getProject' => fn () => false,
            'getUsersAssignedToProject' => function () {
                throw new \RuntimeException('must not list members of a foreign project');
            },
        ]);

        $result = (new GetUsersAssignedToProjectTool($projects))->handle(['projectId' => 77])->toArray();

        $this->assertTrue($result['isError']);
    }

    public function test_lists_members_of_a_visible_project(): void
    {
        $projects = $this->make(Projects::class, [
            'getProject' => fn () => ['id' => 9],
            'getUsersAssignedToProject' => fn () => [['id' => 1, 'firstname' => 'A', 'lastname' => 'B', 'username' => 'a@b.c', 'role' => 'editor']],
        ]);

        $result = (new GetUsersAssignedToProjectTool($projects))->handle(['projectId' => 9])->toArray();

        $this->assertFalse($result['isError']);
        $this->assertStringContainsString('a@b.c', $result['content'][0]['text']);
    }
}

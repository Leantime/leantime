<?php

namespace Unit\app\Domain\Projects\Tools;

use Laravel\Mcp\Request;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Projects\Tools\AddUserToProjectTool;
use Leantime\Domain\Projects\Tools\RemoveUserFromProjectTool;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\TestCase;

/**
 * MCP tools call services directly (no RPC permission gate), so the team tools check
 * projects.edit themselves and refuse a project the caller cannot view.
 */
class ProjectTeamToolsTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function permissions(bool $allowed): PermissionService
    {
        return $this->make(PermissionService::class, ['currentUserCan' => fn () => $allowed]);
    }

    private function users(array|false $user = ['id' => 2, 'status' => 'a']): UserRepository
    {
        return $this->make(UserRepository::class, ['getUser' => fn () => $user]);
    }

    private function projects(bool $visible, bool $changed): Projects
    {
        return $this->make(Projects::class, [
            'getProject' => fn () => $visible ? ['id' => 9] : false,
            'addUserToProject' => fn () => $changed,
            'removeUserFromProject' => fn () => $changed,
        ]);
    }

    public function test_both_tools_refuse_without_permission(): void
    {
        $projects = $this->make(Projects::class, [
            'addUserToProject' => function () {
                throw new \RuntimeException('must not change the team');
            },
            'removeUserFromProject' => function () {
                throw new \RuntimeException('must not change the team');
            },
        ]);
        $request = new Request(['userId' => 2, 'projectId' => 9]);

        $this->assertTrue((new AddUserToProjectTool($projects, $this->permissions(false)))->handle($request)->isError());
        $this->assertTrue((new RemoveUserFromProjectTool($projects, $this->permissions(false)))->handle($request)->isError());
    }

    public function test_both_tools_refuse_a_project_the_caller_cannot_view(): void
    {
        $request = new Request(['userId' => 2, 'projectId' => 77]);

        $this->assertTrue((new AddUserToProjectTool($this->projects(false, true), $this->permissions(true)))->handle($request)->isError());
        $this->assertTrue((new RemoveUserFromProjectTool($this->projects(false, true), $this->permissions(true)))->handle($request)->isError());
    }

    public function test_add_reports_added_or_already_on_the_team(): void
    {
        $request = new Request(['userId' => 2, 'projectId' => 9]);

        $added = (new AddUserToProjectTool($this->projects(true, true), $this->permissions(true), $this->users()))->handle($request);
        $this->assertFalse($added->isError());
        $this->assertStringContainsString('was added to project 9', (string) $added->content());

        $already = (new AddUserToProjectTool($this->projects(true, false), $this->permissions(true), $this->users()))->handle($request);
        $this->assertFalse($already->isError());
        $this->assertStringContainsString('already on the team', (string) $already->content());
    }

    public function test_remove_reports_removed_or_not_on_the_team(): void
    {
        $request = new Request(['userId' => 2, 'projectId' => 9]);

        $removed = (new RemoveUserFromProjectTool($this->projects(true, true), $this->permissions(true), $this->users()))->handle($request);
        $this->assertStringContainsString('was removed from project 9', (string) $removed->content());

        $absent = (new RemoveUserFromProjectTool($this->projects(true, false), $this->permissions(true), $this->users()))->handle($request);
        $this->assertStringContainsString('is not on the team', (string) $absent->content());
    }

    public function test_an_unknown_user_is_an_error_not_already_on_the_team(): void
    {
        $request = new Request(['userId' => 404, 'projectId' => 9]);

        $add = (new AddUserToProjectTool($this->projects(true, false), $this->permissions(true), $this->users(false)))->handle($request);
        $this->assertTrue($add->isError());
        $this->assertStringContainsString('User 404 was not found', (string) $add->content());

        $remove = (new RemoveUserFromProjectTool($this->projects(true, false), $this->permissions(true), $this->users(false)))->handle($request);
        $this->assertTrue($remove->isError());
        $this->assertStringContainsString('User 404 was not found', (string) $remove->content());
    }

    public function test_an_inactive_user_is_not_added(): void
    {
        $projects = $this->make(Projects::class, [
            'getProject' => fn () => ['id' => 9],
            'addUserToProject' => function () {
                throw new \RuntimeException('must not change the team');
            },
        ]);

        $result = (new AddUserToProjectTool($projects, $this->permissions(true), $this->users(['id' => 2, 'status' => 'i'])))
            ->handle(new Request(['userId' => 2, 'projectId' => 9]));

        $this->assertTrue($result->isError());
        $this->assertStringContainsString('not active', (string) $result->content());
    }
}

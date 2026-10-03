<?php

namespace Unit\app\Domain\Api\Services;

use Leantime\Domain\Api\Repositories\Api as ApiRepository;
use Leantime\Domain\Api\Services\Api as ApiService;
use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\TestCase;

/**
 * Unit tests for the Api service helpers extracted during the thin-controller
 * refactor (project relation reconciliation, API key creation/update, image
 * response building and user filtering).
 */
class ApiServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Builds a real Api service, allowing each dependency to be overridden with
     * a stub so we can observe the persistence calls.
     */
    private function makeService(
        ?ApiRepository $apiRepo = null,
        ?UserRepository $userRepo = null,
        ?ProjectRepository $projectRepo = null,
        ?MenuRepository $menuRepo = null,
    ): ApiService {
        return new ApiService(
            $apiRepo ?? $this->make(ApiRepository::class),
            $userRepo ?? $this->make(UserRepository::class),
            $projectRepo ?? $this->make(ProjectRepository::class),
            $menuRepo ?? $this->make(MenuRepository::class),
        );
    }

    public function test_get_project_relation_ids_extracts_project_ids(): void
    {
        $projectRepo = $this->make(ProjectRepository::class, [
            'getUserProjectRelation' => fn () => [
                ['projectId' => 5],
                ['projectId' => 9],
            ],
        ]);

        $result = $this->makeService(projectRepo: $projectRepo)->getProjectRelationIds(3);

        $this->assertSame([5, 9], $result);
    }

    public function test_create_api_key_with_projects_sets_relations_when_projects_selected(): void
    {
        $editCalledWith = null;
        $deleteCalled = false;

        $userRepo = $this->make(UserRepository::class, [
            'addUser' => fn () => '77',
        ]);
        $projectRepo = $this->make(ProjectRepository::class, [
            'editUserProjectRelations' => function ($id, $projects) use (&$editCalledWith) {
                $editCalledWith = [$id, $projects];

                return true;
            },
            'deleteAllProjectRelations' => function () use (&$deleteCalled) {
                $deleteCalled = true;
            },
        ]);

        $result = $this->makeService(userRepo: $userRepo, projectRepo: $projectRepo)
            ->createApiKeyWithProjects(['firstname' => 'Key', 'role' => '20'], ['3', '4']);

        $this->assertIsArray($result);
        $this->assertSame('77', $result['id']);
        // id is cast to int when reconciling relations.
        $this->assertSame([77, ['3', '4']], $editCalledWith);
        $this->assertFalse($deleteCalled);
    }

    public function test_create_api_key_with_projects_clears_relations_when_leading_zero(): void
    {
        $editCalled = false;
        $deleteCalledWith = null;

        $userRepo = $this->make(UserRepository::class, [
            'addUser' => fn () => '88',
        ]);
        $projectRepo = $this->make(ProjectRepository::class, [
            'editUserProjectRelations' => function () use (&$editCalled) {
                $editCalled = true;

                return true;
            },
            'deleteAllProjectRelations' => function ($id) use (&$deleteCalledWith) {
                $deleteCalledWith = $id;
            },
        ]);

        $this->makeService(userRepo: $userRepo, projectRepo: $projectRepo)
            ->createApiKeyWithProjects(['firstname' => 'Key'], ['0']);

        $this->assertFalse($editCalled);
        $this->assertSame(88, $deleteCalledWith);
    }

    public function test_create_api_key_with_projects_skips_reconcile_when_no_projects(): void
    {
        $touched = false;

        $userRepo = $this->make(UserRepository::class, [
            'addUser' => fn () => '5',
        ]);
        $projectRepo = $this->make(ProjectRepository::class, [
            'editUserProjectRelations' => function () use (&$touched) {
                $touched = true;

                return true;
            },
            'deleteAllProjectRelations' => function () use (&$touched) {
                $touched = true;
            },
        ]);

        // null projects and empty array both mean "do nothing".
        $this->makeService(userRepo: $userRepo, projectRepo: $projectRepo)
            ->createApiKeyWithProjects(['firstname' => 'Key'], null);

        $this->assertFalse($touched);
    }

    public function test_create_api_key_with_projects_returns_false_when_user_not_created(): void
    {
        $userRepo = $this->make(UserRepository::class, [
            'addUser' => fn () => false,
        ]);

        $result = $this->makeService(userRepo: $userRepo)
            ->createApiKeyWithProjects(['firstname' => 'Key'], ['3']);

        $this->assertFalse($result);
    }

    public function test_update_api_key_edits_user_and_reconciles_relations(): void
    {
        $editUserCalledWith = null;
        $editRelationsCalledWith = null;

        $userRepo = $this->make(UserRepository::class, [
            'getUser' => fn () => [
                'firstname' => 'Old',
                'username' => 'lt_old',
                'status' => 'i',
                'role' => '10',
                'source' => 'api',
            ],
            'editUser' => function ($values, $id) use (&$editUserCalledWith) {
                $editUserCalledWith = [$values, $id];

                return true;
            },
        ]);
        $projectRepo = $this->make(ProjectRepository::class, [
            'editUserProjectRelations' => function ($id, $projects) use (&$editRelationsCalledWith) {
                $editRelationsCalledWith = [$id, $projects];

                return true;
            },
        ]);

        $result = $this->makeService(userRepo: $userRepo, projectRepo: $projectRepo)
            ->updateApiKey(12, ['firstname' => 'New', 'status' => 'a', 'role' => '20'], ['7']);

        $this->assertTrue($result);
        // Posted firstname/status/role applied, username preserved from row, source forced to 'api'.
        $this->assertSame(12, $editUserCalledWith[1]);
        $this->assertSame('New', $editUserCalledWith[0]['firstname']);
        $this->assertSame('a', $editUserCalledWith[0]['status']);
        $this->assertSame('20', $editUserCalledWith[0]['role']);
        $this->assertSame('lt_old', $editUserCalledWith[0]['user']);
        $this->assertSame('api', $editUserCalledWith[0]['source']);
        $this->assertSame([12, ['7']], $editRelationsCalledWith);
    }

    public function test_update_api_key_falls_back_to_row_values_when_not_posted(): void
    {
        $editUserCalledWith = null;

        $userRepo = $this->make(UserRepository::class, [
            'getUser' => fn () => [
                'firstname' => 'Old',
                'username' => 'lt_old',
                'status' => 'i',
                'role' => '10',
                'source' => 'api',
            ],
            'editUser' => function ($values) use (&$editUserCalledWith) {
                $editUserCalledWith = $values;

                return true;
            },
        ]);
        $projectRepo = $this->make(ProjectRepository::class, [
            'deleteAllProjectRelations' => fn () => null,
        ]);

        $this->makeService(userRepo: $userRepo, projectRepo: $projectRepo)
            ->updateApiKey(12, [], null);

        $this->assertSame('Old', $editUserCalledWith['firstname']);
        $this->assertSame('i', $editUserCalledWith['status']);
        $this->assertSame('10', $editUserCalledWith['role']);
    }

    public function test_update_api_key_throws_on_invalid_id(): void
    {
        $this->expectException(\Exception::class);

        $this->makeService()->updateApiKey(0, [], null);
    }

    public function test_api_key_cannot_outrank_its_creator(): void
    {
        session(['userdata' => ['id' => 4, 'role' => 'admin']]);
        $userRepo = $this->make(UserRepository::class, [
            'addUser' => function () {
                $this->fail('the owner-role key must not be created');
            },
        ]);

        $this->expectException(\Leantime\Core\Exceptions\AuthorizationException::class);

        $this->makeService(userRepo: $userRepo)->createAPIKey(['firstname' => 'key', 'role' => '50']);
    }

    public function test_update_api_key_rejects_non_api_accounts_and_promotions(): void
    {
        session(['userdata' => ['id' => 4, 'role' => 'admin']]);
        $userRepo = $this->make(UserRepository::class, [
            'getUser' => fn ($id) => $id === 1
                ? ['firstname' => 'Owner', 'username' => 'owner@example.com', 'status' => 'a', 'role' => '50', 'source' => '']
                : ['firstname' => 'Key', 'username' => 'lt_key', 'status' => 'a', 'role' => '20', 'source' => 'api'],
            'editUser' => function () {
                $this->fail('nothing may be stored');
            },
        ]);
        $service = $this->makeService(userRepo: $userRepo);

        try {
            $service->updateApiKey(1, ['firstname' => 'Hijacked'], null);
            $this->fail('a regular user account must not be editable as an API key');
        } catch (\Leantime\Core\Exceptions\AuthorizationException) {
        }

        $this->expectException(\Leantime\Core\Exceptions\AuthorizationException::class);
        $service->updateApiKey(2, ['role' => '50'], null);
    }

    public function test_delete_api_key_only_deletes_api_keys_within_the_ceiling(): void
    {
        session(['userdata' => ['id' => 4, 'role' => 'admin']]);
        $deleted = [];
        $rows = [
            1 => ['id' => 1, 'role' => '50', 'source' => ''],
            2 => ['id' => 2, 'role' => '50', 'source' => 'api'],
            3 => ['id' => 3, 'role' => '20', 'source' => 'api'],
        ];
        $userRepo = $this->make(UserRepository::class, [
            'getUser' => fn ($id) => $rows[$id] ?? false,
            'deleteUser' => function ($id) use (&$deleted) {
                $deleted[] = $id;

                return true;
            },
        ]);
        $projectRepo = $this->make(ProjectRepository::class, [
            'deleteAllProjectRelations' => fn () => null,
        ]);
        $service = $this->makeService(userRepo: $userRepo, projectRepo: $projectRepo);

        foreach ([1, 2, 99] as $id) {
            try {
                $service->deleteApiKey($id);
                $this->fail('id '.$id.' must not be deletable as an API key');
            } catch (\Leantime\Core\Exceptions\AuthorizationException) {
            }
        }

        $this->assertTrue($service->deleteApiKey(3));
        $this->assertSame([3], $deleted);
    }
}

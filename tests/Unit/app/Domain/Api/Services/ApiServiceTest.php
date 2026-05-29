<?php

namespace Unit\app\Domain\Api\Services;

use Leantime\Domain\Api\Repositories\Api as ApiRepository;
use Leantime\Domain\Api\Services\Api as ApiService;
use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use SVG\SVG;
use Symfony\Component\HttpFoundation\Response;
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

    public function test_filter_users_by_query_matches_case_insensitive_substring(): void
    {
        $users = [
            ['id' => 1, 'firstname' => 'Alice', 'lastname' => 'Anderson'],
            ['id' => 2, 'firstname' => 'Bob', 'lastname' => 'Brown'],
            ['id' => 3, 'firstname' => 'Carol', 'lastname' => 'Smith'],
        ];

        $result = $this->makeService()->filterUsersByQuery($users, 'BROWN');

        $this->assertCount(1, $result);
        $this->assertSame(2, $result[0]['id']);
    }

    public function test_filter_users_by_query_reindexes_results(): void
    {
        $users = [
            ['id' => 1, 'name' => 'nope'],
            ['id' => 2, 'name' => 'match me'],
        ];

        $result = $this->makeService()->filterUsersByQuery($users, 'match');

        // Re-indexed from 0 even though the original key was 1.
        $this->assertArrayHasKey(0, $result);
        $this->assertSame(2, $result[0]['id']);
    }

    public function test_filter_users_by_query_returns_empty_when_no_match(): void
    {
        $users = [['id' => 1, 'name' => 'Alice']];

        $this->assertSame([], $this->makeService()->filterUsersByQuery($users, 'zzz'));
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

    public function test_build_image_response_renders_svg(): void
    {
        $svg = $this->make(SVG::class, [
            'toXMLString' => fn () => '<svg></svg>',
        ]);

        $response = $this->makeService()->buildImageResponse($svg);

        $this->assertSame('<svg></svg>', $response->getContent());
        $this->assertSame('image/svg+xml', $response->headers->get('Content-type'));
        // Symfony normalizes the Cache-Control header by appending ", private".
        $this->assertStringContainsString('max-age=86400', $response->headers->get('Cache-Control'));
    }

    public function test_build_image_response_passes_through_existing_response(): void
    {
        $existing = new Response('already built');

        $this->assertSame($existing, $this->makeService()->buildImageResponse($existing));
    }
}

<?php

namespace Unit\app\Domain\Clients\Services;

use Leantime\Core\Exceptions\EntityExistsException;
use Leantime\Core\Exceptions\MissingParameterException;
use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Comments\Services\Comments as CommentService;
use Leantime\Domain\Files\Services\Files as FileService;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\TestCase;

/**
 * Unit tests for the Clients service helpers extracted during the
 * thin-controller refactor (createClient, updateClient, removeUser,
 * getClientPageData).
 */
class ClientsServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Builds a real Clients service, allowing each dependency to be
     * overridden with a stub so we can observe the persistence calls.
     */
    private function makeService(
        ?ClientRepository $clientRepo = null,
        ?UserRepository $userRepo = null,
        ?ProjectRepository $projectRepo = null,
        ?CommentService $commentService = null,
        ?FileService $fileService = null,
    ): ClientService {
        return new ClientService(
            $projectRepo ?? $this->make(ProjectRepository::class),
            $clientRepo ?? $this->make(ClientRepository::class),
            $commentService ?? $this->make(CommentService::class),
            $fileService ?? $this->make(FileService::class),
            $userRepo ?? $this->make(UserRepository::class),
        );
    }

    public function test_create_client_returns_new_id_for_valid_unique_client(): void
    {
        $repo = $this->make(ClientRepository::class, [
            'isClient' => fn () => false,
            'addClient' => fn () => '42',
        ]);

        $id = $this->makeService(clientRepo: $repo)->createClient(['name' => 'Acme']);

        $this->assertSame(42, $id);
    }

    public function test_create_client_throws_when_name_missing(): void
    {
        $addCalls = 0;
        $repo = $this->make(ClientRepository::class, [
            'isClient' => fn () => false,
            'addClient' => function () use (&$addCalls) {
                $addCalls++;

                return 1;
            },
        ]);

        $this->expectException(MissingParameterException::class);

        try {
            $this->makeService(clientRepo: $repo)->createClient(['name' => '']);
        } finally {
            $this->assertSame(0, $addCalls, 'An invalid client must never reach the repository');
        }
    }

    public function test_create_client_throws_when_client_already_exists(): void
    {
        $addCalls = 0;
        $repo = $this->make(ClientRepository::class, [
            'isClient' => fn () => true,
            'addClient' => function () use (&$addCalls) {
                $addCalls++;

                return 1;
            },
        ]);

        $this->expectException(EntityExistsException::class);

        try {
            $this->makeService(clientRepo: $repo)->createClient(['name' => 'Acme']);
        } finally {
            $this->assertSame(0, $addCalls, 'A duplicate client must never be persisted');
        }
    }

    public function test_update_client_throws_when_name_missing(): void
    {
        $editCalls = 0;
        $repo = $this->make(ClientRepository::class, [
            'editClient' => function () use (&$editCalls) {
                $editCalls++;

                return true;
            },
        ]);

        $this->expectException(MissingParameterException::class);

        try {
            $this->makeService(clientRepo: $repo)->updateClient(['id' => 5, 'name' => '']);
        } finally {
            $this->assertSame(0, $editCalls, 'An invalid update must never reach the repository');
        }
    }

    public function test_update_client_persists_valid_values(): void
    {
        $captured = null;
        $repo = $this->make(ClientRepository::class, [
            'editClient' => function ($values, $id) use (&$captured) {
                $captured = ['values' => $values, 'id' => $id];

                return true;
            },
        ]);

        $result = $this->makeService(clientRepo: $repo)->updateClient(['id' => 5, 'name' => 'Acme']);

        $this->assertTrue($result);
        $this->assertSame(5, $captured['id']);
        $this->assertSame('Acme', $captured['values']['name']);
    }

    public function test_remove_user_returns_false_for_missing_ids(): void
    {
        $removeCalls = 0;
        $userRepo = $this->make(UserRepository::class, [
            'removeFromClient' => function () use (&$removeCalls) {
                $removeCalls++;

                return true;
            },
        ]);

        $service = $this->makeService(userRepo: $userRepo);

        $this->assertFalse($service->removeUser(0, 5));
        $this->assertFalse($service->removeUser(5, 0));
        $this->assertSame(0, $removeCalls, 'Guarded calls must not hit the repository');
    }

    public function test_remove_user_delegates_to_user_repository(): void
    {
        $removedUserId = null;
        $userRepo = $this->make(UserRepository::class, [
            'removeFromClient' => function ($userId) use (&$removedUserId) {
                $removedUserId = $userId;

                return true;
            },
        ]);

        $result = $this->makeService(userRepo: $userRepo)->removeUser(3, 7);

        $this->assertTrue($result);
        $this->assertSame(7, $removedUserId);
    }
}

<?php

namespace Unit\app\Domain\Clients\Services;

use GuzzleHttp\Client;
use Illuminate\Database\SQLiteConnection;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\Avatarcreator;
use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
use Leantime\Domain\Clients\Services\Clients as ClientService;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Comments\Services\Comments as CommentService;
use Leantime\Domain\Files\Services\Files as FileService;
use Leantime\Domain\Notifications\Services\Messengers;
use Leantime\Domain\Notifications\Services\Notifications as NotificationService;
use Leantime\Domain\Notifications\Services\Webhooks;
use Leantime\Domain\Projects\Permissions\ProjectsPermissions;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\TestCase;

/**
 * Regression for #4040: deleting a client deletes its projects through the Projects service, so
 * each project is cleaned up like a project deleted from its settings (team memberships removed,
 * cached project access refreshed). Runs the real Clients + Projects services and repositories
 * against an in-memory SQLite database.
 */
class DeleteClientTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private SQLiteConnection $connection;

    /** How often the permission engine was told that project access changed. */
    private int $accessChangedSignals = 0;

    /** @var array<int, string> Permission keys the current user holds. */
    private array $grantedPermissions = [ProjectsPermissions::DELETE];

    protected function setUp(): void
    {
        parent::setUp();

        session(['userdata' => ['id' => 1, 'role' => 'admin']]);

        $this->connection = new SQLiteConnection(new \PDO('sqlite::memory:'));
        $this->connection->statement('CREATE TABLE zp_clients (id INTEGER PRIMARY KEY, name VARCHAR(200))');
        $this->connection->statement('CREATE TABLE zp_projects (
            id INTEGER PRIMARY KEY,
            name VARCHAR(200),
            clientId INTEGER NULL,
            hourBudget INTEGER NULL,
            dollarBudget INTEGER NULL,
            state INTEGER NULL,
            active INTEGER NULL,
            modified DATETIME NULL,
            type VARCHAR(45) NULL
        )');
        $this->connection->statement('CREATE TABLE zp_tickets (id INTEGER PRIMARY KEY, projectId INTEGER, type VARCHAR(45))');
        $this->connection->statement('CREATE TABLE zp_relationuserproject (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            userId INTEGER,
            projectId INTEGER,
            projectRole VARCHAR(45) NULL
        )');

        $this->connection->table('zp_clients')->insert([
            ['id' => 5, 'name' => 'Acme'],
            ['id' => 6, 'name' => 'Other client'],
        ]);
        $this->connection->table('zp_projects')->insert([
            ['id' => 50, 'name' => 'Acme website', 'clientId' => 5],
            ['id' => 51, 'name' => 'Acme app', 'clientId' => 5],
            ['id' => 60, 'name' => 'Other project', 'clientId' => 6],
        ]);
        $this->connection->table('zp_relationuserproject')->insert([
            ['userId' => 2, 'projectId' => 50],
            ['userId' => 3, 'projectId' => 50],
            ['userId' => 2, 'projectId' => 51],
            ['userId' => 2, 'projectId' => 60],
        ]);

        // A resolved permission engine, so PermissionService::projectAccessChanged() reaches it.
        $permissions = $this->make(PermissionService::class, [
            'currentUserCan' => fn (string $permissionKey) => in_array($permissionKey, $this->grantedPermissions, true),
            'forgetResolvedProjects' => function () {
                $this->accessChangedSignals++;
            },
        ]);
        app()->instance(PermissionService::class, $permissions);
    }

    private function makeClientService(): ClientService
    {
        $db = $this->make(DbCore::class, ['getConnection' => fn () => $this->connection]);
        $dbHelper = $this->make(DatabaseHelper::class);

        $projectRepository = new ProjectRepository(
            $this->make(Environment::class),
            $db,
            $this->make(Avatarcreator::class),
            $dbHelper,
        );
        $clientRepository = new ClientRepository($db, $dbHelper);
        $permissions = app(PermissionService::class);

        $projectService = new ProjectService(
            $projectRepository,
            $this->make(TicketRepository::class),
            $this->make(SettingRepository::class),
            $this->make(LanguageCore::class, ['__' => fn ($key) => $key]),
            $this->make(Messengers::class),
            $this->make(NotificationService::class),
            $this->make(FileService::class),
            $this->make(Avatarcreator::class),
            $this->make(QueueRepository::class),
            $this->make(UserRepository::class),
            $this->make(CommentRepository::class),
            $clientRepository,
            $this->make(Client::class),
            $this->make(Webhooks::class),
        );
        $projectService->setPermissionService($permissions);

        $clientService = new ClientService(
            $projectRepository,
            $clientRepository,
            $this->make(CommentService::class),
            $this->make(FileService::class),
            $this->make(UserRepository::class),
            $projectService,
        );
        $clientService->setPermissionService($permissions);

        return $clientService;
    }

    /** @return array<int, int> */
    private function projectIds(): array
    {
        return array_map('intval', $this->connection->table('zp_projects')->orderBy('id')->pluck('id')->all());
    }

    /** @return array<int, int> */
    private function membershipProjectIds(): array
    {
        return array_map('intval', $this->connection->table('zp_relationuserproject')->orderBy('projectId')->pluck('projectId')->all());
    }

    public function test_deleting_a_client_removes_its_projects_and_their_team_memberships(): void
    {
        $deleted = $this->makeClientService()->delete(5);

        $this->assertTrue($deleted);
        $this->assertFalse($this->connection->table('zp_clients')->where('id', 5)->exists());
        $this->assertSame([60], $this->projectIds(), 'Only the other client\'s project remains');
        $this->assertSame([60], $this->membershipProjectIds(), 'Memberships of the deleted projects are removed');
        $this->assertGreaterThan(0, $this->accessChangedSignals, 'Cached project access must be refreshed');
    }

    public function test_projects_hidden_from_client_lists_are_deleted_with_the_client(): void
    {
        // active = -1 projects are skipped by getClientProjects(), but must not outlive their client.
        $this->connection->table('zp_projects')->insert(['id' => 52, 'name' => 'Acme archive', 'clientId' => 5, 'active' => -1]);
        $this->connection->table('zp_relationuserproject')->insert(['userId' => 4, 'projectId' => 52]);

        $clientService = $this->makeClientService();
        $this->assertSame([51, 52, 50], array_column($clientService->getProjectsDeletedWithClient(5), 'id'), 'The dialog lists every project, by name');

        $this->assertTrue($clientService->delete(5));
        $this->assertSame([60], $this->projectIds());
        $this->assertSame([60], $this->membershipProjectIds());
    }

    public function test_a_client_without_projects_is_deleted_without_touching_other_projects(): void
    {
        $this->connection->table('zp_clients')->insert(['id' => 7, 'name' => 'Empty client']);

        $this->assertTrue($this->makeClientService()->delete(7));
        $this->assertFalse($this->connection->table('zp_clients')->where('id', 7)->exists());
        $this->assertSame([50, 51, 60], $this->projectIds());
    }

    public function test_a_client_with_to_dos_is_not_deleted(): void
    {
        $this->connection->table('zp_tickets')->insert(['id' => 1, 'projectId' => 51, 'type' => 'task']);

        $deleted = $this->makeClientService()->delete(5);

        $this->assertFalse($deleted);
        $this->assertTrue($this->connection->table('zp_clients')->where('id', 5)->exists());
        $this->assertSame([50, 51, 60], $this->projectIds());
        $this->assertSame([50, 50, 51, 60], $this->membershipProjectIds());
    }

    public function test_nothing_is_deleted_when_the_user_may_not_delete_projects(): void
    {
        $this->grantedPermissions = [];

        try {
            $this->makeClientService()->delete(5);
            $this->fail('Deleting a client with projects must require projects.delete');
        } catch (AuthorizationException) {
            // expected
        }

        $this->assertTrue($this->connection->table('zp_clients')->where('id', 5)->exists());
        $this->assertSame([50, 51, 60], $this->projectIds());
        $this->assertSame([50, 50, 51, 60], $this->membershipProjectIds());
    }
}

<?php

namespace Unit\app\Domain\Projects\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\Avatarcreator;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Files\Services\Files as FileService;
use Leantime\Domain\Notifications\Services\Messengers;
use Leantime\Domain\Notifications\Services\Notifications as NotificationService;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\TestCase;

/**
 * Unit tests for the business logic extracted from the Projects domain
 * controllers into the Projects service during the thin-controller refactor:
 * getProjectHubData, notifyProjectCreated, saveZulipWebhook,
 * getProjectIntegrationSettings and getProjectCardData.
 */
class ProjectsServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    protected function setUp(): void
    {
        parent::setUp();

        // Session + macros needed because getUsersAssignedToProject() uses dtHelper().
        session(['usersettings.timezone' => 'UTC']);
        session(['usersettings.language' => 'en-US']);
        session(['usersettings.date_format' => 'Y-m-d']);
        session(['usersettings.time_format' => 'H:i']);
        session(['userdata.id' => 1]);

        $envMock = $this->make(EnvironmentCore::class, [
            'defaultTimezone' => 'UTC',
            'language' => 'en-US',
        ]);
        app()->instance(EnvironmentCore::class, $envMock);

        CarbonImmutable::mixin(new CarbonMacros('UTC', 'en-US', 'Y-m-d', 'H:i'));
    }

    /**
     * Builds a real Projects service, allowing each dependency to be overridden
     * with a stub so we can observe persistence/queue calls.
     */
    private function makeService(
        ?ProjectRepository $projectRepo = null,
        ?TicketRepository $ticketRepo = null,
        ?SettingRepository $settingsRepo = null,
        ?QueueRepository $queueRepo = null,
        ?UserRepository $userRepo = null,
        ?CommentRepository $commentRepo = null,
        ?ClientRepository $clientRepo = null,
        ?LanguageCore $language = null,
    ): ProjectService {
        $language ??= $this->make(LanguageCore::class, [
            '__' => fn ($key) => $key,
        ]);

        return new ProjectService(
            $projectRepo ?? $this->make(ProjectRepository::class),
            $ticketRepo ?? $this->make(TicketRepository::class),
            $settingsRepo ?? $this->make(SettingRepository::class),
            $language,
            $this->make(Messengers::class),
            $this->make(NotificationService::class),
            $this->make(FileService::class),
            $this->make(Avatarcreator::class),
            $queueRepo ?? $this->make(QueueRepository::class),
            $userRepo ?? $this->make(UserRepository::class),
            $commentRepo ?? $this->make(CommentRepository::class),
            $clientRepo ?? $this->make(ClientRepository::class),
        );
    }

    public function test_get_project_hub_data_builds_unique_client_map_and_returns_all_projects_when_no_filter(): void
    {
        $projectRepo = $this->make(ProjectRepository::class, [
            'getUserProjects' => fn () => [
                ['id' => 1, 'clientId' => 10, 'clientName' => 'Acme'],
                ['id' => 2, 'clientId' => 10, 'clientName' => 'Acme'],
                ['id' => 3, 'clientId' => 20, 'clientName' => 'Globex'],
            ],
        ]);

        $result = $this->makeService(projectRepo: $projectRepo)->getProjectHubData(1, null);

        $this->assertCount(3, $result['allProjects']);
        $this->assertCount(2, $result['clients'], 'Duplicate clients must be collapsed into a unique map');
        $this->assertSame('Acme', $result['clients'][10]['name']);
        $this->assertSame('Globex', $result['clients'][20]['name']);
        $this->assertSame('', $result['currentClientName']);
        $this->assertSame('', $result['currentClient']);
    }

    public function test_get_project_hub_data_filters_projects_by_selected_client(): void
    {
        $projectRepo = $this->make(ProjectRepository::class, [
            'getUserProjects' => fn () => [
                ['id' => 1, 'clientId' => 10, 'clientName' => 'Acme'],
                ['id' => 2, 'clientId' => 20, 'clientName' => 'Globex'],
            ],
        ]);
        $clientRepo = $this->make(ClientRepository::class, [
            'getClient' => fn () => ['id' => 10, 'name' => 'Acme'],
        ]);

        $result = $this->makeService(projectRepo: $projectRepo, clientRepo: $clientRepo)->getProjectHubData(1, 10);

        $this->assertCount(1, $result['allProjects'], 'Only projects of the selected client are returned');
        $this->assertSame(1, $result['allProjects'][0]['id']);
        $this->assertCount(2, $result['clients'], 'The client map is still built from all projects');
        $this->assertSame('Acme', $result['currentClientName']);
        $this->assertSame(10, $result['currentClient']);
    }

    public function test_notify_project_created_queues_only_users_who_opted_in(): void
    {
        $projectRepo = $this->make(ProjectRepository::class, [
            'getUsersAssignedToProject' => fn () => [
                ['username' => 'wants@example.com', 'notifications' => 1, 'modified' => ''],
                ['username' => 'muted@example.com', 'notifications' => 0, 'modified' => ''],
            ],
        ]);

        $captured = null;
        $queueRepo = $this->make(QueueRepository::class, [
            'queueMessageToUsers' => function ($recipients, $message, $subject, $projectId) use (&$captured) {
                $captured = compact('recipients', 'message', 'subject', 'projectId');
            },
        ]);

        $this->makeService(projectRepo: $projectRepo, queueRepo: $queueRepo)
            ->notifyProjectCreated(42, 'My Project', 'Author');

        $this->assertNotNull($captured, 'A message must be queued');
        $this->assertSame(['wants@example.com'], $captured['recipients'], 'Users with notifications=0 are excluded');
        $this->assertSame(42, $captured['projectId']);
    }

    public function test_save_zulip_webhook_persists_when_all_fields_present(): void
    {
        $savedKey = null;
        $settingsRepo = $this->make(SettingRepository::class, [
            'saveSetting' => function ($key, $value) use (&$savedKey) {
                $savedKey = $key;

                return true;
            },
        ]);

        $result = $this->makeService(settingsRepo: $settingsRepo)->saveZulipWebhook(7, [
            'zulipURL' => 'https://zulip.example.com',
            'zulipEmail' => 'bot@example.com',
            'zulipBotKey' => 'key123',
            'zulipStream' => 'general',
            'zulipTopic' => 'updates',
        ]);

        $this->assertTrue($result['saved']);
        $this->assertSame('projectsettings.7.zulipHook', $savedKey);
        $this->assertSame('https://zulip.example.com', $result['hook']['zulipURL']);
    }

    public function test_save_zulip_webhook_does_not_persist_when_a_field_is_missing(): void
    {
        $saveCalls = 0;
        $settingsRepo = $this->make(SettingRepository::class, [
            'saveSetting' => function () use (&$saveCalls) {
                $saveCalls++;

                return true;
            },
        ]);

        $result = $this->makeService(settingsRepo: $settingsRepo)->saveZulipWebhook(7, [
            'zulipURL' => 'https://zulip.example.com',
            'zulipEmail' => '',
            'zulipBotKey' => 'key123',
            'zulipStream' => 'general',
            'zulipTopic' => 'updates',
        ]);

        $this->assertFalse($result['saved']);
        $this->assertSame(0, $saveCalls, 'Incomplete zulip config must not be persisted');
        $this->assertSame('', $result['hook']['zulipEmail']);
    }

    public function test_get_project_integration_settings_returns_empty_zulip_hook_when_unset(): void
    {
        $settingsRepo = $this->make(SettingRepository::class, [
            'getSetting' => fn () => '',
        ]);

        $settings = $this->makeService(settingsRepo: $settingsRepo)->getProjectIntegrationSettings(5);

        $this->assertSame('', $settings['mattermostWebhookURL']);
        $this->assertArrayHasKey('discordWebhookURL1', $settings);
        $this->assertArrayHasKey('discordWebhookURL3', $settings);
        $this->assertSame([
            'zulipURL' => '',
            'zulipEmail' => '',
            'zulipBotKey' => '',
            'zulipStream' => '',
            'zulipTopic' => '',
        ], $settings['zulipHook']);
    }

    public function test_get_project_integration_settings_unserializes_stored_zulip_hook(): void
    {
        $storedHook = serialize(['zulipURL' => 'https://z.example.com', 'zulipTopic' => 't']);
        $settingsRepo = $this->make(SettingRepository::class, [
            'getSetting' => fn ($key) => str_ends_with($key, 'zulipHook') ? $storedHook : '',
        ]);

        $settings = $this->makeService(settingsRepo: $settingsRepo)->getProjectIntegrationSettings(5);

        $this->assertSame('https://z.example.com', $settings['zulipHook']['zulipURL']);
        $this->assertSame('t', $settings['zulipHook']['zulipTopic']);
    }

    public function test_get_project_card_data_sets_last_update_and_status_from_first_comment(): void
    {
        $ticketRepo = $this->make(TicketRepository::class, [
            'getAverageTodoSize' => fn () => 0,
            'getFirstTicket' => fn () => null,
        ]);
        $projectRepo = $this->make(ProjectRepository::class, [
            'getUsersAssignedToProject' => fn () => [],
        ]);
        $commentRepo = $this->make(CommentRepository::class, [
            'getComments' => fn () => [
                ['id' => 99, 'status' => 'on_track', 'text' => 'Looking good'],
            ],
        ]);

        $card = $this->makeService(
            projectRepo: $projectRepo,
            ticketRepo: $ticketRepo,
            commentRepo: $commentRepo,
        )->getProjectCardData(3);

        $this->assertSame(3, $card['id']);
        $this->assertSame('on_track', $card['status']);
        $this->assertIsArray($card['lastUpdate']);
        $this->assertSame(99, $card['lastUpdate']['id']);
    }

    public function test_get_project_card_data_defaults_when_no_comments(): void
    {
        $ticketRepo = $this->make(TicketRepository::class, [
            'getAverageTodoSize' => fn () => 0,
            'getFirstTicket' => fn () => null,
        ]);
        $projectRepo = $this->make(ProjectRepository::class, [
            'getUsersAssignedToProject' => fn () => [],
        ]);
        $commentRepo = $this->make(CommentRepository::class, [
            'getComments' => fn () => [],
        ]);

        $card = $this->makeService(
            projectRepo: $projectRepo,
            ticketRepo: $ticketRepo,
            commentRepo: $commentRepo,
        )->getProjectCardData(3);

        $this->assertFalse($card['lastUpdate']);
        $this->assertSame('', $card['status']);
    }

    // ---------------------------------------------------------------------
    // Authorized JSON-RPC entry points for project sort/status/patch.
    // The /api/projects controller (which had a route-level gate) was retired,
    // so these wrappers must self-authorize: manager+ AND access to each project.
    // ---------------------------------------------------------------------

    public function test_user_can_manage_project_allows_admin_without_explicit_assignment(): void
    {
        session(['userdata.role' => 'admin']);

        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => false,
        ]);

        // Admins/owners manage every project regardless of assignment.
        $this->assertTrue($this->makeService(projectRepo: $projectRepo)->userCanManageProject(99));
    }

    public function test_user_can_manage_project_requires_assignment_for_managers(): void
    {
        session(['userdata.role' => 'manager']);

        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => false,
        ]);

        $this->assertFalse($this->makeService(projectRepo: $projectRepo)->userCanManageProject(99));
    }

    public function test_patch_project_status_and_sorting_rejects_non_manager(): void
    {
        session(['userdata.role' => 'editor']);

        $patchCalls = 0;
        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => true,
            'patch' => function () use (&$patchCalls) {
                $patchCalls++;

                return true;
            },
        ]);

        $thrown = null;
        try {
            $this->makeService(projectRepo: $projectRepo)
                ->patchProjectStatusAndSorting(['3' => 'item[]=5']);
        } catch (AuthorizationException $e) {
            $thrown = $e;
        }

        $this->assertInstanceOf(AuthorizationException::class, $thrown, 'Editors must not be able to re-status projects');
        $this->assertSame(0, $patchCalls, 'Unauthorized request must not persist any sorting');
    }

    public function test_patch_project_status_and_sorting_rejects_manager_without_project_access(): void
    {
        session(['userdata.role' => 'manager']);

        $patchCalls = 0;
        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => false,
            'patch' => function () use (&$patchCalls) {
                $patchCalls++;

                return true;
            },
        ]);

        $thrown = null;
        try {
            $this->makeService(projectRepo: $projectRepo)
                ->patchProjectStatusAndSorting(['3' => 'item[]=5']);
        } catch (AuthorizationException $e) {
            $thrown = $e;
        }

        $this->assertInstanceOf(AuthorizationException::class, $thrown, 'A manager smuggling a project they cannot access must be blocked');
        $this->assertSame(0, $patchCalls);
    }

    public function test_patch_project_status_and_sorting_allows_manager_with_access(): void
    {
        session(['userdata.role' => 'manager']);

        $patched = [];
        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => true,
            'patch' => function ($id, $values) use (&$patched) {
                $patched[] = ['id' => $id, 'values' => $values];

                return true;
            },
        ]);

        $result = $this->makeService(projectRepo: $projectRepo)
            ->patchProjectStatusAndSorting(['3' => 'item[]=5&item[]=6']);

        $this->assertTrue($result);
        $this->assertCount(2, $patched, 'Both serialized projects must be re-sorted');
        $this->assertSame('5', $patched[0]['id']);
        $this->assertSame(3, (int) $patched[0]['values']['state']);
    }

    public function test_sort_projects_rejects_when_user_cannot_manage_target_project(): void
    {
        session(['userdata.role' => 'manager']);

        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => false,
        ]);

        $thrown = null;
        try {
            $this->makeService(projectRepo: $projectRepo)->sortProjects(['pgm-5' => 1]);
        } catch (AuthorizationException $e) {
            $thrown = $e;
        }

        $this->assertInstanceOf(AuthorizationException::class, $thrown);
    }

    public function test_sort_projects_resolves_ticket_to_its_project_for_authorization(): void
    {
        session(['userdata.role' => 'manager']);

        $checkedProjectId = null;
        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => function ($userId, $projectId) use (&$checkedProjectId) {
                $checkedProjectId = $projectId;

                return false; // deny so we stop before delegating to the Tickets service
            },
        ]);
        $ticket = new \Leantime\Domain\Tickets\Models\Tickets;
        $ticket->projectId = 9;
        $ticketRepo = $this->make(TicketRepository::class, [
            'getTicket' => fn () => $ticket,
        ]);

        $thrown = null;
        try {
            $this->makeService(projectRepo: $projectRepo, ticketRepo: $ticketRepo)
                ->sortProjects(['ticket-7' => 1]);
        } catch (AuthorizationException $e) {
            $thrown = $e;
        }

        $this->assertInstanceOf(AuthorizationException::class, $thrown);
        $this->assertSame(9, $checkedProjectId, 'Authorization must check the ticket\'s project, not the ticket id');
    }

    public function test_patch_project_rejects_non_manager(): void
    {
        session(['userdata.role' => 'editor']);

        $patchCalls = 0;
        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => true,
            'patch' => function () use (&$patchCalls) {
                $patchCalls++;

                return true;
            },
        ]);

        $thrown = null;
        try {
            $this->makeService(projectRepo: $projectRepo)->patchProject(5, ['sortIndex' => 2]);
        } catch (AuthorizationException $e) {
            $thrown = $e;
        }

        $this->assertInstanceOf(AuthorizationException::class, $thrown);
        $this->assertSame(0, $patchCalls);
    }

    public function test_patch_project_allows_manager_and_strips_control_fields(): void
    {
        session(['userdata.role' => 'manager']);

        $patchedValues = null;
        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => true,
            'patch' => function ($id, $values) use (&$patchedValues) {
                $patchedValues = $values;

                return true;
            },
        ]);

        $result = $this->makeService(projectRepo: $projectRepo)
            ->patchProject(5, ['act' => 'projects.x', 'id' => 5, 'sortIndex' => 2, 'start' => '2026-01-01']);

        $this->assertTrue($result);
        $this->assertArrayNotHasKey('act', $patchedValues, 'Control fields must be stripped before persisting');
        $this->assertArrayNotHasKey('id', $patchedValues);
        $this->assertSame(2, $patchedValues['sortIndex']);
    }

    // ---- permission-engine: recursion guardrail ---------------------------

    /**
     * THE recursion guardrail. The permission engine calls isUserAssignedToProject() and
     * getProjectRole() during every project-scoped authorization, so those two methods must never
     * invoke the engine in-body — otherwise authorize() → currentUserCan() → isUserAssignedToProject()
     * → authorize() → ∞. A PermissionService stub that fails the test if touched proves it.
     */
    public function test_access_resolution_methods_never_invoke_the_permission_engine(): void
    {
        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => true,
            'getUserProjectRelation' => fn () => [['projectRole' => 'editor']],
        ]);

        $tripwire = $this->make(\Leantime\Core\Auth\Permissions\PermissionService::class, [
            'currentUserCan' => fn () => $this->fail('isUserAssignedToProject/getProjectRole must NOT call the permission engine (infinite-recursion guard).'),
            'authorize' => fn () => $this->fail('access-resolution methods must NOT authorize in-body (infinite-recursion guard).'),
        ]);

        $service = $this->makeService(projectRepo: $projectRepo);
        $service->setPermissionService($tripwire);

        // Neither call may touch the engine.
        $this->assertTrue($service->isUserAssignedToProject(1, 5));
        $this->assertSame('editor', $service->getProjectRole(1, 5));
    }

    /**
     * getProjectRole() must resolve "no explicit role" to '' so callers fall back to the global
     * role. This locks in the fix for the "Inherit" lockout: the legacy 0 role (written when
     * "inherit" was cast to int), a missing relation, unknown/junk keys, and admin/owner keys all
     * map to '', while a real assignable key is returned unchanged.
     *
     * @dataProvider projectRoleResolutionProvider
     */
    public function test_get_project_role_resolves_inherit_and_junk_to_empty(mixed $stored, string $expected): void
    {
        $relation = $stored === '__none__' ? [] : [['projectRole' => $stored]];
        $projectRepo = $this->make(ProjectRepository::class, [
            'getUserProjectRelation' => fn () => $relation,
        ]);

        $service = $this->makeService(projectRepo: $projectRepo);

        $this->assertSame($expected, $service->getProjectRole(1, 5));
    }

    public static function projectRoleResolutionProvider(): array
    {
        return [
            'legacy int 0 -> inherit' => [0, ''],
            'legacy string 0 -> inherit' => ['0', ''],
            'empty string -> inherit' => ['', ''],
            'no relation row -> inherit' => ['__none__', ''],
            'unknown numeric key -> inherit' => ['999', ''],
            'admin key not assignable -> inherit' => ['40', ''],
            'owner key not assignable -> inherit' => ['50', ''],
            'valid editor key preserved' => ['20', '20'],
            'valid readonly key preserved' => ['5', '5'],
        ];
    }

    /**
     * Reflection lock: the engine-reachable access methods must carry NO #[RequiresPermission]
     * dispatch attribute (a dispatch gate on them would re-enter the engine), and the mutations/reads
     * must carry the expected gate. Locks the recursion-safe contract in CI.
     */
    public function test_rpc_surface_contract(): void
    {
        $gate = function (string $method): ?array {
            $attrs = (new \ReflectionMethod(ProjectService::class, $method))
                ->getAttributes(\Leantime\Core\Auth\Permissions\RequiresPermission::class);
            if ($attrs === []) {
                return null;
            }
            $a = $attrs[0]->newInstance();

            return ['permission' => $a->permission, 'global' => $a->global, 'projectIdParam' => $a->projectIdParam];
        };

        // Engine-reachable / access-resolution: MUST be ungated (the recursion guard). Note
        // getUsersAssignedToProject is NOT in this set — the engine never calls it, so it is safely
        // view-gated below to close its member-list IDOR.
        foreach (['getProjectRole', 'isUserAssignedToProject', 'getUserProjectRelation', 'userCanManageProject', 'getProjectsUserHasAccessTo'] as $m) {
            $this->assertNull($gate($m), "$m must carry NO #[RequiresPermission] (recursion guard)");
        }

        // Mutations: global manager+.
        foreach (['addProject' => 'projects.create', 'duplicateProject' => 'projects.create', 'editProject' => 'projects.edit', 'patch' => 'projects.edit', 'patchProject' => 'projects.edit', 'updateProjectUsers' => 'projects.edit', 'saveSlackWebhook' => 'projects.edit', 'deleteProject' => 'projects.delete'] as $m => $perm) {
            $g = $gate($m);
            $this->assertNotNull($g, "$m must be gated");
            $this->assertSame($perm, $g['permission'], $m);
            $this->assertTrue($g['global'], "$m must be global-scoped (manager+ company-wide)");
        }

        // By-id reads: project-scoped view.
        foreach (['getProject', 'getProjectProgress', 'getProjectName', 'getProjectIntegrationSettings', 'getProjectCardData', 'getUsersAssignedToProject'] as $m) {
            $g = $gate($m);
            $this->assertNotNull($g, "$m must be gated");
            $this->assertSame('projects.view', $g['permission'], $m);
            $this->assertNotNull($g['projectIdParam'], "$m must bind to the requested project id");
        }
    }

    /**
     * The $userId-param reads pin to the SESSION user for non-admins, closing the cross-user spoof
     * (an RPC caller could otherwise list another user's projects by passing a foreign id).
     */
    public function test_assigned_to_user_reads_pin_to_session_user_for_non_admins(): void
    {
        session(['userdata.id' => 1]);  // non-admin session user

        $capturedUserId = null;
        $projectRepo = $this->make(ProjectRepository::class, [
            'getUserProjectRelation' => function ($userId) use (&$capturedUserId) {
                $capturedUserId = $userId;

                return [];
            },
        ]);

        // Caller passes a FOREIGN userId (99); the read must be scoped to the session user (1).
        $this->makeService(projectRepo: $projectRepo)->getProjectIdAssignedToUser(99);

        $this->assertSame(1, $capturedUserId, 'a non-admin must not be able to read another user\'s project assignments');
    }

    // ---- Project hierarchy safety (#3540: cyclic parents hung every page via the project selector) ----

    public function test_find_my_children_builds_nested_hierarchy(): void
    {
        $projects = [
            ['id' => 1, 'parent' => 0, 'name' => 'Program'],
            ['id' => 2, 'parent' => 1, 'name' => 'Project'],
            ['id' => 3, 'parent' => 2, 'name' => 'Subproject'],
            ['id' => 4, 'parent' => 0, 'name' => 'Standalone'],
        ];

        $hierarchy = $this->makeService()->findMyChildren(0, $projects);

        $this->assertCount(2, $hierarchy);
        $this->assertSame(2, $hierarchy[0]['children'][0]['id']);
        $this->assertSame(3, $hierarchy[0]['children'][0]['children'][0]['id']);
        $this->assertArrayNotHasKey('children', $hierarchy[1]);
    }

    public function test_find_my_children_does_not_recurse_on_self_referential_parent(): void
    {
        $projects = [
            ['id' => 1, 'parent' => 0, 'name' => 'Root'],
            ['id' => 2, 'parent' => 2, 'name' => 'Self-parented'],
        ];

        $hierarchy = $this->makeService()->findMyChildren(0, $projects);

        $this->assertCount(1, $hierarchy, 'must terminate instead of recursing on a self-parented project');
        $this->assertSame(1, $hierarchy[0]['id']);
    }

    public function test_clean_parent_relationship_reroots_self_parent_and_cycles(): void
    {
        $projects = [
            ['id' => 1, 'parent' => 1, 'name' => 'Self-parented'],
            ['id' => 2, 'parent' => 3, 'name' => 'Cycle A'],
            ['id' => 3, 'parent' => 2, 'name' => 'Cycle B'],
            ['id' => 4, 'parent' => 99, 'name' => 'Orphan'],
            ['id' => 5, 'parent' => 1, 'name' => 'Valid child'],
        ];

        $service = $this->makeService();
        $clean = $service->cleanParentRelationship($projects);
        $byId = array_column($clean, null, 'id');

        $this->assertSame(0, $byId[1]['parent'], 'self-parent must be re-rooted');
        $this->assertSame(0, $byId[2]['parent'], 'cycle members must be re-rooted');
        $this->assertSame(0, $byId[3]['parent'], 'cycle members must be re-rooted');
        $this->assertSame(0, $byId[4]['parent'], 'orphans must be re-rooted');
        $this->assertSame(1, $byId[5]['parent'], 'valid parent links must be preserved');

        // The full pipeline must terminate and surface every project.
        $hierarchy = $service->findMyChildren(0, $clean);
        $this->assertCount(4, $hierarchy);
    }
}

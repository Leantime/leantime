<?php

namespace Unit\app\Domain\Users\Services;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\Avatarcreator;
use Leantime\Core\UI\Theme as ThemeCore;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
use Leantime\Domain\Files\Services\Files;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Leantime\Domain\Users\Services\Users as UserService;
use Unit\TestCase;

/**
 * Unit tests for the Users service helpers extracted during the
 * thin-controller refactor (saveModalDismissal).
 */
class UsersServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Builds a real Users service with mocked dependencies, injecting the
     * provided (stubbed) repository so we can observe persistence calls.
     * Optional overrides let individual tests swap in stubbed collaborators.
     *
     * @param  array<string, mixed>  $overrides  Keyed by dependency short name.
     */
    private function makeService(UserRepository $userRepo, array $overrides = []): UserService
    {
        return new UserService(
            $userRepo,
            $overrides['language'] ?? $this->make(LanguageCore::class),
            $overrides['projectRepository'] ?? $this->make(ProjectRepository::class),
            $overrides['clientRepo'] ?? $this->make(ClientRepository::class),
            $overrides['authService'] ?? $this->make(AuthService::class),
            $overrides['fileService'] ?? $this->make(Files::class),
            $overrides['avatarcreator'] ?? $this->make(Avatarcreator::class),
            $overrides['settingsService'] ?? $this->make(SettingService::class),
            $overrides['themeCore'] ?? $this->make(ThemeCore::class),
            $overrides['projectService'] ?? $this->make(ProjectService::class),
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        session(['userdata.id' => 1]);
        session()->forget('usersettings');
    }

    public function test_session_only_dismissal_records_session_without_persisting(): void
    {
        $persistCalls = 0;
        $repo = $this->make(UserRepository::class, [
            'patchUser' => function () use (&$persistCalls) {
                $persistCalls++;

                return true;
            },
        ]);

        $result = $this->makeService($repo)->saveModalDismissal('welcomeModal', false);

        $this->assertTrue($result);
        $this->assertSame(1, session('usersettings.modals.welcomeModal'));
        $this->assertSame(0, $persistCalls, 'A non-permanent dismissal must not touch the repository');
    }

    public function test_permanent_dismissal_persists_to_user_settings(): void
    {
        $persistCalls = 0;
        $repo = $this->make(UserRepository::class, [
            'patchUser' => function ($id, $params) use (&$persistCalls) {
                $persistCalls++;

                // The service must persist the serialized usersettings blob.
                $this->assertArrayHasKey('settings', $params);

                return true;
            },
        ]);

        $result = $this->makeService($repo)->saveModalDismissal('welcomeModal', true);

        $this->assertTrue($result);
        $this->assertSame('1', session('usersettings.modals.welcomeModal'));
        $this->assertSame(1, $persistCalls, 'A permanent dismissal must persist via the repository');
    }

    public function test_get_user_project_ids_flattens_relation_rows(): void
    {
        $repo = $this->make(UserRepository::class);
        $projectService = $this->make(ProjectService::class, [
            'getUserProjectRelation' => fn () => [
                ['projectId' => 5],
                ['projectId' => 9],
                ['projectId' => 12],
            ],
        ]);

        $ids = $this->makeService($repo, ['projectService' => $projectService])->getUserProjectIds(3);

        $this->assertSame([5, 9, 12], $ids);
    }

    public function test_validate_user_update_rejects_empty_username(): void
    {
        $repo = $this->make(UserRepository::class);
        $service = $this->makeService($repo);

        $result = $service->validateUserUpdate(
            ['user' => ''],
            ['username' => 'old@example.com'],
            7,
            []
        );

        $this->assertSame('passwords_dont_match', $result);
    }

    public function test_validate_user_update_rejects_invalid_email(): void
    {
        $repo = $this->make(UserRepository::class);
        $service = $this->makeService($repo);

        $result = $service->validateUserUpdate(
            ['user' => 'not-an-email'],
            ['username' => 'old@example.com'],
            7,
            []
        );

        $this->assertSame('no_valid_email', $result);
    }

    public function test_validate_user_update_rejects_taken_email_on_change(): void
    {
        $repo = $this->make(UserRepository::class, [
            'usernameExist' => fn () => true,
        ]);
        $service = $this->makeService($repo);

        $result = $service->validateUserUpdate(
            ['user' => 'new@example.com'],
            ['username' => 'old@example.com'],
            7,
            []
        );

        $this->assertSame('user_exists', $result);
    }

    public function test_validate_user_update_passes_for_unchanged_valid_email(): void
    {
        $repo = $this->make(UserRepository::class, [
            'usernameExist' => fn () => true,
        ]);
        $service = $this->makeService($repo);

        // Email unchanged, so usernameExist must NOT block it.
        $result = $service->validateUserUpdate(
            ['user' => 'same@example.com'],
            ['username' => 'same@example.com'],
            7,
            []
        );

        $this->assertSame('valid', $result);
    }

    public function test_invite_new_user_rejects_invalid_email(): void
    {
        $repo = $this->make(UserRepository::class, [
            'usernameExist' => fn () => false,
        ]);
        $service = $this->makeService($repo);

        $result = $service->inviteNewUser(
            ['user' => 'nope'],
            sessionClientId: null,
            isManager: false
        );

        $this->assertSame('no_valid_email', $result);
    }

    public function test_invite_new_user_rejects_existing_user(): void
    {
        $repo = $this->make(UserRepository::class, [
            'usernameExist' => fn () => true,
        ]);
        $service = $this->makeService($repo);

        $result = $service->inviteNewUser(
            ['user' => 'taken@example.com'],
            sessionClientId: null,
            isManager: false
        );

        $this->assertSame('user_exists', $result);
    }

    public function test_change_own_password_rejects_wrong_current_password(): void
    {
        $repo = $this->make(UserRepository::class, [
            'getUser' => fn () => [
                'id' => 1,
                'password' => password_hash('correct-horse', PASSWORD_DEFAULT),
                'firstname' => 'A',
                'lastname' => 'B',
                'username' => 'a@b.com',
                'phone' => '',
                'notifications' => 1,
                'twoFAEnabled' => 0,
            ],
        ]);
        $service = $this->makeService($repo);

        $result = $service->changeOwnPassword(1, 'wrong', 'NewPass1!', 'NewPass1!');

        $this->assertSame('previous_password_incorrect', $result);
    }

    public function test_change_own_password_rejects_mismatched_confirmation(): void
    {
        $repo = $this->make(UserRepository::class, [
            'getUser' => fn () => [
                'id' => 1,
                'password' => password_hash('correct-horse', PASSWORD_DEFAULT),
                'firstname' => 'A',
                'lastname' => 'B',
                'username' => 'a@b.com',
                'phone' => '',
                'notifications' => 1,
                'twoFAEnabled' => 0,
            ],
        ]);
        $service = $this->makeService($repo);

        $result = $service->changeOwnPassword(1, 'correct-horse', 'NewPass1!', 'Different1!');

        $this->assertSame('passwords_dont_match', $result);
    }

    public function test_change_own_password_persists_on_success(): void
    {
        $savedValues = null;
        $repo = $this->make(UserRepository::class, [
            'getUser' => fn () => [
                'id' => 1,
                'password' => password_hash('correct-horse', PASSWORD_DEFAULT),
                'firstname' => 'A',
                'lastname' => 'B',
                'username' => 'a@b.com',
                'phone' => '',
                'notifications' => 1,
                'twoFAEnabled' => 0,
            ],
            'editOwn' => function ($values) use (&$savedValues) {
                $savedValues = $values;

                return true;
            },
        ]);
        $service = $this->makeService($repo);

        $result = $service->changeOwnPassword(1, 'correct-horse', 'NewPass1!', 'NewPass1!');

        $this->assertSame('success', $result);
        $this->assertSame('NewPass1!', $savedValues['password']);
    }

    public function test_save_own_profile_blocks_duplicate_email(): void
    {
        $editCalls = 0;
        $repo = $this->make(UserRepository::class, [
            'getUser' => fn () => [
                'id' => 1,
                'firstname' => 'A',
                'lastname' => 'B',
                'username' => 'old@example.com',
                'phone' => '',
                'notifications' => 1,
                'twoFAEnabled' => 0,
            ],
            'usernameExist' => fn () => true,
            'editOwn' => function () use (&$editCalls) {
                $editCalls++;

                return true;
            },
        ]);
        $service = $this->makeService($repo);

        $result = $service->saveOwnProfile(1, ['user' => 'taken@example.com']);

        $this->assertSame('user_exists', $result);
        $this->assertSame(0, $editCalls, 'A duplicate email must not be persisted');
    }

    // ---------------------------------------------------------------------
    // searchProjectUsers() — JSON-RPC entry for the @mention autocomplete.
    // ---------------------------------------------------------------------

    public function test_search_project_users_filters_by_query(): void
    {
        session(['userdata' => ['id' => 1], 'currentProject' => 5]);

        $projectRepository = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => true,
            'getProject' => fn () => ['psettings' => 'restricted', 'clientId' => 0],
            'getUsersAssignedToProject' => fn () => [
                ['id' => 1, 'firstname' => 'Alice'],
                ['id' => 2, 'firstname' => 'Bob'],
            ],
        ]);

        $users = $this->makeService($this->make(UserRepository::class), ['projectRepository' => $projectRepository])
            ->searchProjectUsers(5, 'alice');

        $this->assertCount(1, $users);
        $this->assertSame('Alice', $users[0]['firstname']);
    }

    public function test_search_project_users_returns_empty_without_project_access(): void
    {
        session(['userdata' => ['id' => 1], 'currentProject' => 5]);

        $projectRepository = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => false,
        ]);

        $users = $this->makeService($this->make(UserRepository::class), ['projectRepository' => $projectRepository])
            ->searchProjectUsers(5);

        $this->assertSame([], $users);
    }

    // ---------------------------------------------------------------------
    // Authorization. The company-wide manage-others methods
    // (editUser/updateUser/addUser/getAll/…) gate via the dispatch-time
    // #[RequiresPermission(global: true)] attribute (covered by PermissionEnforcerTest).
    // These two methods authorize in their own body, so they gate on direct calls too.
    // ---------------------------------------------------------------------

    private function denyingPermissions(): PermissionService
    {
        return $this->make(PermissionService::class, [
            'currentUserCan' => fn () => false,
            'authorize' => function (): void {
                throw new AuthorizationException;
            },
        ]);
    }

    private function allowingPermissions(): PermissionService
    {
        return $this->make(PermissionService::class, [
            'currentUserCan' => fn () => true,
            'authorize' => fn () => null,
        ]);
    }

    public function test_delete_user_throws_without_delete_permission(): void
    {
        $service = $this->makeService($this->make(UserRepository::class));
        $service->setPermissionService($this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->deleteUser(5);
    }

    public function test_patch_user_allows_self_with_limited_fields_without_edit_permission(): void
    {
        session(['userdata' => ['id' => 7]]);

        $patched = [];
        $service = $this->makeService($this->make(UserRepository::class, [
            'patchUser' => function ($id, $fields) use (&$patched) {
                $patched = ['id' => $id, 'fields' => $fields];

                return true;
            },
        ]));
        $service->setPermissionService($this->denyingPermissions()); // no users.edit

        // Editing OWN account (id === session user) is allowed even without users.edit...
        $result = $service->patchUser(7, ['firstname' => 'Bob', 'role' => '50']);

        $this->assertTrue($result);
        $this->assertSame(7, $patched['id']);
        $this->assertArrayHasKey('firstname', $patched['fields']);
        // ...but the privileged 'role' field is stripped — no self privilege-escalation.
        $this->assertArrayNotHasKey('role', $patched['fields']);
    }

    public function test_patch_user_denies_other_account_without_edit_permission(): void
    {
        session(['userdata' => ['id' => 7]]);

        $service = $this->makeService($this->make(UserRepository::class, [
            'patchUser' => fn () => true,
        ]));
        $service->setPermissionService($this->denyingPermissions());

        // Patching ANOTHER account without users.edit must fail (closes the RPC escalation hole).
        $this->assertFalse($service->patchUser(99, ['role' => '50']));
    }

    public function test_patch_user_allows_other_account_with_edit_permission(): void
    {
        session(['userdata' => ['id' => 7]]);

        $patched = [];
        $service = $this->makeService($this->make(UserRepository::class, [
            'patchUser' => function ($id, $fields) use (&$patched) {
                $patched = ['id' => $id, 'fields' => $fields];

                return true;
            },
        ]));
        $service->setPermissionService($this->allowingPermissions()); // has users.edit

        $result = $service->patchUser(99, ['role' => '20']);

        $this->assertTrue($result);
        $this->assertSame(99, $patched['id']);
        // A users.edit holder may set privileged fields on another account.
        $this->assertArrayHasKey('role', $patched['fields']);
    }

    public function test_self_service_methods_ignore_caller_supplied_id_and_pin_to_session(): void
    {
        // Self-service methods (editOwn/saveOwn*/getOwn*/changeOwnPassword) must operate on the
        // authenticated user only — over JSON-RPC a caller controls the $userId argument, so a
        // foreign id must NOT be honored (otherwise it is a cross-account IDOR). Representative
        // check via changeOwnPassword: the credential lookup must hit the SESSION user (7), not
        // the attacker-supplied id (99).
        session(['userdata' => ['id' => 7]]);

        $seenId = null;
        $repo = $this->make(UserRepository::class, [
            'getUser' => function ($id) use (&$seenId) {
                $seenId = $id;

                return [
                    'id' => $id,
                    'password' => password_hash('correct-horse', PASSWORD_DEFAULT),
                    'firstname' => 'A', 'lastname' => 'B', 'username' => 'a@b.com',
                    'phone' => '', 'notifications' => 1, 'twoFAEnabled' => 0,
                ];
            },
        ]);

        $this->makeService($repo)->changeOwnPassword(99, 'wrong', 'NewPass1!', 'NewPass1!');

        $this->assertSame(7, $seenId, 'self-service must pin to the session user, not the caller-supplied id');
    }
}

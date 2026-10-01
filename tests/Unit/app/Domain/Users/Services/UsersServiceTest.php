<?php

namespace Unit\app\Domain\Users\Services;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\Avatarcreator;
use Leantime\Core\UI\Theme as ThemeCore;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
use Leantime\Domain\Files\Services\Files;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Users\Exceptions\WebhookSettingNotSavedException;
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

    // ---------------------------------------------------------------------
    // Personal notification webhook (saveOwnNotificationPreferences /
    // getNotificationPreferences).
    // ---------------------------------------------------------------------

    /**
     * A user repository that serves a plain profile row and counts editOwn writes.
     */
    private function profileRepo(int &$editOwnCalls = 0): UserRepository
    {
        return $this->make(UserRepository::class, [
            'getUser' => fn ($id) => [
                'id' => $id, 'firstname' => 'A', 'lastname' => 'B', 'username' => 'a@b.com',
                'phone' => '', 'notifications' => 1, 'twoFAEnabled' => 0,
            ],
            'editOwn' => function () use (&$editOwnCalls) {
                $editOwnCalls++;

                return true;
            },
        ]);
    }

    /**
     * A settings service backed by an in-memory map, recording every write.
     *
     * @param  array<string, mixed>  $stored  Initial setting values; a write that reports success replaces what reads return.
     * @param  array<string, mixed>  $saved  Receives key => value for each saveSetting call.
     * @param  \Closure|null  $saveResult  fn ($key, $value): bool — the write's result (may throw); default true.
     */
    private function settingsStore(array $stored, array &$saved = [], ?\Closure $saveResult = null): SettingService
    {
        return $this->make(SettingService::class, [
            'getSetting' => function ($key, $default = false) use (&$stored) {
                return $stored[$key] ?? $default;
            },
            'saveSetting' => function ($key, $value) use (&$stored, &$saved, $saveResult) {
                $saved[$key] = $value;
                $writeSucceeded = $saveResult ? $saveResult($key, $value) : true;
                if ($writeSucceeded) {
                    $stored[$key] = $value;
                }

                return $writeSucceeded;
            },
        ]);
    }

    /**
     * The stored personal webhook: one JSON value holding URL and opt-in.
     */
    private function webhookSetting(string $url, bool $enabled): string
    {
        return json_encode(['url' => $url, 'enabled' => $enabled]);
    }

    public function test_save_notification_preferences_stores_the_webhook_for_the_session_user_only(): void
    {
        session(['userdata' => ['id' => 7]]);
        $saved = [];

        $this->makeService($this->profileRepo(), ['settingsService' => $this->settingsStore([], $saved)])
            ->saveOwnNotificationPreferences(99, [
                'webhookUrl' => '  https://hooks.example.com/services/abc?token=xyz  ',
                'webhookEnabled' => 'on',
            ]);

        $this->assertSame(
            ['url' => 'https://hooks.example.com/services/abc?token=xyz', 'enabled' => true],
            json_decode($saved['usersettings.7.webhook'], true)
        );
        $this->assertSame('usersettings.7.webhook', array_key_first($saved), 'The webhook is saved before any other preference');
        foreach (array_keys($saved) as $key) {
            $this->assertStringNotContainsString('usersettings.99.', $key, 'A caller-supplied id must never be written to');
        }
    }

    public function test_unchecking_the_webhook_keeps_the_url_but_disables_delivery(): void
    {
        $saved = [];

        $this->makeService($this->profileRepo(), ['settingsService' => $this->settingsStore([], $saved)])
            ->saveOwnNotificationPreferences(1, ['webhookUrl' => 'https://hooks.example.com/abc']);

        $this->assertSame(['url' => 'https://hooks.example.com/abc', 'enabled' => false], json_decode($saved['usersettings.1.webhook'], true));
    }

    public function test_an_empty_webhook_url_disables_delivery_even_when_checked(): void
    {
        $saved = [];

        $this->makeService($this->profileRepo(), ['settingsService' => $this->settingsStore([], $saved)])
            ->saveOwnNotificationPreferences(1, ['webhookUrl' => '', 'webhookEnabled' => '1']);

        $this->assertSame(['url' => '', 'enabled' => false], json_decode($saved['usersettings.1.webhook'], true));
    }

    /**
     * @dataProvider payloadWithoutWebhookUrlProvider
     */
    public function test_omitting_the_webhook_url_leaves_the_stored_webhook_unchanged(array $post): void
    {
        // API clients built before the webhook existed only send the older preferences.
        $storedWebhook = $this->webhookSetting('https://hooks.example.com/abc', true);
        $saved = [];
        $editOwnCalls = 0;
        $settings = $this->settingsStore(['usersettings.1.webhook' => $storedWebhook], $saved);

        $this->makeService($this->profileRepo($editOwnCalls), ['settingsService' => $settings])
            ->saveOwnNotificationPreferences(1, $post);

        $this->assertSame($storedWebhook, $settings->getSetting('usersettings.1.webhook'), 'The stored webhook must be left exactly as it was');
        $this->assertArrayNotHasKey('usersettings.1.webhook', $saved, 'Nothing may be written to the webhook setting');
        $this->assertSame(1, $editOwnCalls, 'The notifications flag is still saved');
        $this->assertSame(60, $saved['usersettings.1.messageFrequency']);
        $this->assertSame(json_encode(['tasks']), $saved['usersettings.1.notificationEventTypes']);
    }

    public static function payloadWithoutWebhookUrlProvider(): array
    {
        $olderPreferences = ['notifications' => '1', 'messagesfrequency' => '60', 'enabledEventTypes' => ['tasks']];

        return [
            'older preferences only' => [$olderPreferences],
            'opt-in without a url' => [$olderPreferences + ['webhookEnabled' => '0']],
        ];
    }

    public function test_a_failed_webhook_disable_is_not_reported_as_saved(): void
    {
        // The user unchecks the box, but the write reports false and the store still holds
        // the enabled webhook — delivery would keep going, so this must not look saved.
        $saved = [];
        $editOwnCalls = 0;
        $logged = [];
        Log::shouldReceive('error')->andReturnUsing(function ($message, $context = []) use (&$logged) {
            $logged[] = $message.' '.json_encode($context);
        });
        $settings = $this->settingsStore(
            ['usersettings.1.webhook' => $this->webhookSetting('https://hooks.example.com/secret-token', true)],
            $saved,
            fn () => false,
        );

        try {
            $this->makeService($this->profileRepo($editOwnCalls), ['settingsService' => $settings])
                ->saveOwnNotificationPreferences(1, ['notifications' => '1', 'webhookUrl' => 'https://hooks.example.com/secret-token']);
            $this->fail('A webhook setting that did not persist must throw');
        } catch (WebhookSettingNotSavedException $e) {
            $this->assertStringNotContainsString('secret-token', $e->getMessage());
        }

        $this->assertSame(['usersettings.1.webhook'], array_keys($saved), 'No other preference may be written after the webhook failed');
        $this->assertSame(0, $editOwnCalls);
        $this->assertCount(1, $logged);
        $this->assertStringNotContainsString('secret-token', $logged[0], 'The webhook URL must never reach the logs');
    }

    public function test_a_webhook_write_that_throws_is_reported_without_the_url(): void
    {
        $saved = [];
        $editOwnCalls = 0;
        $logged = [];
        Log::shouldReceive('error')->andReturnUsing(function ($message, $context = []) use (&$logged) {
            $logged[] = $message.' '.json_encode($context);
        });
        // DB exceptions embed the bound values — here the webhook URL with its secret.
        $settings = $this->settingsStore([], $saved, function ($key, $value) {
            throw new \RuntimeException('SQLSTATE[HY000]: General error (SQL: update zp_settings set value = '.$value.')');
        });

        try {
            $this->makeService($this->profileRepo($editOwnCalls), ['settingsService' => $settings])
                ->saveOwnNotificationPreferences(1, ['webhookUrl' => 'https://hooks.example.com/secret-token', 'webhookEnabled' => '1']);
            $this->fail('A webhook setting that could not be written must throw');
        } catch (WebhookSettingNotSavedException $e) {
            $this->assertNull($e->getPrevious(), 'The DB exception (and the URL in its message) must not be chained');
            $this->assertStringNotContainsString('secret-token', $e->getMessage());
        }

        $this->assertSame(['usersettings.1.webhook'], array_keys($saved));
        $this->assertSame(0, $editOwnCalls);
        $this->assertCount(1, $logged);
        $this->assertStringContainsString('RuntimeException', $logged[0]);
        $this->assertStringNotContainsString('secret-token', $logged[0], 'The webhook URL must never reach the logs');
    }

    public function test_an_unchanged_webhook_counts_as_saved_when_the_write_reports_no_change(): void
    {
        // updateOrInsert returns false when the row already holds the identical value.
        $saved = [];
        $editOwnCalls = 0;
        $settings = $this->settingsStore(
            ['usersettings.1.webhook' => $this->webhookSetting('https://hooks.example.com/abc', true)],
            $saved,
            fn () => false,
        );

        $this->makeService($this->profileRepo($editOwnCalls), ['settingsService' => $settings])
            ->saveOwnNotificationPreferences(1, ['webhookUrl' => 'https://hooks.example.com/abc', 'webhookEnabled' => '1', 'messagesfrequency' => '60']);

        $this->assertSame(1, $editOwnCalls, 'The remaining preferences are still saved');
        $this->assertSame(60, $saved['usersettings.1.messageFrequency']);
    }

    /**
     * @dataProvider invalidWebhookUrlProvider
     */
    public function test_an_invalid_webhook_url_is_rejected_before_anything_is_saved(mixed $webhookUrl): void
    {
        $saved = [];
        $editOwnCalls = 0;
        $service = $this->makeService($this->profileRepo($editOwnCalls), ['settingsService' => $this->settingsStore([], $saved)]);

        try {
            $service->saveOwnNotificationPreferences(1, [
                'notifications' => '1',
                'messagesfrequency' => '60',
                'enabledEventTypes' => ['tasks'],
                'webhookUrl' => $webhookUrl,
                'webhookEnabled' => '1',
            ]);
            $this->fail('An invalid webhook URL must throw');
        } catch (ValidationException $e) {
            $this->assertSame(['webhookUrl' => ['notification.invalid_webhook_url']], $e->getErrorData());
        }

        $this->assertSame([], $saved, 'No preference may be written when the webhook URL is rejected');
        $this->assertSame(0, $editOwnCalls, 'The notifications flag must not be written either');
    }

    public static function invalidWebhookUrlProvider(): array
    {
        return [
            'plain http' => ['http://hooks.example.com/abc'],
            'embedded credentials' => ['https://user:secret@hooks.example.com/abc'],
            'single-label host' => ['https://localhost/abc'],
            'private ip literal' => ['https://192.168.1.10/abc'],
            'not a url' => ['hooks.example.com/abc'],
            'too long' => ['https://hooks.example.com/'.str_repeat('a', 2048)],
            'array instead of string' => [['https://hooks.example.com/abc']],
            'explicit null' => [null],
        ];
    }

    public function test_get_notification_preferences_returns_the_session_users_webhook_only(): void
    {
        session(['userdata' => ['id' => 7]]);
        $settings = $this->settingsStore([
            'usersettings.7.webhook' => $this->webhookSetting('https://hooks.example.com/mine', true),
            'usersettings.99.webhook' => $this->webhookSetting('https://hooks.example.com/someone-else', true),
        ]);
        $projectService = $this->make(ProjectService::class, [
            'getProjectHierarchyAvailableToUser' => fn () => ['allAvailableProjects' => []],
        ]);

        $preferences = $this->makeService($this->profileRepo(), ['settingsService' => $settings, 'projectService' => $projectService])
            ->getNotificationPreferences(99);

        $this->assertSame('https://hooks.example.com/mine', $preferences['webhookUrl']);
        $this->assertTrue($preferences['webhookEnabled']);
    }

    public function test_get_notification_preferences_defaults_the_webhook_to_off(): void
    {
        $projectService = $this->make(ProjectService::class, [
            'getProjectHierarchyAvailableToUser' => fn () => ['allAvailableProjects' => []],
        ]);

        $preferences = $this->makeService($this->profileRepo(), ['settingsService' => $this->settingsStore([]), 'projectService' => $projectService])
            ->getNotificationPreferences(1);

        $this->assertSame('', $preferences['webhookUrl']);
        $this->assertFalse($preferences['webhookEnabled']);
    }

    /**
     * Regression for #3556: getUser is @api and intentionally ungated, so any
     * authenticated client can request an arbitrary id. It must never return
     * credentials — password hash, plaintext 2FA seed, session token, or the
     * password-reset token/metadata — while keeping the safe profile fields
     * the view composers rely on.
     */
    public function test_get_user_strips_sensitive_fields_from_api_response(): void
    {
        $fullRow = [
            'id' => 5,
            'firstname' => 'Ada',
            'lastname' => 'Lovelace',
            'username' => 'ada@example.com',
            'role' => '20',
            'password' => '$2y$10$abcdefghijklmnopqrstuv',
            'twoFASecret' => 'SECRET2FASEED',
            'session' => 'sess-token-xyz',
            'sessiontime' => '1700000000',
            'pwReset' => 'reset-token',
            'pwResetExpiration' => '2026-01-01 00:00:00',
            'pwResetCount' => 2,
        ];

        $repo = $this->make(UserRepository::class, [
            'getUser' => fn () => $fullRow,
        ]);

        $user = $this->makeService($repo)->getUser(5);

        $this->assertIsArray($user);
        // Safe profile fields survive so composers/avatars keep working.
        $this->assertSame('Ada', $user['firstname']);
        $this->assertSame('ada@example.com', $user['username']);

        // Every credential/session/reset field is stripped.
        foreach (['password', 'twoFASecret', 'session', 'sessiontime', 'pwReset', 'pwResetExpiration', 'pwResetCount'] as $secret) {
            $this->assertArrayNotHasKey($secret, $user, "getUser must not leak {$secret} over the API");
        }
    }
}

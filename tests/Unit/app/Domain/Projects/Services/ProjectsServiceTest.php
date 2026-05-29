<?php

namespace Unit\app\Domain\Projects\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
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
}

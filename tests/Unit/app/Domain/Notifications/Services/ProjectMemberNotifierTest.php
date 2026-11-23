<?php

namespace Unit\app\Domain\Notifications\Services;

use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Notifications\Services\Notifications as NotificationService;
use Leantime\Domain\Notifications\Services\ProjectMemberNotifier;
use Leantime\Domain\Notifications\Services\Push;
use Leantime\Domain\Projects\Events\ProjectMemberAdded;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\TestCase;

/**
 * "Added you to a project" goes to the one person added: inbox always, email when they kept
 * email on, push when they kept push on. Adding yourself, no signed-in actor, deactivated or
 * invited people and unreadable projects send nothing; a bulk add pushes at most three times.
 */
class ProjectMemberNotifierTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private const STRINGS = [
        'text.x_added_you_to_project' => "%1\$s added you to the project '%2\$s'",
        'text.click_here' => 'Click here to get to it.',
        'email_notifications.added_to_project_subject' => 'You were added to %s',
        'notification.push_added_to_project_body' => '%s added you to this project',
        'label.someone' => 'Someone',
    ];

    /** @var array<int, array<string, mixed>> */
    private array $users = [];

    private array $pushes = [];

    private array $emails = [];

    private array $inbox = [];

    /** @var array<int, int> users the project repository says aren't on the team */
    private array $notOnTeam = [];

    protected function setUp(): void
    {
        parent::setUp();

        session(['userdata' => ['id' => 1, 'name' => 'Sam']]);
        $this->users = [
            1 => ['id' => 1, 'firstname' => 'Sam', 'status' => 'a', 'notifications' => 1],
            2 => ['id' => 2, 'firstname' => 'Ada', 'status' => 'a', 'notifications' => 1],
            3 => ['id' => 3, 'firstname' => 'Invited', 'status' => 'i', 'notifications' => 1],
            4 => ['id' => 4, 'firstname' => 'Quiet', 'status' => 'A', 'notifications' => 0],
        ];
        $this->pushes = [];
        $this->emails = [];
        $this->inbox = [];
        $this->notOnTeam = [];
        ProjectMemberNotifier::resetPushCount();
    }

    /**
     * @param  array<string, string>  $settings  Stored zp_settings by key.
     */
    private function notifier(array $settings = []): ProjectMemberNotifier
    {
        return new ProjectMemberNotifier(
            $this->make(UserRepository::class, ['getUser' => fn ($id) => $this->users[(int) $id] ?? false]),
            $this->make(ProjectRepository::class, [
                'getProject' => fn ($id) => (int) $id === 5 ? ['id' => 5, 'name' => 'Customer <b>Service</b>'] : false,
                'isUserMemberOfProject' => fn ($userId, $projectId) => ! in_array((int) $userId, $this->notOnTeam, true),
            ]),
            $this->make(NotificationService::class, [
                'addNotifications' => function (array $rows) {
                    array_push($this->inbox, ...$rows);

                    return true;
                },
            ]),
            $this->make(QueueRepository::class, [
                'queueMessageToUsers' => function (array $recipients, string $message, string $subject = '', int $projectId = 0, ?string $module = null, ?int $moduleId = null, ?array $summary = null) {
                    $this->emails[] = compact('recipients', 'message', 'subject', 'projectId', 'module', 'moduleId', 'summary');
                },
            ]),
            $this->make(SettingRepository::class, ['getSetting' => fn ($key, $default = false) => $settings[$key] ?? $default]),
            $this->make(LanguageCore::class, ['__' => fn ($key) => self::STRINGS[$key] ?? $key]),
            $this->make(Push::class, [
                'send' => function (array $userIds, string $title, string $body, array $data = []) {
                    $this->pushes[] = compact('userIds', 'title', 'body', 'data');
                },
            ]),
            $this->make(TrustedAppUrl::class, ['rebase' => fn (string $url) => preg_replace('#^https?://[^/]+#', 'https://app.example.com', $url)]),
        );
    }

    public function test_the_new_member_gets_an_inbox_row_a_digest_email_and_a_push(): void
    {
        $this->notifier()->notify(new ProjectMemberAdded(projectId: 5, userId: 2));

        $this->assertCount(1, $this->inbox);
        $row = $this->inbox[0];
        $this->assertSame([2, 'project_added', 'projects', 5, 1], [$row['userId'], $row['type'], $row['module'], $row['moduleId'], $row['authorId']]);
        $this->assertSame("Sam added you to the project 'Customer Service'", $row['message'], 'Markup in the project name is stripped');
        $this->assertSame('https://app.example.com/projects/changeCurrentProject/5', $row['url']);

        $this->assertCount(1, $this->emails);
        $this->assertSame([2], $this->emails[0]['recipients']);
        $this->assertSame('You were added to Customer Service', $this->emails[0]['subject']);
        $this->assertSame(['projects', 5, 5], [$this->emails[0]['module'], $this->emails[0]['moduleId'], $this->emails[0]['projectId']]);
        $this->assertSame('projects:5', $this->emails[0]['summary']['group']);
        $this->assertSame('Customer Service', $this->emails[0]['summary']['title']);
        $this->assertSame('Sam added you to this project', $this->emails[0]['summary']['line']);

        $this->assertCount(1, $this->pushes);
        $this->assertSame([2], $this->pushes[0]['userIds']);
        $this->assertSame('Customer Service', $this->pushes[0]['title']);
        $this->assertSame('Sam added you to this project', $this->pushes[0]['body']);
        $this->assertSame('projects', $this->pushes[0]['data']['module']);
        $this->assertSame('5', $this->pushes[0]['data']['moduleId']);
        $this->assertSame(Push::TIER_DIRECT, $this->pushes[0]['data']['tier']);
        $this->assertSame(Push::CATEGORY_NONE, $this->pushes[0]['data']['category']);
    }

    public function test_adding_yourself_sends_nothing(): void
    {
        $this->notifier()->notify(new ProjectMemberAdded(projectId: 5, userId: 1));

        $this->assertSame([[], [], []], [$this->inbox, $this->emails, $this->pushes]);
    }

    public function test_nothing_is_sent_without_a_signed_in_actor(): void
    {
        session()->forget('userdata');

        $this->notifier()->notify(new ProjectMemberAdded(projectId: 5, userId: 2));

        $this->assertSame([[], [], []], [$this->inbox, $this->emails, $this->pushes]);
    }

    public function test_invited_or_unknown_people_and_unreadable_projects_get_nothing(): void
    {
        $this->notifier()->notify(new ProjectMemberAdded(projectId: 5, userId: 3));
        $this->notifier()->notify(new ProjectMemberAdded(projectId: 5, userId: 99));
        $this->notifier()->notify(new ProjectMemberAdded(projectId: 6, userId: 2));

        $this->assertSame([[], [], []], [$this->inbox, $this->emails, $this->pushes]);
    }

    public function test_someone_not_actually_on_the_team_gets_nothing(): void
    {
        // An event fired for a membership the repository didn't save (a new project's team entry
        // without a projectRole): not even the project's name goes out.
        $this->notOnTeam = [2];

        $this->notifier()->notify(new ProjectMemberAdded(projectId: 5, userId: 2));

        $this->assertSame([[], [], []], [$this->inbox, $this->emails, $this->pushes]);
    }

    public function test_email_off_still_gets_the_inbox_and_push(): void
    {
        $this->notifier()->notify(new ProjectMemberAdded(projectId: 5, userId: 4));

        $this->assertCount(1, $this->inbox);
        $this->assertSame([], $this->emails);
        $this->assertCount(1, $this->pushes, 'status is compared case-insensitively');
    }

    public function test_push_off_still_gets_the_inbox_and_email(): void
    {
        $this->notifier(['usersettings.2.pushNotifications' => '0'])->notify(new ProjectMemberAdded(projectId: 5, userId: 2));

        $this->assertCount(1, $this->inbox);
        $this->assertCount(1, $this->emails);
        $this->assertSame([], $this->pushes);
    }

    public function test_a_bulk_add_pushes_at_most_three_times_but_lists_every_project(): void
    {
        $notifier = $this->notifier();
        for ($i = 0; $i < 5; $i++) {
            $notifier->notify(new ProjectMemberAdded(projectId: 5, userId: 2));
        }

        $this->assertCount(5, $this->inbox);
        $this->assertCount(5, $this->emails);
        $this->assertCount(3, $this->pushes);
    }

    public function test_a_failing_push_does_not_break_the_inbox_or_email(): void
    {
        $notifier = new ProjectMemberNotifier(
            $this->make(UserRepository::class, ['getUser' => fn ($id) => $this->users[(int) $id] ?? false]),
            $this->make(ProjectRepository::class, ['getProject' => fn () => ['id' => 5, 'name' => 'P'], 'isUserMemberOfProject' => true]),
            $this->make(NotificationService::class, ['addNotifications' => function (array $rows) {
                array_push($this->inbox, ...$rows);

                return true;
            }]),
            $this->make(QueueRepository::class, ['queueMessageToUsers' => function (...$args) {
                $this->emails[] = $args;
            }]),
            $this->make(SettingRepository::class, ['getSetting' => fn ($key, $default = false) => $default]),
            $this->make(LanguageCore::class, ['__' => fn ($key) => self::STRINGS[$key] ?? $key]),
            $this->make(Push::class, ['send' => function () {
                throw new \RuntimeException('FCM down');
            }]),
            $this->make(TrustedAppUrl::class, ['rebase' => fn (string $url) => $url]),
        );

        $notifier->notify(new ProjectMemberAdded(projectId: 5, userId: 2));

        $this->assertCount(1, $this->inbox);
        $this->assertCount(1, $this->emails);
    }
}

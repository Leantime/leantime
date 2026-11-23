<?php

namespace Unit\app\Domain\Queue\Workers;

use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Mailer;
use Leantime\Domain\Notifications\Repositories\Notifications as NotificationsRepository;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Leantime\Domain\Queue\Workers\EmailWorker;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\TestCase;

/**
 * The email digest splits a person's queued rows into "New" and "Already seen" against the
 * inbox's read state, sends nothing when nothing is new, and waits at least as long between two
 * digests as the person chose, with the company value as the floor rather than an override.
 */
class EmailWorkerTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private const STRINGS = [
        'email_notifications.latest_updates_subject' => 'Leantime - Latest updates',
        'text.here_are_news' => 'News from your Leantime projects',
        'email_notifications.digest_new_heading' => 'New',
        'email_notifications.digest_seen_heading' => 'Already seen',
        'email_notifications.digest_seen_hint' => 'You already read these in Leantime.',
        'email_notifications.digest_title' => 'Updates on your work',
        'email_notifications.digest_intro' => "Here's what changed since your last email.",
        'email_notifications.digest_open_task' => 'Open task',
        'email_notifications.digest_open' => 'Open in Leantime',
        'email_notifications.hi' => 'Hi, ',
        'email_notifications.digest_greeting' => 'Hi %s,',
        'email_notifications.digest_since' => 'What changed since your last email (%s).',
        'email_notifications.digest_yesterday' => 'Yesterday %s',
        'email_notifications.digest_and' => '%1$s and %2$s',
        'email_notifications.digest_open_leantime' => 'Open Leantime',
        'email_notifications.digest_footer' => "You get this email when your work changes. <a href='%s'>Change how often</a> in your notification settings.",
        'email_notifications.digest_footer_text' => 'You get this email when your work changes. Change how often in your notification settings: %s',
    ];

    /** @var array<string, string> zp_settings by key */
    private array $settings = [];

    /** @var array<int, array{to: array, subject: string, html: string, headline: ?string, footer: ?string, text: string}> */
    private array $sent = [];

    /** @var array<int, array<int, string>> Each deleteMessageInQueue() call's hashes. */
    private array $deleted = [];

    /** @var array<string, array{read: string|null, unread: string|null}> "module:moduleId" => newest read / unread datetimes, as the repository returns them. */
    private array $latest = [];

    /** @var array<int, array{userId: int, entities: array}> */
    private array $lookups = [];

    private int $projectLookups = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->settings = [];
        $this->sent = [];
        $this->deleted = [];
        $this->latest = [];
        $this->lookups = [];
        $this->projectLookups = 0;
        $this->firstNames = [];
    }

    /** First names by user id, as getUser() returns them. */
    private array $firstNames = [];

    private function worker(): EmailWorker
    {
        $subject = '';
        $html = '';
        $headline = null;
        $footer = null;
        $text = '';

        return new EmailWorker(
            $this->make(UserRepository::class, [
                'getUser' => fn ($id) => (int) $id === 404 ? false : ['id' => (int) $id, 'username' => 'user'.$id.'@example.com', 'firstname' => $this->firstNames[(int) $id] ?? ''],
            ]),
            $this->make(SettingRepository::class, [
                'getSetting' => fn ($key, $default = false) => $this->settings[$key] ?? $default,
                'saveSetting' => function ($key, $value) {
                    $this->settings[$key] = (string) $value;

                    return true;
                },
            ]),
            $this->make(Mailer::class, [
                'setContext' => fn () => null,
                'setSubject' => function ($value) use (&$subject) {
                    $subject = (string) $value;
                },
                'setHtml' => function ($value) use (&$html) {
                    $html = (string) $value;
                },
                'setHeadline' => function ($value) use (&$headline) {
                    $headline = $value;
                },
                'setFooter' => function ($value) use (&$footer) {
                    $footer = $value;
                },
                'setText' => function ($value) use (&$text) {
                    $text = (string) $value;
                },
                'sendMail' => function (array $to) use (&$subject, &$html, &$headline, &$footer, &$text) {
                    $this->sent[] = ['to' => $to, 'subject' => $subject, 'html' => $html, 'headline' => $headline, 'footer' => $footer, 'text' => $text];

                    return true;
                },
            ]),
            $this->make(QueueRepository::class, [
                'deleteMessageInQueue' => function ($hashes) {
                    $this->deleted[] = is_string($hashes) ? [$hashes] : array_values($hashes);

                    return true;
                },
            ]),
            $this->make(LanguageCore::class, ['__' => fn ($key) => self::STRINGS[$key] ?? $key]),
            $this->make(NotificationsRepository::class, [
                'latestByEntity' => function (int $userId, array $entities) {
                    $this->lookups[] = compact('userId', 'entities');

                    return $this->latest;
                },
            ]),
            $this->make(ProjectRepository::class, [
                'getProject' => function ($id) {
                    $this->projectLookups++;

                    return (int) $id === 5 ? ['id' => 5, 'name' => 'Sales'] : false;
                },
            ]),
            $this->make(TrustedAppUrl::class, ['forLinks' => fn () => 'https://app.example.com']),
        );
    }

    /**
     * A zp_queue row as listMessageInQueue() returns it. thedate defaults to two minutes ago.
     */
    private function row(string $hash, int $userId, string $subject, ?string $module = 'tickets', ?int $moduleId = 12, ?string $thedate = null): array
    {
        return [
            'msghash' => $hash,
            'channel' => 'email',
            'userId' => $userId,
            'subject' => $subject,
            'message' => $subject.' <a href="https://app.example.com/tickets/'.$moduleId.'">Click here</a>',
            'thedate' => $thedate ?? gmdate('Y-m-d H:i:s', time() - 120),
            'projectId' => 5,
            'module' => $module,
            'moduleId' => $moduleId,
        ];
    }

    /** The inbox's state for one entity: newest read row, and newest unread row if any. */
    private function inbox(string $key, ?string $read, ?string $unread = null): void
    {
        $this->latest[$key] = ['read' => $read, 'unread' => $unread];
    }

    private function deletedHashes(): array
    {
        return array_merge(...($this->deleted ?: [[]]));
    }

    // ---------------------------------------------------------------------
    // New vs. already seen
    // ---------------------------------------------------------------------

    public function test_unread_rows_are_emailed_in_full_without_section_headings(): void
    {
        $this->worker()->handleQueue([
            $this->row('h1', 2, 'Ticket updated', 'tickets', 12),
            $this->row('h2', 2, 'New comment', 'comments', 77),
        ]);

        $this->assertCount(1, $this->sent);
        $this->assertSame(['user2@example.com'], $this->sent[0]['to']);
        $this->assertSame('Leantime - Latest updates', $this->sent[0]['subject'], 'more than one new item: the generic subject');
        $this->assertStringContainsString('<a href="https://app.example.com/tickets/12">', $this->sent[0]['html']);
        $this->assertStringContainsString('<a href="https://app.example.com/tickets/77">', $this->sent[0]['html']);
        $this->assertStringNotContainsString('Already seen', $this->sent[0]['html']);
        $this->assertStringNotContainsString('>New<', $this->sent[0]['html'], 'no headings when everything is new: the digest reads as it always did');
        $this->assertSame(['h1', 'h2'], $this->deletedHashes());
        $this->assertArrayHasKey('usersettings.2.lastMessageDate', $this->settings);
    }

    public function test_a_single_new_row_uses_its_own_subject(): void
    {
        $this->worker()->handleQueue([$this->row('h1', 2, 'Assigned to you: Fix login')]);

        $this->assertSame('Assigned to you: Fix login', $this->sent[0]['subject']);
    }

    public function test_a_row_read_in_the_inbox_after_it_was_queued_moves_to_already_seen_without_its_link(): void
    {
        $this->inbox('tickets:12', gmdate('Y-m-d H:i:s', time() - 60));

        $this->worker()->handleQueue([
            $this->row('seen', 2, 'Ticket updated', 'tickets', 12),
            $this->row('fresh', 2, 'New comment', 'comments', 77),
        ]);

        $this->assertCount(1, $this->sent);
        $this->assertSame('New comment', $this->sent[0]['subject'], 'one new item: its subject, the seen one does not count');
        $html = $this->sent[0]['html'];
        $this->assertStringContainsString('<a href="https://app.example.com/tickets/77">', $html, 'the new item keeps its link');
        $this->assertStringNotContainsString('tickets/12', $html, 'the seen item is one line, no link');
        $this->assertStringContainsString('Ticket updated', $html, 'but it is still listed');
        $this->assertLessThan(strpos($html, 'Already seen'), strpos($html, '>New<'), 'New comes first');
        $this->assertLessThan(strpos($html, 'Ticket updated'), strpos($html, 'Already seen'), 'the seen item sits under its heading');
        $this->assertStringContainsString('You already read these in Leantime.', $html);
        $this->assertSame(['seen', 'fresh'], $this->deletedHashes(), 'both rows leave the queue with the digest');
        $this->assertSame([['userId' => 2, 'entities' => [['module' => 'tickets', 'moduleId' => 12], ['module' => 'comments', 'moduleId' => 77]]]], $this->lookups);
    }

    public function test_nothing_new_means_no_email_and_the_rows_wait(): void
    {
        $this->inbox('tickets:12', gmdate('Y-m-d H:i:s'));

        $this->worker()->handleQueue([$this->row('seen', 2, 'Ticket updated', 'tickets', 12)]);

        $this->assertSame([], $this->sent);
        $this->assertSame([], $this->deleted, 'the seen row stays for the next digest that has news');
        $this->assertArrayNotHasKey('usersettings.2.lastMessageDate', $this->settings, 'nothing was sent, so the clock does not restart');
    }

    public function test_a_read_older_than_the_queued_row_does_not_count(): void
    {
        // Read five minutes ago; the row was queued two minutes ago (grace is two minutes).
        $this->inbox('tickets:12', gmdate('Y-m-d H:i:s', time() - 300));

        $this->worker()->handleQueue([$this->row('h1', 2, 'Ticket updated', 'tickets', 12)]);

        $this->assertCount(1, $this->sent);
        $this->assertStringNotContainsString('Already seen', $this->sent[0]['html']);
    }

    public function test_a_read_just_before_the_row_was_queued_counts_within_the_grace(): void
    {
        $queuedAt = time() - 120;
        $this->inbox('tickets:12', gmdate('Y-m-d H:i:s', $queuedAt - EmailWorker::SEEN_GRACE_SECONDS + 1));

        $this->worker()->handleQueue([
            $this->row('seen', 2, 'Ticket updated', 'tickets', 12, gmdate('Y-m-d H:i:s', $queuedAt)),
            $this->row('fresh', 2, 'New comment', 'comments', 77),
        ]);

        $this->assertStringContainsString('Already seen', $this->sent[0]['html']);
    }

    /**
     * The person read an earlier notification about the task; then a new update was queued, and
     * its own inbox row is still unread. The earlier read must not hide the new update.
     */
    public function test_an_earlier_read_does_not_hide_a_newer_unread_update_on_the_same_entity(): void
    {
        $queuedAt = time() - 120;
        // The read row sits INSIDE the grace before the queue time, so the read check alone would
        // pass: only the unread guard keeps this update new.
        $this->inbox('tickets:12', read: gmdate('Y-m-d H:i:s', $queuedAt - EmailWorker::SEEN_GRACE_SECONDS + 1), unread: gmdate('Y-m-d H:i:s', $queuedAt));

        $this->worker()->handleQueue([$this->row('h1', 2, 'Ticket updated', 'tickets', 12, gmdate('Y-m-d H:i:s', $queuedAt))]);

        $this->assertCount(1, $this->sent, 'the update is new and the digest goes out');
        $this->assertStringNotContainsString('Already seen', $this->sent[0]['html']);
    }

    public function test_an_unread_row_older_than_the_queued_email_does_not_block_seen(): void
    {
        $queuedAt = time() - 120;
        // An old unread row about the task from yesterday; today's update was read.
        $this->inbox('tickets:12', read: gmdate('Y-m-d H:i:s', $queuedAt + 30), unread: gmdate('Y-m-d H:i:s', $queuedAt - 86400));

        $this->worker()->handleQueue([
            $this->row('seen', 2, 'Ticket updated', 'tickets', 12, gmdate('Y-m-d H:i:s', $queuedAt)),
            $this->row('fresh', 2, 'New comment', 'comments', 77),
        ]);

        $this->assertStringContainsString('Already seen', $this->sent[0]['html']);
    }

    public function test_an_entity_with_only_unread_rows_is_new(): void
    {
        $this->inbox('tickets:12', read: null, unread: gmdate('Y-m-d H:i:s'));

        $this->worker()->handleQueue([$this->row('h1', 2, 'Ticket updated', 'tickets', 12)]);

        $this->assertCount(1, $this->sent);
        $this->assertStringNotContainsString('Already seen', $this->sent[0]['html']);
    }

    public function test_rows_without_an_entity_are_always_new_and_never_looked_up(): void
    {
        $this->inbox('tickets:12', gmdate('Y-m-d H:i:s'));

        $this->worker()->handleQueue([$this->row('legacy', 2, 'Project created', null, null)]);

        $this->assertCount(1, $this->sent);
        $this->assertSame([], $this->lookups, 'no entity, nothing to ask the inbox');
    }

    public function test_the_seen_subject_is_escaped(): void
    {
        $this->inbox('tickets:12', gmdate('Y-m-d H:i:s'));

        $this->worker()->handleQueue([
            $this->row('seen', 2, 'Tom & Jerry <b>', 'tickets', 12),
            $this->row('fresh', 2, 'New comment', 'comments', 77),
        ]);

        $this->assertStringContainsString('Tom &amp; Jerry', $this->sent[0]['html']);
        $this->assertStringNotContainsString('<b>', $this->sent[0]['html']);
    }

    // ---------------------------------------------------------------------
    // Frequency: the person's choice, the company value as the floor
    // ---------------------------------------------------------------------

    /** @return array<string, array{own: string|null, company: string|null, expected: int}> */
    public static function frequencies(): array
    {
        return [
            'neither set: the default' => ['own' => null, 'company' => null, 'expected' => 900],
            'only the person: theirs' => ['own' => '86400', 'company' => null, 'expected' => 86400],
            'only the company: stands in' => ['own' => null, 'company' => '3600', 'expected' => 3600],
            'person longer than company: theirs (quieter)' => ['own' => '86400', 'company' => '3600', 'expected' => 86400],
            'person shorter than company: the company floor' => ['own' => '300', 'company' => '3600', 'expected' => 3600],
            'junk values read as unset' => ['own' => 'soon', 'company' => '', 'expected' => 900],
        ];
    }

    /** @dataProvider frequencies */
    public function test_frequency_is_the_persons_choice_with_the_company_value_as_the_floor(?string $own, ?string $company, int $expected): void
    {
        if ($own !== null) {
            $this->settings['usersettings.2.messageFrequency'] = $own;
        }
        if ($company !== null) {
            $this->settings['companysettings.messageFrequency'] = $company;
        }

        $this->assertSame($expected, $this->worker()->frequencyFor(2));
    }

    public function test_a_digest_inside_the_wait_is_skipped_and_the_rows_stay(): void
    {
        $this->settings['usersettings.2.messageFrequency'] = '3600';
        $this->settings['usersettings.2.lastMessageDate'] = date('Y-m-d H:i:s', time() - 600);

        $this->worker()->handleQueue([$this->row('h1', 2, 'Ticket updated')]);

        $this->assertSame([], $this->sent);
        $this->assertSame([], $this->deleted);
    }

    public function test_a_person_who_chose_a_longer_wait_than_the_company_is_not_emailed_sooner(): void
    {
        $this->settings['companysettings.messageFrequency'] = '300';
        $this->settings['usersettings.2.messageFrequency'] = '86400';
        $this->settings['usersettings.2.lastMessageDate'] = date('Y-m-d H:i:s', time() - 3600);

        $this->worker()->handleQueue([$this->row('h1', 2, 'Ticket updated')]);

        $this->assertSame([], $this->sent, 'an hour is past the company value but not the person\'s own day');
    }

    // ---------------------------------------------------------------------
    // Housekeeping
    // ---------------------------------------------------------------------

    public function test_expired_rows_are_dropped_even_when_nothing_is_sent(): void
    {
        $this->worker()->handleQueue([
            $this->row('old', 2, 'Ancient news', thedate: gmdate('Y-m-d H:i:s', time() - 20 * 86400)),
        ]);

        $this->assertSame([], $this->sent);
        $this->assertSame(['old'], $this->deletedHashes());
    }

    public function test_rows_for_an_unknown_user_are_left_alone(): void
    {
        $this->worker()->handleQueue([$this->row('h1', 404, 'Ticket updated')]);

        $this->assertSame([], $this->sent);
        $this->assertSame([], $this->deleted);
    }

    public function test_each_person_gets_their_own_digest(): void
    {
        $this->inbox('tickets:12', gmdate('Y-m-d H:i:s'));

        $this->worker()->handleQueue([
            $this->row('a', 2, 'Ticket updated', 'tickets', 12),
            $this->row('b', 3, 'Ticket updated', 'tickets', 12),
            $this->row('c', 3, 'New comment', 'comments', 77),
        ]);

        // User 2 saw their only item: no email. User 3 saw one of two: a digest with both sections.
        $this->assertCount(1, $this->sent);
        $this->assertSame(['user3@example.com'], $this->sent[0]['to']);
        $this->assertStringContainsString('Already seen', $this->sent[0]['html']);
        $this->assertSame(['b', 'c'], $this->deletedHashes());
    }

    // ---------------------------------------------------------------------
    // Grouped by item
    // ---------------------------------------------------------------------

    /** A row with the digest's summary, as Queue::queueMessageToUsers() stores it. */
    private function summaryRow(string $hash, string $line, string $thedate, array $extra = [], string $group = 'tickets:12', string $title = 'Pricing page'): array
    {
        $row = $this->row($hash, 2, 'One of your To-Dos was updated [12] - '.$title, 'tickets', 12, $thedate);
        $row['summary'] = json_encode(['group' => $group, 'title' => $title, 'line' => $line, 'url' => 'https://app.example.com/t/'.$group] + $extra);

        return $row;
    }

    public function test_changes_to_one_task_sit_together_with_one_button_and_no_email_wording(): void
    {
        $this->worker()->handleQueue([
            $this->summaryRow('h1', 'Marcel assigned this to you', gmdate('Y-m-d H:i:s', time() - 300), ['detail' => 'Due Nov 20']),
            $this->summaryRow('h2', 'Marcel moved this to Done', gmdate('Y-m-d H:i:s', time() - 120)),
        ]);

        $html = $this->sent[0]['html'];
        $this->assertSame(1, substr_count($html, 'Pricing page'), 'the task name once');
        $this->assertStringContainsString('Sales', $html, 'the project above it');
        $this->assertLessThan(strpos($html, 'Marcel moved this to Done'), strpos($html, 'Marcel assigned this to you'), 'oldest change first');
        $this->assertStringContainsString('Due Nov 20', $html);
        $this->assertLessThan(strpos($html, 'Marcel moved this to Done'), strpos($html, 'Due Nov 20'), 'the due date sits under its own event');
        $this->assertSame(1, substr_count($html, 'href="https://app.example.com/t/tickets:12"'), 'the task name is the one link to it');
        $this->assertStringNotContainsString('Open task', $html, 'no button per task');
        $this->assertSame(1, substr_count($html, '>Open Leantime<'), 'one button for the whole email');
        $this->assertStringNotContainsString('One of your To-Dos', $html, 'no email subject');
        $this->assertStringNotContainsString('Click here', $html, 'no "click here" links');
        $this->assertSame(['h1', 'h2'], $this->deletedHashes());
    }

    public function test_the_same_line_from_one_save_shows_once(): void
    {
        $at = gmdate('Y-m-d H:i:s', time() - 120);
        $this->worker()->handleQueue([
            $this->summaryRow('h1', 'Marcel updated this task', $at),
            $this->summaryRow('h2', 'Marcel updated this task', $at),
        ]);

        $this->assertSame(1, substr_count($this->sent[0]['html'], 'Marcel updated this task'));
    }

    public function test_the_same_line_later_is_a_new_change_and_stays(): void
    {
        // Done at 09:00, In Progress at 10:00, Done again at 11:00: the digest ends at Done.
        $this->worker()->handleQueue([
            $this->summaryRow('h1', 'Marcel moved this to Done', gmdate('Y-m-d H:i:s', time() - 7200)),
            $this->summaryRow('h2', 'Marcel moved this to In Progress', gmdate('Y-m-d H:i:s', time() - 3600)),
            $this->summaryRow('h3', 'Marcel moved this to Done', gmdate('Y-m-d H:i:s', time() - 60)),
        ]);

        $html = $this->sent[0]['html'];
        $this->assertSame(2, substr_count($html, 'Marcel moved this to Done'));
        $this->assertGreaterThan(strpos($html, 'In Progress'), strrpos($html, 'Marcel moved this to Done'), 'ends at Done');
    }

    public function test_a_renamed_task_is_headed_by_its_newest_name(): void
    {
        $this->worker()->handleQueue([
            $this->summaryRow('h1', 'Marcel assigned this to you', gmdate('Y-m-d H:i:s', time() - 600), [], 'tickets:12', 'Draft pricing'),
            $this->summaryRow('h2', 'Marcel renamed this task', gmdate('Y-m-d H:i:s', time() - 60), [], 'tickets:12', 'Pricing page'),
        ]);

        $html = $this->sent[0]['html'];
        $this->assertStringContainsString('Pricing page', $html);
        $this->assertStringNotContainsString('Draft pricing', $html);
        $this->assertLessThan(strpos($html, 'Marcel renamed this task'), strpos($html, 'Marcel assigned this to you'), 'lines stay oldest first');
    }

    public function test_an_update_then_a_rename_within_one_save_window_keeps_the_new_name(): void
    {
        // Same line twice inside two minutes, but the second carries the new name: it is a
        // change of its own, and the newest name heads the item.
        $this->worker()->handleQueue([
            $this->summaryRow('h1', 'Marcel updated this task', gmdate('Y-m-d H:i:s', time() - 90), [], 'tickets:12', 'Draft pricing'),
            $this->summaryRow('h2', 'Marcel updated this task', gmdate('Y-m-d H:i:s', time() - 30), [], 'tickets:12', 'Pricing page'),
        ]);

        $html = $this->sent[0]['html'];
        $this->assertStringContainsString('Pricing page', $html);
        $this->assertStringNotContainsString('Draft pricing', $html);
    }

    public function test_each_project_is_looked_up_once_per_run(): void
    {
        $this->worker()->handleQueue([
            $this->summaryRow('h1', 'Marcel updated this task', gmdate('Y-m-d H:i:s', time() - 60), [], 'tickets:12', 'Pricing page'),
            $this->summaryRow('h2', 'Marcel updated this task', gmdate('Y-m-d H:i:s', time() - 600), [], 'tickets:13', 'Landing copy'),
            $this->summaryRow('h3', 'Marcel updated this task', gmdate('Y-m-d H:i:s', time() - 900), [], 'tickets:14', 'Signup form'),
        ]);

        $this->assertSame(3, substr_count($this->sent[0]['html'], 'Sales'));
        $this->assertSame(1, $this->projectLookups);
    }

    public function test_the_task_changed_last_comes_first(): void
    {
        $this->worker()->handleQueue([
            $this->summaryRow('h1', 'Marcel updated this task', gmdate('Y-m-d H:i:s', time() - 60), [], 'tickets:12', 'Pricing page'),
            $this->summaryRow('h2', 'Marcel updated this task', gmdate('Y-m-d H:i:s', time() - 600), [], 'tickets:13', 'Landing copy'),
        ]);

        $html = $this->sent[0]['html'];
        $this->assertLessThan(strpos($html, 'Landing copy'), strpos($html, 'Pricing page'));
    }

    public function test_times_are_in_the_persons_time_zone(): void
    {
        $this->settings['usersettings.2.timezone'] = 'America/Los_Angeles';
        $at = time() - 120;
        $this->worker()->handleQueue([$this->summaryRow('h1', 'Marcel updated this task', gmdate('Y-m-d H:i:s', $at))]);

        $expected = (new \DateTimeImmutable('@'.$at))->setTimezone(new \DateTimeZone('America/Los_Angeles'))->format('g:i A');
        $this->assertStringContainsString($expected, $this->sent[0]['html']);
        $this->assertStringNotContainsString(gmdate('Y-m-d H:i:s', $at), $this->sent[0]['html'], 'no raw database timestamp');
    }

    public function test_a_comment_is_quoted_under_its_line_and_escaped(): void
    {
        $this->worker()->handleQueue([
            $this->summaryRow('h1', 'Marcel commented', gmdate('Y-m-d H:i:s', time() - 120), ['quote' => 'Ship <b>Friday</b>?']),
        ]);

        $html = $this->sent[0]['html'];
        $this->assertStringContainsString('Ship &lt;b&gt;Friday&lt;/b&gt;?', $html);
        $this->assertStringNotContainsString('<b>Friday</b>', $html);
    }

    public function test_the_button_uses_the_company_colour(): void
    {
        $this->settings['companysettings.primarycolor'] = '#7a1fa2';
        $this->worker()->handleQueue([$this->summaryRow('h1', 'Marcel updated this task', gmdate('Y-m-d H:i:s', time() - 120))]);

        $this->assertStringContainsString('background: #7a1fa2', $this->sent[0]['html']);
    }

    public function test_rows_without_a_summary_still_show_their_own_subject_and_link(): void
    {
        $this->worker()->handleQueue([
            $this->summaryRow('h1', 'Marcel updated this task', gmdate('Y-m-d H:i:s', time() - 120)),
            $this->row('h2', 2, 'Project created', 'projects', 5),
        ]);

        $html = $this->sent[0]['html'];
        $this->assertStringContainsString('Project created', $html);
        $this->assertStringContainsString('<a href="https://app.example.com/tickets/5">', $html);
        $this->assertStringContainsString('Marcel updated this task', $html);
    }

    public function test_the_greeting_uses_the_first_name_and_escapes_it(): void
    {
        $this->firstNames[2] = 'Ana & "Bo"';
        $this->worker()->handleQueue([$this->summaryRow('h1', 'Marcel updated this task', gmdate('Y-m-d H:i:s', time() - 120))]);

        $this->assertSame('Hi Ana &amp; &quot;Bo&quot;,', $this->sent[0]['headline']);
        $this->assertStringStartsWith('Hi Ana & "Bo",', $this->sent[0]['text'], 'plain text is not HTML-escaped');
    }

    public function test_without_a_first_name_the_greeting_is_the_standard_one(): void
    {
        $this->worker()->handleQueue([$this->summaryRow('h1', 'Marcel updated this task', gmdate('Y-m-d H:i:s', time() - 120))]);

        $this->assertSame('Hi,', $this->sent[0]['headline']);
    }

    public function test_the_subtitle_names_when_the_last_email_went_out(): void
    {
        $this->settings['usersettings.2.timezone'] = 'UTC';
        $this->settings['usersettings.2.lastMessageDate'] = gmdate('Y-m-d H:i:s', strtotime('-1 day', strtotime(gmdate('Y-m-d 12:00:00'))));
        $this->worker()->handleQueue([$this->summaryRow('h1', 'Marcel updated this task', gmdate('Y-m-d H:i:s', time() - 120))]);

        $this->assertStringContainsString('What changed since your last email (Yesterday 12:00 PM).', $this->sent[0]['html']);
    }

    public function test_without_a_last_email_the_subtitle_is_general(): void
    {
        $this->worker()->handleQueue([$this->summaryRow('h1', 'Marcel updated this task', gmdate('Y-m-d H:i:s', time() - 120))]);

        $this->assertStringContainsString('Here&#039;s what changed since your last email.', $this->sent[0]['html']);
    }

    public function test_a_comment_and_a_change_by_the_same_person_moments_apart_are_one_line(): void
    {
        $this->worker()->handleQueue([
            $this->summaryRow('h1', 'Marcel commented', gmdate('Y-m-d H:i:s', time() - 180), ['quote' => 'Booked for Tuesday.', 'actor' => 'Marcel']),
            $this->summaryRow('h2', 'Marcel moved this to Done', gmdate('Y-m-d H:i:s', time() - 120), ['actor' => 'Marcel']),
        ]);

        $html = $this->sent[0]['html'];
        $this->assertStringContainsString('<strong style="font-weight: 600;">Marcel</strong> commented and moved this to Done', $html);
        $this->assertSame(1, substr_count($html, 'Booked for Tuesday.'), 'the comment is quoted once');
        $this->assertStringNotContainsString('>Marcel commented<', $html);
        $this->assertStringContainsString('- Marcel commented and moved this to Done', $this->sent[0]['text']);
        $this->assertStringContainsString('"Booked for Tuesday."', $this->sent[0]['text']);
    }

    public function test_a_change_first_then_a_comment_joins_in_that_order(): void
    {
        $this->worker()->handleQueue([
            $this->summaryRow('h1', 'Marcel moved this to Done', gmdate('Y-m-d H:i:s', time() - 180), ['actor' => 'Marcel']),
            $this->summaryRow('h2', 'Marcel commented', gmdate('Y-m-d H:i:s', time() - 120), ['quote' => 'Shipped.', 'actor' => 'Marcel']),
        ]);

        $this->assertStringContainsString('moved this to Done and commented', $this->sent[0]['html']);
    }

    public function test_comments_and_changes_stay_apart_when_far_apart_by_different_people_or_both_comments(): void
    {
        $this->worker()->handleQueue([
            // Ten minutes apart.
            $this->summaryRow('h1', 'Marcel commented', gmdate('Y-m-d H:i:s', time() - 900), ['quote' => 'First', 'actor' => 'Marcel']),
            $this->summaryRow('h2', 'Marcel moved this to Done', gmdate('Y-m-d H:i:s', time() - 300), ['actor' => 'Marcel']),
            // Another person, a minute later.
            $this->summaryRow('h3', 'Chloe commented', gmdate('Y-m-d H:i:s', time() - 240), ['quote' => 'Second', 'actor' => 'Chloe']),
            // Two comments by the same person.
            $this->summaryRow('h4', 'Chloe commented', gmdate('Y-m-d H:i:s', time() - 200), ['quote' => 'Third', 'actor' => 'Chloe']),
        ]);

        $html = $this->sent[0]['html'];
        $this->assertStringNotContainsString(' and ', strip_tags($html), 'nothing joined');
        $this->assertSame(3, substr_count($html, '</strong> commented'));
    }

    public function test_rows_queued_without_a_name_are_shown_as_their_line(): void
    {
        $this->worker()->handleQueue([
            $this->summaryRow('h1', 'Marcel commented', gmdate('Y-m-d H:i:s', time() - 180), ['quote' => 'Old row']),
            $this->summaryRow('h2', 'Marcel moved this to Done', gmdate('Y-m-d H:i:s', time() - 120)),
        ]);

        $html = $this->sent[0]['html'];
        $this->assertStringContainsString('Marcel commented', $html);
        $this->assertStringContainsString('Marcel moved this to Done', $html);
        $this->assertStringNotContainsString('<strong', $html, 'no name to set apart');
    }

    public function test_older_changes_say_the_day(): void
    {
        $this->settings['usersettings.2.timezone'] = 'UTC';
        $threeDaysAgo = strtotime(gmdate('Y-m-d 10:15:00')) - 3 * 86400;
        $tenDaysAgo = strtotime(gmdate('Y-m-d 09:05:00')) - 10 * 86400;
        $this->worker()->handleQueue([
            $this->summaryRow('h1', 'Marcel updated this task', gmdate('Y-m-d H:i:s', $threeDaysAgo), ['actor' => 'Marcel'], 'tickets:12', 'Pricing page'),
            $this->summaryRow('h2', 'Chloe updated this task', gmdate('Y-m-d H:i:s', $tenDaysAgo), ['actor' => 'Chloe'], 'tickets:13', 'Landing copy'),
        ]);

        $html = $this->sent[0]['html'];
        $this->assertStringContainsString(gmdate('l', $threeDaysAgo).' 10:15 AM', $html, 'a weekday within the week');
        $this->assertStringContainsString(gmdate('D, M j', $tenDaysAgo), $html, 'a date beyond it');
    }

    public function test_the_plain_text_part_has_the_same_content_without_html(): void
    {
        $this->worker()->handleQueue([
            $this->summaryRow('h1', 'Marcel commented', gmdate('Y-m-d H:i:s', time() - 120), ['quote' => 'Ship <b>Friday</b>?', 'actor' => 'Marcel']),
        ]);

        $text = $this->sent[0]['text'];
        $this->assertStringContainsString('Updates on your work', $text);
        $this->assertStringContainsString("SALES\nPricing page\nhttps://app.example.com/t/tickets:12\n- Marcel commented · ", $text);
        $this->assertStringContainsString('"Ship <b>Friday</b>?"', $text, 'the comment as written, not escaped');
        $this->assertStringContainsString('Open Leantime: https://app.example.com/dashboard/home', $text);
        $this->assertStringContainsString('notification settings: https://app.example.com/users/editOwn#notifications', $text);
        $this->assertStringNotContainsString('<td', $text);
    }

    public function test_the_footer_links_to_the_persons_notification_settings(): void
    {
        $this->worker()->handleQueue([$this->summaryRow('h1', 'Marcel updated this task', gmdate('Y-m-d H:i:s', time() - 120))]);

        $this->assertStringContainsString("<a href='https://app.example.com/users/editOwn#notifications'>Change how often</a>", (string) $this->sent[0]['footer']);
    }

    public function test_a_company_colour_too_light_to_read_falls_back_to_a_dark_teal(): void
    {
        $this->settings['companysettings.primarycolor'] = '#ffeb3b';
        $this->worker()->handleQueue([$this->summaryRow('h1', 'Marcel updated this task', gmdate('Y-m-d H:i:s', time() - 120))]);

        $html = $this->sent[0]['html'];
        $this->assertStringNotContainsString('#ffeb3b', $html);
        $this->assertStringContainsString('background: #00607d', $html);
    }
}

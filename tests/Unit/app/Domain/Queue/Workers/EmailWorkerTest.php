<?php

namespace Unit\app\Domain\Queue\Workers;

use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Mailer;
use Leantime\Domain\Notifications\Repositories\Notifications as NotificationsRepository;
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
    ];

    /** @var array<string, string> zp_settings by key */
    private array $settings = [];

    /** @var array<int, array{to: array, subject: string, html: string}> */
    private array $sent = [];

    /** @var array<int, array<int, string>> Each deleteMessageInQueue() call's hashes. */
    private array $deleted = [];

    /** @var array<string, string> "module:moduleId" => last read datetime, as the repository returns it. */
    private array $lastRead = [];

    /** @var array<int, array{userId: int, entities: array}> */
    private array $lastReadLookups = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->settings = [];
        $this->sent = [];
        $this->deleted = [];
        $this->lastRead = [];
        $this->lastReadLookups = [];
    }

    private function worker(): EmailWorker
    {
        $subject = '';
        $html = '';

        return new EmailWorker(
            $this->make(UserRepository::class, [
                'getUser' => fn ($id) => (int) $id === 404 ? false : ['id' => (int) $id, 'username' => 'user'.$id.'@example.com'],
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
                'sendMail' => function (array $to) use (&$subject, &$html) {
                    $this->sent[] = ['to' => $to, 'subject' => $subject, 'html' => $html];

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
                'lastReadByEntity' => function (int $userId, array $entities) {
                    $this->lastReadLookups[] = compact('userId', 'entities');

                    return $this->lastRead;
                },
            ]),
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
        $this->lastRead = ['tickets:12' => gmdate('Y-m-d H:i:s', time() - 60)];

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
        $this->assertSame([['userId' => 2, 'entities' => [['module' => 'tickets', 'moduleId' => 12], ['module' => 'comments', 'moduleId' => 77]]]], $this->lastReadLookups);
    }

    public function test_nothing_new_means_no_email_and_the_rows_wait(): void
    {
        $this->lastRead = ['tickets:12' => gmdate('Y-m-d H:i:s')];

        $this->worker()->handleQueue([$this->row('seen', 2, 'Ticket updated', 'tickets', 12)]);

        $this->assertSame([], $this->sent);
        $this->assertSame([], $this->deleted, 'the seen row stays for the next digest that has news');
        $this->assertArrayNotHasKey('usersettings.2.lastMessageDate', $this->settings, 'nothing was sent, so the clock does not restart');
    }

    public function test_a_read_older_than_the_queued_row_does_not_count(): void
    {
        // Read five minutes ago; the row was queued two minutes ago (grace is two minutes).
        $this->lastRead = ['tickets:12' => gmdate('Y-m-d H:i:s', time() - 300)];

        $this->worker()->handleQueue([$this->row('h1', 2, 'Ticket updated', 'tickets', 12)]);

        $this->assertCount(1, $this->sent);
        $this->assertStringNotContainsString('Already seen', $this->sent[0]['html']);
    }

    public function test_a_read_just_before_the_row_was_queued_counts_within_the_grace(): void
    {
        $queuedAt = time() - 120;
        $this->lastRead = ['tickets:12' => gmdate('Y-m-d H:i:s', $queuedAt - EmailWorker::SEEN_GRACE_SECONDS + 1)];

        $this->worker()->handleQueue([
            $this->row('seen', 2, 'Ticket updated', 'tickets', 12, gmdate('Y-m-d H:i:s', $queuedAt)),
            $this->row('fresh', 2, 'New comment', 'comments', 77),
        ]);

        $this->assertStringContainsString('Already seen', $this->sent[0]['html']);
    }

    public function test_rows_without_an_entity_are_always_new_and_never_looked_up(): void
    {
        $this->lastRead = ['tickets:12' => gmdate('Y-m-d H:i:s')];

        $this->worker()->handleQueue([$this->row('legacy', 2, 'Project created', null, null)]);

        $this->assertCount(1, $this->sent);
        $this->assertSame([], $this->lastReadLookups, 'no entity, nothing to ask the inbox');
    }

    public function test_the_seen_subject_is_escaped(): void
    {
        $this->lastRead = ['tickets:12' => gmdate('Y-m-d H:i:s')];

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
        $this->lastRead = ['tickets:12' => gmdate('Y-m-d H:i:s')];

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
}

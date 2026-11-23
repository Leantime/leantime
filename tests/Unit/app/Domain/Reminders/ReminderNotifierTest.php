<?php

namespace Unit\app\Domain\Reminders;

use Carbon\CarbonImmutable;
use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Mailer as MailerCore;
use Leantime\Domain\Notifications\Services\Notifications as NotificationService;
use Leantime\Domain\Notifications\Services\Push;
use Leantime\Domain\Reminders\Services\ReminderNotifier;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Unit\TestCase;

/**
 * The morning summary goes through the existing channels: the inbox always and first, email right
 * away when the person kept email on, push when they kept push on.
 */
class ReminderNotifierTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<int, array<string, mixed>> */
    private array $inbox = [];

    /** @var array<int, array{to: array, subject: string, html: string, headline: ?string, footer: ?string, text: string}> */
    private array $emails = [];

    /** @var array<int, array{userIds: array, title: string, body: string, data: array}> */
    private array $pushes = [];

    /** @var array<string, string> */
    private array $settings = [];

    private bool $pushFails = false;

    private ?bool $inboxResult = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inbox = [];
        $this->emails = [];
        $this->pushes = [];
        $this->settings = [];
        $this->pushFails = false;
        $this->inboxResult = true;

        $mail = ['subject' => '', 'html' => '', 'headline' => null, 'footer' => null, 'text' => ''];
        app()->instance(MailerCore::class, $this->make(MailerCore::class, [
            'setContext' => fn () => null,
            'setSubject' => function ($subject) use (&$mail) {
                $mail['subject'] = $subject;
            },
            'setHtml' => function ($html) use (&$mail) {
                $mail['html'] = $html;
            },
            'setHeadline' => function ($headline) use (&$mail) {
                $mail['headline'] = $headline;
            },
            'setFooter' => function ($footer) use (&$mail) {
                $mail['footer'] = $footer;
            },
            'setText' => function ($text) use (&$mail) {
                $mail['text'] = $text;
            },
            'sendMail' => function (array $to) use (&$mail) {
                $this->emails[] = ['to' => $to] + $mail;

                return true;
            },
        ]));
    }

    private function notifier(): ReminderNotifier
    {
        $strings = parse_ini_file(APP_ROOT.'/app/Language/en-US.ini', false, INI_SCANNER_RAW);

        return new ReminderNotifier(
            $this->make(NotificationService::class, ['addNotifications' => function (array $rows) {
                if ($this->inboxResult === true) {
                    $this->inbox = array_merge($this->inbox, $rows);
                }

                return $this->inboxResult;
            }]),
            $this->make(SettingRepository::class, ['getSetting' => fn (string $key) => $this->settings[$key] ?? false]),
            $this->make(LanguageCore::class, ['__' => fn (string $key) => $strings[$key] ?? $key]),
            $this->make(Push::class, ['send' => function (array $userIds, string $title, string $body, array $data = []) {
                if ($this->pushFails) {
                    throw new \RuntimeException('relay down');
                }
                $this->pushes[] = compact('userIds', 'title', 'body', 'data');
            }]),
            $this->make(TrustedAppUrl::class, ['forLinks' => fn () => 'https://pm.example.test']),
        );
    }

    private function summary(): array
    {
        $item = fn (string $kind, int $id, string $title, int $days) => ['kind' => $kind, 'id' => $id, 'title' => $title, 'projectId' => 1, 'projectName' => 'Website', 'due' => '', 'daysUntil' => $days, 'url' => 'https://pm.example.test/x/'.$id];

        return [
            'date' => '2026-11-22',
            'items' => [$item('task', 1, 'Send the invoice', 0), $item('task', 2, 'Call <Ada>', 1), $item('milestone', 3, 'Beta', 7)],
            'moreCount' => 2,
            'routine' => ['count' => 1, 'items' => []],
            'overdueCount' => 3,
            'dueToday' => 2,
            'dueTomorrow' => 1,
            'later' => 3,
            'isEmpty' => false,
        ];
    }

    private function now(): CarbonImmutable
    {
        return CarbonImmutable::parse('2026-11-22 08:00:00', 'UTC');
    }

    public function test_a_summary_goes_to_the_inbox_email_and_push(): void
    {
        $this->notifier()->sendSummary(['id' => 7, 'notifications' => 1, 'username' => 'sam@example.test'], $this->summary(), $this->now());

        $this->assertCount(1, $this->inbox);
        $this->assertSame('morning_summary', $this->inbox[0]['type']);
        $this->assertSame('reminders', $this->inbox[0]['module']);
        $this->assertSame('Your morning summary: 2 due today, 1 due tomorrow, 3 coming up', $this->inbox[0]['message']);
        $this->assertSame('2026-11-22 08:00:00', $this->inbox[0]['datetime']);
        $this->assertSame('https://pm.example.test/dashboard/home#/reminders/comingUp', $this->inbox[0]['url'], 'the row opens the Coming up panel');

        $this->assertCount(1, $this->emails);
        $this->assertSame(['sam@example.test'], $this->emails[0]['to']);
        $this->assertSame('Coming up: 2 due today, 1 due tomorrow, 3 coming up', $this->emails[0]['subject']);
        $this->assertStringContainsString('Call &lt;Ada&gt;', $this->emails[0]['html'], 'titles are escaped');
        $this->assertStringContainsString('Due tomorrow · Website', $this->emails[0]['html']);
        $this->assertStringContainsString('Ends in 7 days', $this->emails[0]['html']);
        $this->assertStringContainsString('And 2 more', $this->emails[0]['html']);
        $this->assertStringContainsString('3 still open from earlier', $this->emails[0]['html']);
        $this->assertStringNotContainsString("\n", $this->emails[0]['html'], 'the mailer turns newlines into breaks');

        $this->assertCount(1, $this->pushes);
        $this->assertSame([7], $this->pushes[0]['userIds']);
        $this->assertSame('2 due today, 1 due tomorrow, 3 coming up', $this->pushes[0]['title']);
        $this->assertSame("Send the invoice · Due today\nCall <Ada> · Due tomorrow\nRoutine tasks today: 1\nAnd 3 more", $this->pushes[0]['body']);
        $this->assertSame(['module' => 'reminders', 'moduleId' => '0', 'type' => 'morning_summary', 'threadId' => 'morning-summary', 'url' => 'https://pm.example.test/dashboard/home#/reminders/comingUp', 'tier' => Push::TIER_DIRECT, 'category' => Push::CATEGORY_NONE], $this->pushes[0]['data']);
    }

    public function test_the_email_greets_by_name_groups_by_day_and_explains_its_own_settings(): void
    {
        $this->notifier()->sendSummary(
            ['id' => 7, 'notifications' => 1, 'username' => 'sam@example.test', 'firstname' => 'Sam <B>'],
            $this->summary(),
            $this->now(),
            '07:30'
        );

        $email = $this->emails[0];
        $this->assertSame('Hi Sam &lt;B&gt;,', $email['headline'], 'the name is escaped');

        $html = $email['html'];
        $today = strpos($html, '>Today<');
        $tomorrow = strpos($html, '>Tomorrow<');
        $week = strpos($html, '>Coming up this week<');
        $this->assertNotFalse($today);
        $this->assertTrue($today < strpos($html, 'Send the invoice') && strpos($html, 'Send the invoice') < $tomorrow, 'today\'s to-do sits under Today');
        $this->assertTrue($tomorrow < strpos($html, 'Call &lt;Ada&gt;') && strpos($html, 'Call &lt;Ada&gt;') < $week, 'tomorrow\'s to-do sits under Tomorrow');
        $this->assertTrue($week < strpos($html, 'Beta'), 'the milestone sits under Coming up this week');
        $this->assertTrue(strpos($html, 'Routine tasks today: 1') < $tomorrow, 'the routine line belongs to Today');
        $this->assertStringContainsString('https://pm.example.test/x/1', $html, 'each item links to it');
        $this->assertStringContainsString('>See what&#039;s coming up</a>', $html);
        $this->assertStringContainsString('href="https://pm.example.test/dashboard/home#/reminders/comingUp"', $html, 'the button opens the Coming up panel');
        $this->assertStringNotContainsString("\n", $html);

        $this->assertStringContainsString('at 07:30 AM (your time)', $email['footer'], 'no time format of their own: the language default (h:i A)');
        $this->assertStringContainsString("href='https://pm.example.test/users/editOwn#notifications'", $email['footer']);
        $this->assertStringNotContainsString('unsubscribe', $email['footer']);

        $text = $email['text'];
        $this->assertStringStartsWith("Hi Sam <B>,\n\nHere is what's coming up for you.", $text);
        $this->assertStringContainsString("Today\n- Send the invoice (Due today · Website): https://pm.example.test/x/1\n- Routine tasks today: 1", $text);
        $this->assertStringContainsString("Coming up this week\n- Beta (Ends in 7 days · Website): https://pm.example.test/x/3", $text);
        $this->assertStringContainsString('3 still open from earlier', $text);
        $this->assertStringContainsString("See what's coming up: https://pm.example.test/dashboard/home#/reminders/comingUp", $text);
        $this->assertStringContainsString('at 07:30 AM (your time). Change the time or turn it off in your notification settings: https://pm.example.test/users/editOwn#notifications', $text);
    }

    public function test_nothing_leaves_the_server_when_the_inbox_row_is_not_written(): void
    {
        foreach ([false, null] as $result) {
            $this->inboxResult = $result;
            try {
                $this->notifier()->sendSummary(['id' => 7, 'notifications' => 1, 'username' => 'sam@example.test'], $this->summary(), $this->now());
                $this->fail('a summary without its inbox record');
            } catch (\RuntimeException) {
                $this->addToAssertionCount(1);
            }
        }

        $this->assertSame([], $this->emails);
        $this->assertSame([], $this->pushes);
    }

    public function test_items_more_than_a_week_away_are_not_called_this_week(): void
    {
        $summary = $this->summary();
        $summary['items'][] = ['kind' => 'task', 'id' => 4, 'title' => 'Write the annual report', 'projectId' => 1, 'projectName' => 'Website', 'due' => '', 'daysUntil' => 12, 'url' => 'https://pm.example.test/x/4'];

        $email = $this->notifier()->summaryEmail($summary, 'https://pm.example.test/dashboard/home', 'Sam', '08:00 AM');

        $week = strpos($email['html'], '>Coming up this week<');
        $later = strpos($email['html'], '>Later<');
        $this->assertNotFalse($later);
        $this->assertTrue($week < strpos($email['html'], 'Beta') && strpos($email['html'], 'Beta') < $later, '7 days away is this week');
        $this->assertTrue($later < strpos($email['html'], 'Write the annual report'), '12 days away is later');
        $this->assertStringContainsString("Later\n- Write the annual report (Due in 12 days · Website)", $email['text']);
    }

    public function test_the_email_names_the_time_in_the_recipient_s_own_format(): void
    {
        $user = ['id' => 7, 'notifications' => 1, 'username' => 'sam@example.test'];

        $this->settings['usersettings.7.time_format'] = 'H:i';
        $this->notifier()->sendSummary($user, $this->summary(), $this->now(), '14:00');
        $this->assertStringContainsString('at 14:00 (your time)', $this->emails[0]['footer'], '24-hour');

        $this->settings['usersettings.7.time_format'] = 'h:i A';
        $this->notifier()->sendSummary($user, $this->summary(), $this->now(), '14:00');
        $this->assertStringContainsString('at 02:00 PM (your time)', $this->emails[1]['footer'], '12-hour');
        $this->assertStringContainsString('at 02:00 PM (your time). Change the time', $this->emails[1]['text']);

        $this->settings['usersettings.8.time_format'] = 'H:i'; // someone else's setting doesn't count
        unset($this->settings['usersettings.7.time_format']);
        $this->notifier()->sendSummary($user, $this->summary(), $this->now(), '14:00');
        $this->assertStringContainsString('at 02:00 PM (your time)', $this->emails[2]['footer'], 'the language default');
    }

    public function test_the_email_falls_back_to_the_plain_greeting_without_a_first_name(): void
    {
        $this->notifier()->sendSummary(['id' => 7, 'notifications' => 1, 'username' => 'sam@example.test', 'firstname' => ''], $this->summary(), $this->now());

        $this->assertSame('Hi, ', $this->emails[0]['headline']);
        $this->assertStringContainsString('at 08:00 AM (your time)', $this->emails[0]['footer'], 'the default time');
    }

    public function test_the_inbox_row_is_written_before_email_and_push(): void
    {
        $order = [];
        $notifier = new ReminderNotifier(
            $this->make(NotificationService::class, ['addNotifications' => function () use (&$order) {
                $order[] = 'inbox';

                return true;
            }]),
            $this->make(SettingRepository::class, ['getSetting' => fn () => false]),
            $this->make(LanguageCore::class, ['__' => fn (string $key) => $key]),
            $this->make(Push::class, ['send' => function () use (&$order) {
                $order[] = 'push';
            }]),
            $this->make(TrustedAppUrl::class, ['forLinks' => fn () => 'https://pm.example.test']),
        );
        app()->instance(MailerCore::class, $this->make(MailerCore::class, [
            'setContext' => fn () => null, 'setSubject' => fn () => null, 'setHtml' => fn () => null,
            'setHeadline' => fn () => null, 'setFooter' => fn () => null, 'setText' => fn () => null,
            'sendMail' => function () use (&$order) {
                $order[] = 'email';

                return true;
            },
        ]));

        $notifier->sendSummary(['id' => 7, 'notifications' => 1, 'username' => 'sam@example.test'], $this->summary(), $this->now());

        $this->assertSame(['inbox', 'email', 'push'], $order);
    }

    public function test_email_and_push_follow_the_person_s_switches_and_one_failure_never_stops_the_rest(): void
    {
        $this->settings['usersettings.7.pushNotifications'] = '0';
        $this->notifier()->sendSummary(['id' => 7, 'notifications' => 0, 'username' => 'sam@example.test'], $this->summary(), $this->now());
        $this->assertCount(1, $this->inbox);
        $this->assertSame([], $this->emails);
        $this->assertSame([], $this->pushes);

        $this->settings = [];
        $this->pushFails = true;
        $this->notifier()->sendSummary(['id' => 7, 'notifications' => 1, 'username' => 'sam@example.test'], $this->summary(), $this->now());
        $this->assertCount(2, $this->inbox);
        $this->assertCount(1, $this->emails);
    }

    /** A week as WeeklySummaryBuilder::buildWeek() returns it: Monday 2026-11-23 to Sunday. */
    private function week(bool $withToday = false, bool $withPlugin = true): array
    {
        $item = fn (string $kind, int $id, string $title, int $days) => ['kind' => $kind, 'id' => $id, 'title' => $title, 'projectId' => 1, 'projectName' => 'Website', 'due' => '', 'daysUntil' => $days, 'url' => 'https://pm.example.test/x/'.$id, 'isYours' => true];
        $ahead = [
            'kind' => 'ahead',
            'items' => [$item('task', 1, 'Send the invoice', 0), $item('milestone', 3, 'Beta', 3), $item('task', 2, 'Call <Ada>', 4)],
            'moreCount' => 2,
            'dueCount' => 4,
            'endingCount' => 1,
            'planned' => [['date' => '2026-11-25', 'count' => 2, 'timeBoxed' => 1], ['date' => '2026-11-26', 'count' => 1, 'timeBoxed' => 0]],
            'plannedCount' => 3,
            'routine' => ['count' => 1],
            'overdueCount' => 2,
        ];
        $lookBack = [
            'kind' => 'lookBack', 'start' => '2026-11-16', 'end' => '2026-11-22', 'count' => 6,
            'items' => [
                ['kind' => 'task', 'id' => 9, 'title' => 'Ship v2', 'projectId' => 1, 'projectName' => 'Website', 'url' => 'https://pm.example.test/x/9', 'isYours' => true, 'milestoneId' => null, 'finishedAt' => '2026-11-20T10:00:00Z', 'closedBy' => ['userId' => 8, 'name' => 'Marcel']],
            ],
        ];
        $today = $withToday ? ['kind' => 'today'] + $this->summary() : null;
        $plugin = ['kind' => 'plugin:myweek.picks', 'title' => 'What you picked', 'lines' => ['Sooner: 2'], 'url' => 'https://pm.example.test/my-week', 'countsForSending' => true];

        return [
            'week' => ['start' => '2026-11-23', 'end' => '2026-11-29'],
            'today' => $today,
            'ahead' => $ahead,
            'lookBack' => $lookBack,
            'sections' => array_values(array_filter([$today, $ahead, $lookBack, $withPlugin ? $plugin : null])),
            'isEmpty' => false,
        ];
    }

    public function test_a_weekly_summary_writes_its_own_inbox_row_first(): void
    {
        $this->notifier()->recordWeekly(['id' => 7, 'notifications' => 1], $this->week(), CarbonImmutable::parse('2026-11-23 08:00:00', 'UTC'));

        $this->assertCount(1, $this->inbox);
        $this->assertSame('weekly_summary', $this->inbox[0]['type']);
        $this->assertSame('reminders', $this->inbox[0]['module']);
        $this->assertSame('Your week: 5 due, 3 planned', $this->inbox[0]['message']);
        $this->assertSame('https://pm.example.test/dashboard/home#/reminders/thisWeek', $this->inbox[0]['url']);
        $this->assertSame('2026-11-23 08:00:00', $this->inbox[0]['datetime']);

        $this->inboxResult = null;
        $this->expectException(\RuntimeException::class);
        $this->notifier()->recordWeekly(['id' => 7, 'notifications' => 1], $this->week(), CarbonImmutable::parse('2026-11-23 08:00:00', 'UTC'));
    }

    public function test_the_weekly_push_is_routed_like_the_morning_one_and_says_whether_today_is_in_it(): void
    {
        $this->notifier()->deliverWeekly(['id' => 7, 'notifications' => 0], $this->week(withToday: true), 1, '08:00');

        $this->assertSame([], $this->emails, 'email off');
        $this->assertCount(1, $this->pushes);
        $this->assertSame('This week: 5 due, 3 planned', $this->pushes[0]['title']);
        $this->assertSame("Today: 2 due today, 1 due tomorrow, 3 coming up\nSend the invoice · Today\nBeta · Thu", $this->pushes[0]['body'], 'about three short lines, today first');
        $this->assertSame(['module' => 'reminders', 'moduleId' => '0', 'type' => 'weekly_summary', 'threadId' => 'weekly-summary', 'url' => 'https://pm.example.test/dashboard/home#/reminders/thisWeek', 'includesToday' => '1', 'tier' => Push::TIER_DIRECT, 'category' => Push::CATEGORY_NONE], $this->pushes[0]['data']);

        $this->pushes = [];
        $this->notifier()->deliverWeekly(['id' => 7, 'notifications' => 0], $this->week(), 1, '08:00');
        $this->assertSame('0', $this->pushes[0]['data']['includesToday']);
        $this->assertSame("Send the invoice · Today\nBeta · Thu\nYou finished 6 to-dos last week.", $this->pushes[0]['body']);
    }

    public function test_the_weekly_email_has_this_week_last_week_and_plugin_sections(): void
    {
        $this->settings['usersettings.7.time_format'] = 'h:i A';

        $this->notifier()->deliverWeekly(['id' => 7, 'notifications' => 1, 'username' => 'sam@example.test', 'firstname' => 'Sam'], $this->week(), 5, '16:30');

        $this->assertCount(1, $this->emails);
        $email = $this->emails[0];
        $this->assertSame(['sam@example.test'], $email['to']);
        $this->assertSame('This week: 5 due, 3 planned', $email['subject']);
        $this->assertSame('Hi Sam,', $email['headline']);

        foreach (['This week', 'Send the invoice', 'Today · Website', 'Beta', 'Thu', 'Call &lt;Ada&gt;', 'Fri · Website', 'And 2 more', 'Routine tasks this week: 1',
            'Wed: 2 planned (1 with a set time)', 'Thu: 1 planned', '2 still open from earlier',
            'Last week', 'You finished 6 to-dos last week.', 'Ship v2', 'Website · closed by Marcel',
            'What you picked', 'Sooner: 2', 'See your week'] as $text) {
            $this->assertStringContainsString($text, $email['html']);
        }
        $this->assertStringNotContainsString('<Ada>', $email['html'], 'escaped');
        $this->assertStringContainsString('dashboard/home#/reminders/thisWeek', $email['html']);
        $this->assertStringContainsString('on Friday at 04:30 PM (your time)', $email['footer'], 'the person\'s day and time format');
        $this->assertStringContainsString('- Call <Ada> (Fri · Website): ', $email['text'], 'the text part mirrors it');
        $this->assertStringNotContainsString('<p', $email['text']);
    }

    public function test_the_weekly_email_puts_today_first_when_it_is_folded_in(): void
    {
        $this->notifier()->deliverWeekly(['id' => 7, 'notifications' => 1, 'username' => 'sam@example.test'], $this->week(withToday: true, withPlugin: false), 1, '08:00');

        $html = $this->emails[0]['html'];
        $this->assertLessThan(strpos($html, '>This week<'), strpos($html, '>Today<'));
        $this->assertStringContainsString('Routine tasks today: 1', $html);
        $this->assertStringNotContainsString('What you picked', $html);
    }

    public function test_the_weekly_counts_line_falls_back_to_the_look_back_then_the_title(): void
    {
        $week = $this->week();
        $week['ahead'] = ['dueCount' => 0, 'endingCount' => 0, 'plannedCount' => 0] + $week['ahead'];
        $this->assertSame('6 finished last week', $this->notifier()->weeklyCountsLine($week));

        $week['lookBack'] = null;
        $this->assertSame('Your week', $this->notifier()->weeklyCountsLine($week));
    }
}

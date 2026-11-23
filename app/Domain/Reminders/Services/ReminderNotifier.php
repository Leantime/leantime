<?php

namespace Leantime\Domain\Reminders\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Mailer as MailerCore;
use Leantime\Domain\Notifications\Services\Notifications as NotificationService;
use Leantime\Domain\Notifications\Services\Push;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;

/**
 * Sends the morning summary through the channels that already exist, in this order:
 *
 *   1. inbox (zp_notifications): always, and first. This row is also the record that today's
 *      summary went out (see {@see ReminderDispatcher}), so a failed write throws and nothing
 *      else is sent; the person stays due for the next run.
 *   2. email: sent right away, not through the digest queue, when the person kept email on
 *      ("Receive email notifications"). The scheduler already picked the right time.
 *   3. push: when the person kept push on (usersettings.{id}.pushNotifications)
 *
 * A failing email or push never stops the other, and neither undoes the inbox row.
 */
class ReminderNotifier
{
    public const TYPE_SUMMARY = 'morning_summary';

    public const MODULE = 'reminders';

    public function __construct(
        private NotificationService $notifications,
        private SettingRepository $settings,
        private LanguageCore $language,
        private Push $push,
        private TrustedAppUrl $trustedAppUrl,
    ) {}

    /**
     * Records the summary, then delivers it: {@see recordSummary()} and {@see deliverSummary()}.
     *
     * @param  array<string, mixed>  $summary  From {@see SummaryBuilder::build()}.
     * @param  array{id: int, notifications: int, username?: string, firstname?: string}  $user
     * @param  string  $summaryTime  The person's summary time ('HH:MM'), named in the email footer.
     */
    public function sendSummary(array $user, array $summary, CarbonImmutable $nowUtc, string $summaryTime = ReminderSettings::DEFAULT_SUMMARY_TIME): void
    {
        $this->recordSummary($user, $summary, $nowUtc);
        $this->deliverSummary($user, $summary, $summaryTime);
    }

    /**
     * Writes the inbox row: the record that today's summary went out. It must be written before
     * anything leaves the server, so a failed write throws; the dispatcher then sends nothing and
     * leaves the person due for the next run.
     *
     * @param  array<string, mixed>  $summary
     * @param  array{id: int, notifications: int, username?: string, firstname?: string}  $user
     */
    public function recordSummary(array $user, array $summary, CarbonImmutable $nowUtc): void
    {
        $userId = (int) $user['id'];

        $written = $this->notifications->addNotifications([[
            'userId' => $userId,
            'read' => '0',
            'type' => self::TYPE_SUMMARY,
            'module' => self::MODULE,
            'moduleId' => 0,
            'message' => sprintf($this->language->__('reminders.summary_inbox'), $this->countsLine($summary)),
            'datetime' => $nowUtc->format('Y-m-d H:i:s'),
            'url' => $this->comingUpUrl(),
            'authorId' => 0,
        ]]);
        if ($written !== true) {
            throw new \RuntimeException('The morning summary inbox row for user '.$userId.' was not written.');
        }
    }

    /**
     * Email and push, after the inbox row is written. Each one's failure is logged and never
     * stops the other.
     *
     * @param  array<string, mixed>  $summary
     * @param  array{id: int, notifications: int, username?: string, firstname?: string}  $user
     */
    public function deliverSummary(array $user, array $summary, string $summaryTime = ReminderSettings::DEFAULT_SUMMARY_TIME): void
    {
        $userId = (int) $user['id'];
        $url = $this->comingUpUrl();
        $counts = $this->countsLine($summary);

        if ($user['notifications'] !== 0 && ($user['username'] ?? '') !== '') {
            try {
                $this->sendMail(
                    (string) $user['username'],
                    sprintf($this->language->__('reminders.email_subject'), $counts),
                    $this->summaryEmail($summary, $url, (string) ($user['firstname'] ?? ''), $this->timeForPerson($userId, $summaryTime))
                );
            } catch (\Throwable $e) {
                Log::warning('Morning summary email failed for user '.$userId.': '.$e->getMessage());
            }
        }

        if ($this->pushEnabled($userId)) {
            try {
                $this->push->send([$userId], $counts, $this->summaryPushBody($summary), [
                    'module' => self::MODULE,
                    'moduleId' => '0',
                    'type' => self::TYPE_SUMMARY,
                    'threadId' => 'morning-summary',
                    'url' => $url,
                    'tier' => Push::TIER_DIRECT,
                    'category' => Push::CATEGORY_NONE,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Morning summary push failed for user '.$userId.': '.$e->getMessage());
            }
        }
    }

    /**
     * The summary time as the recipient reads times: their own time format setting, else the
     * language's default (the scheduler has no session, so it's read from storage).
     */
    private function timeForPerson(int $userId, string $summaryTime): string
    {
        $stored = $this->settings->getSetting('usersettings.'.$userId.'.time_format');
        $format = is_string($stored) && trim($stored) !== '' ? html_entity_decode($stored, ENT_QUOTES) : (string) $this->language->__('language.timeformat');

        return ReminderSettings::formatTime($summaryTime, $format !== '' && $format !== 'language.timeformat' ? $format : 'H:i');
    }

    /**
     * Where the summary's inbox row, email button and push lead: the web home with the "Coming up"
     * panel open. Push data keeps module 'reminders', so apps can route by module instead.
     */
    public function comingUpUrl(): string
    {
        return rtrim($this->trustedAppUrl->forLinks(), '/').'/dashboard/home#/reminders/comingUp';
    }

    /** "2 due today, 1 due tomorrow, 1 coming up". */
    public function countsLine(array $summary): string
    {
        $parts = [];
        if ($summary['dueToday'] > 0) {
            $parts[] = sprintf($this->language->__('reminders.count_today'), $summary['dueToday']);
        }
        if ($summary['dueTomorrow'] > 0) {
            $parts[] = sprintf($this->language->__('reminders.count_tomorrow'), $summary['dueTomorrow']);
        }
        if ($summary['later'] > 0) {
            $parts[] = sprintf($this->language->__('reminders.count_later'), $summary['later']);
        }

        return $parts === [] ? $this->language->__('reminders.summary_title') : implode(', ', $parts);
    }

    /** "Due today", "Due tomorrow", "Due in 3 days"; "Ends …" for milestones and projects. */
    public function whenText(string $kind, int $daysUntil): string
    {
        $prefix = $kind === 'task' ? 'reminders.due' : 'reminders.ends';

        return match (true) {
            $daysUntil <= 0 => $this->language->__($prefix.'_today'),
            $daysUntil === 1 => $this->language->__($prefix.'_tomorrow'),
            default => sprintf($this->language->__($prefix.'_in_days'), $daysUntil),
        };
    }

    /**
     * The push body: the first two items, then "and N more". Plain text, one item per line.
     *
     * @param  array<string, mixed>  $summary
     */
    private function summaryPushBody(array $summary): string
    {
        $lines = [];
        foreach (array_slice($summary['items'], 0, 2) as $item) {
            $lines[] = $item['title'].' · '.$this->whenText($item['kind'], $item['daysUntil']);
        }

        $rest = count($summary['items']) - count($lines) + $summary['moreCount'];
        if ($summary['routine']['count'] > 0) {
            $lines[] = sprintf($this->language->__('reminders.routine'), $summary['routine']['count']);
        }
        if ($rest > 0) {
            $lines[] = sprintf($this->language->__('reminders.more'), $rest);
        }

        return implode("\n", $lines);
    }

    /**
     * The email: a greeting by first name, the items grouped by day (today, tomorrow, the rest
     * of the next 7 days, later), a button to the home page,
     * and a footer that says why this email came and where to change it. HTML is kept on single
     * lines because the mailer turns newlines into line breaks; the text part mirrors it.
     *
     * @param  array<string, mixed>  $summary
     * @param  string  $displayTime  The summary time as the person reads times ("02:00 PM", "14:00").
     * @return array{headline: string, html: string, text: string, footer: string}
     */
    public function summaryEmail(array $summary, string $homeUrl, string $firstname, string $displayTime): array
    {
        $groups = [
            'today' => ['heading' => $this->language->__('reminders.email_today'), 'items' => []],
            'tomorrow' => ['heading' => $this->language->__('reminders.email_tomorrow'), 'items' => []],
            'week' => ['heading' => $this->language->__('reminders.email_this_week'), 'items' => []],
            // "more" can list a started to-do due weeks away: it isn't "this week".
            'later' => ['heading' => $this->language->__('reminders.email_later'), 'items' => []],
        ];
        foreach ($summary['items'] as $item) {
            $group = match (true) {
                $item['daysUntil'] <= 0 => 'today',
                $item['daysUntil'] === 1 => 'tomorrow',
                $item['daysUntil'] <= 7 => 'week',
                default => 'later',
            };
            $groups[$group]['items'][] = $item;
        }

        $html = '<p>'.$this->escape($this->language->__('reminders.email_intro')).'</p>';
        $text = [$this->language->__('reminders.email_intro'), ''];

        foreach ($groups as $key => $group) {
            $routine = $key === 'today' && $summary['routine']['count'] > 0
                ? sprintf($this->language->__('reminders.routine'), $summary['routine']['count'])
                : null;
            if ($group['items'] === [] && $routine === null) {
                continue;
            }

            $html .= '<p style="margin: 18px 3px 4px; font-weight: bold; color: #333;">'.$this->escape($group['heading']).'</p>';
            $text[] = $group['heading'];

            foreach ($group['items'] as $item) {
                $link = MailerCore::notificationEmailUrl($item['url'], $item['kind'] === 'task' ? 'tickets' : 'reminders');
                $detail = $this->whenText($item['kind'], $item['daysUntil'])
                    .($item['projectName'] !== '' && $item['kind'] !== 'project' ? ' · '.$item['projectName'] : '');

                $html .= '<div style="border-top: 1px solid #ddd; margin: 3px; padding: 6px 3px;">'
                    .'<a href="'.$this->escape($link).'"><strong>'.$this->escape($item['title']).'</strong></a>'
                    .'<br/><span style="color: #555;">'.$this->escape($detail).'</span></div>';
                $text[] = '- '.$item['title'].' ('.$detail.'): '.$link;
            }

            if ($routine !== null) {
                $html .= '<p style="margin: 6px 3px; color: #555;">'.$this->escape($routine).'</p>';
                $text[] = '- '.$routine;
            }
            $text[] = '';
        }

        $extra = [];
        if ($summary['moreCount'] > 0) {
            $extra[] = sprintf($this->language->__('reminders.more'), $summary['moreCount']);
        }
        if ($summary['overdueCount'] > 0) {
            $extra[] = sprintf($this->language->__('reminders.still_open'), $summary['overdueCount']);
        }
        foreach ($extra as $line) {
            $html .= '<p style="margin: 6px 3px; color: #666;">'.$this->escape($line).'</p>';
            $text[] = $line;
        }

        $home = MailerCore::notificationEmailUrl($homeUrl, 'reminders');
        $html .= '<p style="margin-top: 20px;"><a href="'.$this->escape($home).'" style="display: inline-block; padding: 10px 18px; border-radius: 4px; background: #004666; color: #ffffff; text-decoration: none; font-weight: bold;">'
            .$this->escape($this->language->__('reminders.email_button')).'</a></p>';
        $text[] = '';
        $text[] = $this->language->__('reminders.email_button').': '.$home;

        $settingsUrl = MailerCore::notificationEmailUrl(rtrim($this->trustedAppUrl->forLinks(), '/').'/users/editOwn#notifications', 'reminders');
        $footer = sprintf($this->language->__('reminders.email_footer'), $this->escape($displayTime), $this->escape($settingsUrl));
        $text[] = '';
        $text[] = sprintf($this->language->__('reminders.email_footer_text'), $displayTime, $settingsUrl);

        $headline = $firstname !== ''
            ? sprintf($this->language->__('reminders.email_hi_name'), $this->escape($firstname))
            : $this->language->__('email_notifications.hi');

        return [
            'headline' => $headline,
            'html' => $html,
            'text' => ($firstname !== '' ? sprintf($this->language->__('reminders.email_hi_name'), $firstname) : trim($this->language->__('email_notifications.hi')))."\n\n".implode("\n", $text),
            'footer' => $footer,
        ];
    }

    /**
     * @param  array{headline: string, html: string, text: string, footer: string}  $email
     */
    private function sendMail(string $to, string $subject, array $email): void
    {
        $mailer = app()->make(MailerCore::class);
        $mailer->setContext('reminders');
        $mailer->setSubject($subject);
        $mailer->setHeadline($email['headline']);
        $mailer->setFooter($email['footer']);
        $mailer->setHtml($email['html']);
        $mailer->setText($email['text']);
        $mailer->sendMail([$to], 'Leantime System');
    }

    /** The per-user push switch: on unless stored as '0'. */
    private function pushEnabled(int $userId): bool
    {
        $stored = $this->settings->getSetting('usersettings.'.$userId.'.pushNotifications');

        return $stored === false || $stored === null || $stored === '' || (string) $stored === '1';
    }

    private function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

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
 *      summary went out (see {@see ReminderDispatcher}).
 *   2. email: sent right away, not through the digest queue, when the person kept email on
 *      ("Receive email notifications"). The scheduler already picked the right time.
 *   3. push: when the person kept push on (usersettings.{id}.pushNotifications)
 *
 * A failing email or push never stops the other.
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
     * @param  array<string, mixed>  $summary  From {@see SummaryBuilder::build()}.
     * @param  array{id: int, notifications: int, username?: string}  $user
     */
    public function sendSummary(array $user, array $summary, CarbonImmutable $nowUtc): void
    {
        $userId = (int) $user['id'];
        $url = rtrim($this->trustedAppUrl->forLinks(), '/').'/dashboard/home';
        $counts = $this->countsLine($summary);

        $this->notifications->addNotifications([[
            'userId' => $userId,
            'read' => '0',
            'type' => self::TYPE_SUMMARY,
            'module' => self::MODULE,
            'moduleId' => 0,
            'message' => sprintf($this->language->__('reminders.summary_inbox'), $counts),
            'datetime' => $nowUtc->format('Y-m-d H:i:s'),
            'url' => $url,
            'authorId' => 0,
        ]]);

        if ($user['notifications'] !== 0 && ($user['username'] ?? '') !== '') {
            try {
                $this->sendMail(
                    (string) $user['username'],
                    sprintf($this->language->__('reminders.email_subject'), $counts),
                    $this->summaryHtml($summary, $url)
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
     * The email body. Kept on single lines: the mailer turns newlines into line breaks.
     *
     * @param  array<string, mixed>  $summary
     */
    private function summaryHtml(array $summary, string $homeUrl): string
    {
        $html = '<p>'.$this->escape($this->language->__('reminders.email_intro')).'</p>';

        foreach ($summary['items'] as $item) {
            $link = MailerCore::notificationEmailUrl($item['url'], $item['kind'] === 'task' ? 'tickets' : 'reminders');
            $html .= '<div style="border-top: 1px solid #ddd; margin: 3px; padding: 6px 3px;">'
                .'<a href="'.$this->escape($link).'"><strong>'.$this->escape($item['title']).'</strong></a>'
                .'<br/><span style="color: #555;">'.$this->escape($this->whenText($item['kind'], $item['daysUntil']))
                .($item['projectName'] !== '' && $item['kind'] !== 'project' ? ' · '.$this->escape($item['projectName']) : '')
                .'</span></div>';
        }

        $extra = [];
        if ($summary['moreCount'] > 0) {
            $extra[] = sprintf($this->language->__('reminders.more'), $summary['moreCount']);
        }
        if ($summary['routine']['count'] > 0) {
            $extra[] = sprintf($this->language->__('reminders.routine'), $summary['routine']['count']);
        }
        if ($summary['overdueCount'] > 0) {
            $extra[] = sprintf($this->language->__('reminders.still_open'), $summary['overdueCount']);
        }
        foreach ($extra as $line) {
            $html .= '<p style="margin: 6px 3px; color: #666;">'.$this->escape($line).'</p>';
        }

        $html .= '<p><a href="'.$this->escape(MailerCore::notificationEmailUrl($homeUrl, 'reminders')).'">'.$this->escape($this->language->__('reminders.open_leantime')).'</a></p>';

        return $html;
    }

    private function sendMail(string $to, string $subject, string $html): void
    {
        $mailer = app()->make(MailerCore::class);
        $mailer->setContext('reminders');
        $mailer->setSubject($subject);
        $mailer->setHtml($html);
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

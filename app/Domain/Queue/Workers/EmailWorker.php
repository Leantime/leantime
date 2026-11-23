<?php

namespace Leantime\Domain\Queue\Workers;

use Carbon\CarbonImmutable;
use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Core\Language;
use Leantime\Core\Mailer;
use Leantime\Domain\Notifications\Repositories\Notifications as NotificationsRepository;
use Leantime\Domain\Notifications\Services\FriendlyDate;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Queue\Repositories\Queue;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Users\Repositories\Users;

/**
 * Sends the email digest: everything queued for a person since their last email, in one mail.
 *
 * The digest has two parts. "New" holds what the person has not seen yet, with the full text and
 * link as before. "Already seen" holds what they already read in the inbox (web or app): an email
 * whose entity has a read inbox notification from when it was queued or later, and no unread one
 * since. One quiet line each, no link. A digest with nothing new is not sent at all; the seen
 * rows wait for the next digest that has news, and are dropped after {@see MAX_AGE_DAYS} like any
 * other row. Rows queued without an entity (module / moduleId) are always new.
 *
 * New items are grouped by what they are about: a task's changes sit together under its name and
 * project, one short line each ("Marcel moved this to Done") with the time in the person's time
 * zone, a comment quoted under its line, and one button to open the task. Repeated lines from one
 * save show once. Rows queued with a summary (see Queue::queueMessageToUsers()) are grouped;
 * older rows and producers without one show their own subject and message.
 *
 * How often a person gets a digest is their own setting. The company setting is the shortest wait
 * between two digests for everyone: it fills in for a person who chose nothing, and it wins over a
 * shorter personal wait, never over a longer one. Admins can make email quieter, not louder.
 */
class EmailWorker
{
    /** Queued rows older than this are dropped unsent. */
    public const MAX_AGE_DAYS = 14;

    /** Wait between two digests when neither the person nor the company chose one. */
    public const DEFAULT_FREQUENCY_SECONDS = 900;

    /**
     * The inbox row of an event is written just before its queue row, by the same request, and
     * stamped separately, so it may carry a slightly earlier time. Inbox rows this many seconds
     * before the queue time still count as "since the email was queued". Kept short on purpose:
     * every second of grace is a second in which a read of an EARLIER notification about the same
     * entity could pass for a read of this one.
     */
    public const SEEN_GRACE_SECONDS = 10;

    public function __construct(
        private Users $userRepo,
        private Setting $settingsRepo,
        private Mailer $mailer,
        private Queue $queue,
        private Language $language,
        private NotificationsRepository $notifications,
        private ?ProjectRepository $projects = null,
        private ?TrustedAppUrl $trustedAppUrl = null,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $messages  zp_queue rows of the email channel.
     */
    public function handleQueue($messages): void
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $this->projectNames = [];
        $this->brandColor = null;

        $messagesByUser = [];
        $expiredHashes = [];
        foreach ($messages as $message) {
            $messageDate = $this->utc((string) $message['thedate']);

            // Don't send messages older than two weeks. Expired rows go regardless of whether
            // their owner gets a digest this run, so they never pile up behind seen ones.
            if ($messageDate === null || $messageDate->diff($now)->days > self::MAX_AGE_DAYS) {
                $expiredHashes[] = $message['msghash'];

                continue;
            }

            $messagesByUser[(int) $message['userId']][$message['msghash']] = [
                'thedate' => $message['thedate'],
                'subject' => $message['subject'],
                'message' => $message['message'],
                'projectId' => $message['projectId'],
                'module' => $message['module'] ?? null,
                'moduleId' => $message['moduleId'] ?? null,
                'summary' => self::summary($message['summary'] ?? null),
            ];
        }

        if ($expiredHashes !== []) {
            $this->queue->deleteMessageInQueue($expiredHashes);
        }

        foreach ($messagesByUser as $userId => $userMessages) {
            $theuser = $this->userRepo->getUser($userId);

            if ($theuser === false) {
                continue;
            }

            $recipient = $theuser['username'];

            $lastSentAt = (string) $this->settingsRepo->getSetting('usersettings.'.$theuser['id'].'.lastMessageDate');
            $lastMessageDate = strtotime($lastSentAt);
            $timeSince = abs(time() - (int) $lastMessageDate);

            if ($timeSince < $this->frequencyFor((int) $theuser['id'])) {
                continue;
            }

            [$new, $seen] = $this->splitBySeen((int) $theuser['id'], $userMessages);

            // Nothing new: no email. The seen rows stay queued and ride along with the next
            // digest that has news, so the person still gets a complete record.
            if ($new === []) {
                continue;
            }

            $this->mailer->setContext('latest_updates');
            if (count($new) == 1) {
                $this->mailer->setSubject(current($new)['subject']);
            } else {
                $this->mailer->setSubject($this->language->__('email_notifications.latest_updates_subject'));
            }
            $this->mailer->setHeadline($this->greeting($theuser));
            $this->mailer->setFooter($this->footerHtml());
            $this->mailer->setHtml($this->doFormatMail($new, $seen, (int) $theuser['id'], $lastSentAt));
            $this->mailer->setText($this->doFormatText($new, $seen, $theuser, $lastSentAt));

            $this->mailer->sendMail([$recipient], 'Leantime System');

            // Delete the corresponding messages from the queue when the mail is sent
            // TODO here : only delete these if the send was successful
            $this->queue->deleteMessageInQueue(array_keys($userMessages));

            // Store the last time a mail was sent to $recipient email
            $this->settingsRepo->saveSetting('usersettings.'.$theuser['id'].'.lastMessageDate', date('Y-m-d H:i:s'));
        }
    }

    /**
     * The shortest wait, in seconds, between two digests for this person.
     *
     * The person's own choice, with the company value as the floor: it stands in when they chose
     * nothing and wins when theirs is shorter. Neither set means {@see DEFAULT_FREQUENCY_SECONDS}.
     */
    public function frequencyFor(int $userId): int
    {
        $own = (int) $this->settingsRepo->getSetting('usersettings.'.$userId.'.messageFrequency');
        $company = (int) $this->settingsRepo->getSetting('companysettings.messageFrequency');

        if ($own > 0 && $company > 0) {
            return max($own, $company);
        }

        if ($own > 0) {
            return $own;
        }

        if ($company > 0) {
            return $company;
        }

        return self::DEFAULT_FREQUENCY_SECONDS;
    }

    /**
     * Splits a person's queued rows into [new, seen], each keyed by msghash in queue order.
     *
     * A row is seen when an inbox notification about the same entity (module + moduleId) created
     * since the row was queued (less {@see SEEN_GRACE_SECONDS}) was read, and none created since
     * then is still unread: an earlier read never hides a later update. Rows without an entity
     * are new.
     *
     * @param  array<string, array<string, mixed>>  $userMessages
     * @return array{0: array<string, array<string, mixed>>, 1: array<string, array<string, mixed>>}
     */
    private function splitBySeen(int $userId, array $userMessages): array
    {
        $entities = [];
        foreach ($userMessages as $message) {
            if (! empty($message['module']) && $message['moduleId'] !== null) {
                $entities[] = ['module' => (string) $message['module'], 'moduleId' => (int) $message['moduleId']];
            }
        }

        $latest = $entities === [] ? [] : $this->notifications->latestByEntity($userId, $entities);

        $new = [];
        $seen = [];
        foreach ($userMessages as $hash => $message) {
            if ($this->wasSeen($message, $latest)) {
                $seen[$hash] = $message;
            } else {
                $new[$hash] = $message;
            }
        }

        return [$new, $seen];
    }

    /**
     * @param  array<string, mixed>  $message  A queued row.
     * @param  array<string, array{read: string|null, unread: string|null}>  $latest  From {@see NotificationsRepository::latestByEntity()}.
     */
    private function wasSeen(array $message, array $latest): bool
    {
        if (empty($message['module']) || $message['moduleId'] === null) {
            return false;
        }

        $key = $message['module'].':'.(int) $message['moduleId'];
        if (! isset($latest[$key]) || $latest[$key]['read'] === null) {
            return false;
        }

        $queuedAt = $this->utc((string) $message['thedate']);
        $readAt = $this->utc($latest[$key]['read']);
        if ($queuedAt === null || $readAt === null) {
            return false;
        }

        $since = $queuedAt->getTimestamp() - self::SEEN_GRACE_SECONDS;
        if ($readAt->getTimestamp() < $since) {
            return false;
        }

        // Something about this entity arrived since the email was queued and is still unread: the
        // read row is an earlier one, and this update has not been seen.
        $unreadAt = $latest[$key]['unread'] === null ? null : $this->utc($latest[$key]['unread']);

        return $unreadAt === null || $unreadAt->getTimestamp() < $since;
    }

    /** Parses a UTC `Y-m-d H:i:s` database timestamp; null when it isn't one. */
    private function utc(string $dateTime): ?\DateTimeImmutable
    {
        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateTime, new \DateTimeZone('UTC'));

        return $parsed === false ? null : $parsed;
    }

    /**
     * A queued row's summary, decoded; null when it has none or it is unusable.
     *
     * @return array{group: string, title: string, line: string, quote: string, detail: string, url: string, actor: string}|null
     */
    private static function summary(mixed $json): ?array
    {
        if (! is_string($json) || $json === '') {
            return null;
        }

        $data = json_decode($json, true);
        if (! is_array($data) || ! is_string($data['group'] ?? null) || ! is_string($data['title'] ?? null) || ! is_string($data['line'] ?? null)) {
            return null;
        }
        if ($data['group'] === '' || $data['title'] === '' || $data['line'] === '') {
            return null;
        }

        return [
            'group' => $data['group'],
            'title' => $data['title'],
            'line' => $data['line'],
            'quote' => is_string($data['quote'] ?? null) ? $data['quote'] : '',
            'detail' => is_string($data['detail'] ?? null) ? $data['detail'] : '',
            'url' => is_string($data['url'] ?? null) ? $data['url'] : '',
            // Who did it, so the digest can set the name apart and join a comment with the
            // change that came with it. Rows queued before it existed have none.
            'actor' => is_string($data['actor'] ?? null) ? $data['actor'] : '',
        ];
    }

    /**
     * The digest body: a headline and what it covers, what's new grouped by item, then, when there
     * are any, the seen items as one line each, and one button into Leantime.
     *
     * @param  array<string, array<string, mixed>>  $new
     * @param  array<string, array<string, mixed>>  $seen
     */
    private function doFormatMail(array $new, array $seen, int $userId = 0, string $lastSentAt = ''): string
    {
        $timezone = $this->timezoneFor($userId);
        $locale = $this->localeFor($userId);
        $now = CarbonImmutable::now($timezone);

        $outputHTML = '<h1 style="margin: 0 0 4px; font-size: 22px; line-height: 1.3; font-weight: 700; color: '.self::TEXT.';">'
            .$this->e($this->language->__('email_notifications.digest_title')).'</h1>';
        $outputHTML .= '<p style="margin: 0 0 16px; font-size: 15px; line-height: 1.5; color: '.self::MUTED.';">'
            .$this->e($this->sinceLine($lastSentAt, $timezone, $locale, $now)).'</p>';

        if ($seen !== []) {
            $outputHTML .= '<h3 style="margin: 12px 0 3px 0; font-size: 15px; color: '.self::TEXT.';">'.$this->e($this->language->__('email_notifications.digest_new_heading')).'</h3>';
        }

        foreach ($this->groups($new) as $group) {
            $outputHTML .= $this->formatGroup($group, $timezone, $locale, $now);
        }

        if ($seen !== []) {
            $outputHTML .= '<h3 style="margin: 18px 0 0 0; font-size: 15px; color: '.self::MUTED.';">'.$this->e($this->language->__('email_notifications.digest_seen_heading')).'</h3>';
            $outputHTML .= '<p style="margin: 0 0 3px 0; color: '.self::MUTED.'; font-size: 14px;">'.$this->e($this->language->__('email_notifications.digest_seen_hint')).'</p>';
            foreach ($seen as $chunk) {
                // One quiet line: when, and what it was about. Plain text; the body, with its
                // link, stays out.
                $what = $chunk['summary'] !== null ? $chunk['summary']['title'].' · '.$chunk['summary']['line'] : strip_tags((string) $chunk['subject']);
                $outputHTML .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top: 1px solid '.self::RULE.';"><tr>'
                    .'<td style="padding: 6px 0; color: '.self::MUTED.'; font-size: 14px;">'.$this->e($what).'</td>'
                    .'<td style="padding: 6px 0 6px 12px; color: '.self::MUTED.'; font-size: 14px; white-space: nowrap; text-align: right;">'.$this->e($this->when((string) $chunk['thedate'], $timezone, $locale, $now)).'</td>'
                    .'</tr></table>';
            }
        }

        $openUrl = $this->openUrl();
        if ($openUrl !== '') {
            $color = $this->e($this->accentColor());
            $outputHTML .= '<table role="presentation" cellpadding="0" cellspacing="0" style="margin-top: 24px;"><tr><td style="border-radius: 8px; background: '.$color.';">'
                .'<a href="'.$this->e($openUrl).'" style="display: inline-block; padding: 13px 22px; font-size: 16px; line-height: 20px; font-weight: 600; color: #ffffff; text-decoration: none; border-radius: 8px;">'
                .$this->e($this->language->__('email_notifications.digest_open_leantime')).'</a>'
                .'</td></tr></table>';
        }

        return $outputHTML;
    }

    /**
     * The plain-text part: the same content and grouping as the HTML, without the initials.
     *
     * @param  array<string, array<string, mixed>>  $new
     * @param  array<string, array<string, mixed>>  $seen
     * @param  array<string, mixed>  $user
     */
    private function doFormatText(array $new, array $seen, array $user, string $lastSentAt = ''): string
    {
        $userId = (int) ($user['id'] ?? 0);
        $timezone = $this->timezoneFor($userId);
        $locale = $this->localeFor($userId);
        $now = CarbonImmutable::now($timezone);

        $lines = [
            $this->greetingText($user),
            '',
            $this->language->__('email_notifications.digest_title'),
            $this->sinceLine($lastSentAt, $timezone, $locale, $now),
        ];

        foreach ($this->groups($new) as $group) {
            $lines[] = '';
            $first = $group['rows'][0];
            if ($first['summary'] === null) {
                $lines[] = strip_tags((string) $first['subject']).' · '.$this->when((string) $first['thedate'], $timezone, $locale, $now);
                $lines[] = trim(html_entity_decode(strip_tags((string) $first['message']), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

                continue;
            }

            $project = $this->projectName((int) $first['projectId']);
            if ($project !== '') {
                $lines[] = mb_strtoupper($project);
            }
            $newest = $group['rows'][count($group['rows']) - 1];
            $lines[] = $newest['summary']['title'];
            $url = $this->groupUrl($group);
            if ($url !== '') {
                $lines[] = $url;
            }
            foreach ($this->events($group['rows']) as $event) {
                $lines[] = '- '.$event['line'].' · '.$this->when($event['thedate'], $timezone, $locale, $now);
                if ($event['detail'] !== '') {
                    $lines[] = '  '.$event['detail'];
                }
                if ($event['quote'] !== '') {
                    $lines[] = '  "'.$event['quote'].'"';
                }
            }
        }

        if ($seen !== []) {
            $lines[] = '';
            $lines[] = $this->language->__('email_notifications.digest_seen_heading');
            foreach ($seen as $chunk) {
                $what = $chunk['summary'] !== null ? $chunk['summary']['title'].' · '.$chunk['summary']['line'] : strip_tags((string) $chunk['subject']);
                $lines[] = '- '.$what.' · '.$this->when((string) $chunk['thedate'], $timezone, $locale, $now);
            }
        }

        $openUrl = $this->openUrl();
        if ($openUrl !== '') {
            $lines[] = '';
            $lines[] = $this->language->__('email_notifications.digest_open_leantime').': '.$openUrl;
        }
        $settingsUrl = $this->settingsUrl();
        if ($settingsUrl !== '') {
            $lines[] = '';
            $lines[] = sprintf($this->language->__('email_notifications.digest_footer_text'), $settingsUrl);
        }

        return implode("\n", $lines)."\n";
    }

    /** "Hi Gloria," with the person's first name, else the standard greeting. Escaped HTML. */
    private function greeting(array $user): string
    {
        return $this->e($this->greetingText($user));
    }

    /** @param  array<string, mixed>  $user */
    private function greetingText(array $user): string
    {
        $first = trim(strip_tags((string) ($user['firstname'] ?? '')));

        return $first !== ''
            ? sprintf($this->language->__('email_notifications.digest_greeting'), $first)
            : trim($this->language->__('email_notifications.hi'));
    }

    /** Under the email: when it comes, and where to change it. Null when no link can be built. */
    private function footerHtml(): ?string
    {
        $settingsUrl = $this->settingsUrl();

        return $settingsUrl === '' ? null : sprintf($this->language->__('email_notifications.digest_footer'), $this->e($settingsUrl));
    }

    /** "What changed since your last email (Saturday 9:00 PM)." or, without one, the general intro. */
    private function sinceLine(string $lastSentAt, \DateTimeZone $timezone, string $locale, CarbonImmutable $now): string
    {
        $when = $lastSentAt !== '' ? $this->when($lastSentAt, $timezone, $locale, $now) : '';

        return $when !== ''
            ? sprintf($this->language->__('email_notifications.digest_since'), $when)
            : $this->language->__('email_notifications.digest_intro');
    }

    /**
     * Two copies of one line closer together than this came from the same save (one save can
     * raise the same notification twice); further apart, they are separate changes.
     */
    private const SAME_SAVE_SECONDS = 120;

    /**
     * A comment and a change by the same person this close together read as one event: "Marcel
     * commented and moved this to Done".
     */
    private const MERGE_SECONDS = 300;

    /** Text colours. Each is at least 4.5:1 on white and on the light grey of a quoted comment. */
    private const TEXT = '#1f2328';

    private const MUTED = '#4b5563';

    private const RULE = '#e3e6ea';

    /** Links and the button fall back to this when the company colour is too light to read. */
    private const ACCENT_FALLBACK = '#00607d';

    /**
     * Project names looked up during one handleQueue() run, by project id.
     *
     * @var array<int, string>
     */
    private array $projectNames = [];

    /** The company colour, looked up once per handleQueue() run. */
    private ?string $brandColor = null;

    /**
     * New rows grouped by the item they are about, newest change first; within an item, oldest
     * first, a line repeated by the same save shown once. The same line again later is a new
     * change (Done, In Progress, then Done again) and stays. Rows without a summary stay on
     * their own.
     *
     * @param  array<string, array<string, mixed>>  $rows
     * @return array<int, array{rows: array<int, array<string, mixed>>, latest: string}>
     */
    private function groups(array $rows): array
    {
        $groups = [];
        foreach ($rows as $hash => $row) {
            $key = $row['summary'] !== null ? 'g:'.$row['projectId'].':'.$row['summary']['group'] : 'r:'.$hash;
            $groups[$key]['rows'][] = $row;
            $groups[$key]['latest'] = max($groups[$key]['latest'] ?? '', (string) $row['thedate']);
        }

        foreach ($groups as &$group) {
            usort($group['rows'], fn (array $a, array $b) => strcmp((string) $a['thedate'], (string) $b['thedate']));
            $lastKept = null;
            $group['rows'] = array_values(array_filter($group['rows'], function (array $row) use (&$lastKept) {
                if ($row['summary'] === null) {
                    return true;
                }
                // The title is part of it: an update and a rename in the same two minutes read the
                // same line but carry different names, and the newest name heads the item.
                $fingerprint = $row['summary']['title']."\n".$row['summary']['line']."\n".$row['summary']['quote']."\n".$row['summary']['detail'];
                $at = $this->utc((string) $row['thedate'])?->getTimestamp() ?? 0;
                if ($lastKept !== null && $lastKept['fingerprint'] === $fingerprint && abs($at - $lastKept['at']) <= self::SAME_SAVE_SECONDS) {
                    return false;
                }
                $lastKept = ['fingerprint' => $fingerprint, 'at' => $at];

                return true;
            }));
        }
        unset($group);

        uasort($groups, fn (array $a, array $b) => strcmp($b['latest'], $a['latest']));

        return array_values($groups);
    }

    /**
     * One item: its project, its name as a link, and a line per change (who in bold, what, when),
     * with a change's details and a quoted comment under its line. A comment and a change by the
     * same person moments apart are one line. A row without a summary shows its subject and
     * message as before.
     *
     * @param  array{rows: array<int, array<string, mixed>>, latest: string}  $group
     */
    private function formatGroup(array $group, \DateTimeZone $timezone, string $locale, CarbonImmutable $now): string
    {
        $first = $group['rows'][0];
        $html = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top: 1px solid '.self::RULE.';"><tr><td style="padding: 20px 0 14px;">';

        if ($first['summary'] === null) {
            $html .= '<div style="font-size: 16px; font-weight: 600; line-height: 1.4; color: '.self::TEXT.';">'.$this->e(strip_tags((string) $first['subject']))
                .' <span style="font-size: 15px; font-weight: 400; color: '.self::MUTED.';">&middot; '.$this->e($this->when((string) $first['thedate'], $timezone, $locale, $now)).'</span></div>';
            $html .= '<div style="margin-top: 6px; font-size: 15px; line-height: 1.5; color: '.self::TEXT.';">'.$first['message'].'</div>';

            return $html.'</td></tr></table>';
        }

        $project = $this->projectName((int) $first['projectId']);
        if ($project !== '') {
            $html .= '<div style="font-size: 13px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: '.self::MUTED.';">'.$this->e($project).'</div>';
        }

        // The item's name as of its newest change (it may have been renamed), as the link to it;
        // the lines below stay oldest first.
        $newest = $group['rows'][count($group['rows']) - 1];
        $title = $this->e($newest['summary']['title']);
        $url = $this->groupUrl($group);
        $titleStyle = 'font-size: 18px; font-weight: 700; line-height: 1.35; overflow-wrap: anywhere; word-break: break-word;';
        $html .= '<div style="margin: 2px 0 10px;">'
            .($url !== ''
                ? '<a href="'.$this->e($url).'" style="'.$titleStyle.' color: '.$this->e($this->accentColor()).'; text-decoration: underline;">'.$title.'</a>'
                : '<span style="'.$titleStyle.' color: '.self::TEXT.';">'.$title.'</span>')
            .'</div>';

        $html .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">';
        foreach ($this->events($group['rows']) as $event) {
            $html .= '<tr>'
                // The initial is decoration: the name is in the line next to it.
                .'<td width="38" aria-hidden="true" style="width: 38px; padding: 6px 0; vertical-align: top;">'
                .'<div style="width: 28px; height: 28px; border-radius: 14px; background: #dbe7ee; color: '.self::TEXT.'; font-size: 13px; font-weight: 700; line-height: 28px; text-align: center;">'.$this->e($event['initial']).'</div>'
                .'</td>'
                .'<td style="padding: 9px 0 6px; vertical-align: top; overflow-wrap: anywhere; word-break: break-word;">'
                .'<div style="font-size: 15px; line-height: 1.45; color: '.self::TEXT.';">'.$this->eventLineHtml($event)
                .' <span style="color: '.self::MUTED.';">&middot; '.$this->e($this->when($event['thedate'], $timezone, $locale, $now)).'</span></div>';
            if ($event['detail'] !== '') {
                $html .= '<div style="font-size: 15px; line-height: 1.5; color: '.self::MUTED.';">'.$this->e($event['detail']).'</div>';
            }
            if ($event['quote'] !== '') {
                $html .= '<div style="margin: 6px 0 2px; padding: 10px 12px; background: #f1f3f5; border-radius: 8px; font-size: 15px; line-height: 1.5; color: '.self::TEXT.'; overflow-wrap: anywhere; word-break: break-word;">'.$this->e($event['quote']).'</div>';
            }
            $html .= '</td></tr>';
        }
        $html .= '</table>';

        return $html.'</td></tr></table>';
    }

    /**
     * An item's rows as events, oldest first: a comment and a change by the same person within
     * {@see MERGE_SECONDS} of each other become one ("commented and moved this to Done"), with the
     * comment quoted under it.
     *
     * @param  array<int, array<string, mixed>>  $rows  Rows with a summary, oldest first.
     * @return array<int, array{actor: string, line: string, detail: string, quote: string, thedate: string, initial: string}>
     */
    private function events(array $rows): array
    {
        $events = [];
        $count = count($rows);
        for ($i = 0; $i < $count; $i++) {
            $row = $rows[$i];
            $next = $rows[$i + 1] ?? null;
            if ($next !== null && $this->joins($row, $next)) {
                $actor = $row['summary']['actor'];
                $events[] = $this->event(
                    $actor,
                    $actor.' '.sprintf(
                        $this->language->__('email_notifications.digest_and'),
                        $this->afterActor($row['summary']['line'], $actor),
                        $this->afterActor($next['summary']['line'], $actor)
                    ),
                    trim($row['summary']['detail'].' '.$next['summary']['detail']),
                    $row['summary']['quote'] !== '' ? $row['summary']['quote'] : $next['summary']['quote'],
                    (string) $next['thedate'],
                );
                $i++;

                continue;
            }

            $events[] = $this->event(
                $row['summary']['actor'],
                $row['summary']['line'],
                $row['summary']['detail'],
                $row['summary']['quote'],
                (string) $row['thedate'],
            );
        }

        return $events;
    }

    /** Whether two neighbouring rows are a comment and a change by the same person, moments apart. */
    private function joins(array $a, array $b): bool
    {
        $actor = $a['summary']['actor'];
        if ($actor === '' || $actor !== $b['summary']['actor']) {
            return false;
        }
        // Only when both lines start with the name, so joining them reads correctly.
        if (! str_starts_with($a['summary']['line'], $actor.' ') || ! str_starts_with($b['summary']['line'], $actor.' ')) {
            return false;
        }
        // Exactly one of the two is a comment.
        if (($a['summary']['quote'] !== '') === ($b['summary']['quote'] !== '')) {
            return false;
        }
        $at = $this->utc((string) $a['thedate'])?->getTimestamp();
        $bt = $this->utc((string) $b['thedate'])?->getTimestamp();

        return $at !== null && $bt !== null && abs($bt - $at) <= self::MERGE_SECONDS;
    }

    /** @return array{actor: string, line: string, detail: string, quote: string, thedate: string, initial: string} */
    private function event(string $actor, string $line, string $detail, string $quote, string $thedate): array
    {
        $source = $actor !== '' ? $actor : $line;

        return [
            'actor' => $actor,
            'line' => $line,
            'detail' => $detail,
            'quote' => $quote,
            'thedate' => $thedate,
            'initial' => mb_strtoupper(mb_substr(trim($source), 0, 1)),
        ];
    }

    /** The line with the person's name in bold when it starts with it. Escaped HTML. */
    private function eventLineHtml(array $event): string
    {
        $actor = $event['actor'];
        if ($actor !== '' && str_starts_with($event['line'], $actor.' ')) {
            return '<strong style="font-weight: 600;">'.$this->e($actor).'</strong>'.$this->e(substr($event['line'], strlen($actor)));
        }

        return $this->e($event['line']);
    }

    private function afterActor(string $line, string $actor): string
    {
        return trim(substr($line, strlen($actor)));
    }

    /** The newest link any of an item's rows carries; '' when none does. */
    private function groupUrl(array $group): string
    {
        $url = '';
        foreach ($group['rows'] as $row) {
            if ($row['summary'] !== null && $row['summary']['url'] !== '') {
                $url = $row['summary']['url'];
            }
        }

        return $url;
    }

    /**
     * The company colour for links and the button, unless white text on it (or it on white) would
     * be under 4.5:1; then a dark teal that passes.
     */
    private function accentColor(): string
    {
        $color = $this->brandColor();

        return self::contrastWithWhite($color) >= 4.5 ? $color : self::ACCENT_FALLBACK;
    }

    /** WCAG contrast ratio between a #rgb / #rrggbb colour and white. */
    private static function contrastWithWhite(string $hex): float
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (strlen($hex) < 6) {
            return 0.0;
        }
        $channel = static function (string $pair): float {
            $c = hexdec($pair) / 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        };
        $luminance = 0.2126 * $channel(substr($hex, 0, 2)) + 0.7152 * $channel(substr($hex, 2, 2)) + 0.0722 * $channel(substr($hex, 4, 2));

        return 1.05 / ($luminance + 0.05);
    }

    /** The app's home, for the one button; '' when no trusted URL is known. */
    private function openUrl(): string
    {
        $base = $this->appUrl();

        return $base === '' ? '' : Mailer::notificationEmailUrl($base.'/dashboard/home', 'latest_updates');
    }

    /** The person's notification settings; '' when no trusted URL is known. */
    private function settingsUrl(): string
    {
        $base = $this->appUrl();

        return $base === '' ? '' : Mailer::notificationEmailUrl($base.'/users/editOwn#notifications', 'latest_updates');
    }

    private function appUrl(): string
    {
        try {
            return rtrim(($this->trustedAppUrl ?? app(TrustedAppUrl::class))->forLinks(), '/');
        } catch (\Throwable) {
            return '';
        }
    }

    /** The company's primary colour, as the email around the digest uses it (Mailer). Once per run. */
    private function brandColor(): string
    {
        if ($this->brandColor !== null) {
            return $this->brandColor;
        }

        $color = $this->settingsRepo->getSetting('companysettings.primarycolor');

        return $this->brandColor = is_string($color) && preg_match('/^#[0-9a-fA-F]{3,8}$/', $color) === 1 ? $color : '#006c9e';
    }

    /** The person's time zone, else the instance default, else UTC. */
    private function timezoneFor(int $userId): \DateTimeZone
    {
        $candidates = [
            $userId > 0 ? $this->settingsRepo->getSetting('usersettings.'.$userId.'.timezone') : null,
            $this->defaultTimezone(),
        ];
        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                try {
                    return new \DateTimeZone($candidate);
                } catch (\Exception) {
                    continue;
                }
            }
        }

        return new \DateTimeZone('UTC');
    }

    private function defaultTimezone(): ?string
    {
        try {
            return (string) app(\Leantime\Core\Configuration\Environment::class)->defaultTimezone;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * A UTC database time as the person would say it, in their time zone: "4:02 PM" today,
     * "Yesterday 4:02 PM", "Saturday 9:00 PM" within the week, else "Fri, Nov 14, 4:02 PM".
     */
    private function when(string $utc, \DateTimeZone $timezone, string $locale, CarbonImmutable $now): string
    {
        $at = $this->utc($utc);
        if ($at === null) {
            return '';
        }

        try {
            return FriendlyDate::when(
                CarbonImmutable::instance($at)->setTimezone($timezone),
                $now,
                $locale,
                $this->language->__('email_notifications.digest_yesterday')
            );
        } catch (\Throwable) {
            return $at->setTimezone($timezone)->format('M j, g:i A');
        }
    }

    /** The person's language for day names, else the instance default, else English. */
    private function localeFor(int $userId): string
    {
        $candidates = [
            $userId > 0 ? $this->settingsRepo->getSetting('usersettings.'.$userId.'.language') : null,
            $this->defaultLanguage(),
        ];
        foreach ($candidates as $candidate) {
            if (is_string($candidate) && preg_match('/^[A-Za-z]{2,3}(-[A-Za-z0-9]{2,8})*$/', $candidate) === 1) {
                return $candidate;
            }
        }

        return 'en-US';
    }

    private function defaultLanguage(): ?string
    {
        try {
            return (string) app(\Leantime\Core\Configuration\Environment::class)->language;
        } catch (\Throwable) {
            return null;
        }
    }

    private function projectName(int $projectId): string
    {
        if ($projectId <= 0) {
            return '';
        }

        // Once per project per run: a digest repeats the same few projects many times over.
        if (array_key_exists($projectId, $this->projectNames)) {
            return $this->projectNames[$projectId];
        }

        try {
            $project = ($this->projects ?? app(ProjectRepository::class))->getProject($projectId);
        } catch (\Throwable) {
            return '';
        }

        return $this->projectNames[$projectId] = is_array($project) ? trim(strip_tags((string) ($project['name'] ?? ''))) : '';
    }

    private function e(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

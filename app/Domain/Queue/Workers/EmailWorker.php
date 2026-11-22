<?php

namespace Leantime\Domain\Queue\Workers;

use Leantime\Core\Language;
use Leantime\Core\Mailer;
use Leantime\Domain\Notifications\Repositories\Notifications as NotificationsRepository;
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

            $lastMessageDate = strtotime((string) $this->settingsRepo->getSetting('usersettings.'.$theuser['id'].'.lastMessageDate'));
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
            $this->mailer->setHtml($this->doFormatMail($new, $seen, (int) $theuser['id']));

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
     * @return array{group: string, title: string, line: string, quote: string, detail: string, url: string}|null
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
        ];
    }

    /**
     * The digest body: what's new, grouped by item, then, when there are any, the seen items
     * as one line each.
     *
     * @param  array<string, array<string, mixed>>  $new
     * @param  array<string, array<string, mixed>>  $seen
     */
    private function doFormatMail(array $new, array $seen, int $userId = 0): string
    {
        $timezone = $this->timezoneFor($userId);

        $outputHTML = '<h1 style="margin: 0 0 4px; font-size: 22px; line-height: 1.3; font-weight: 700; color: #1f2933;">'
            .$this->e($this->language->__('email_notifications.digest_title')).'</h1>';
        $outputHTML .= '<p style="margin: 0 0 16px; font-size: 14px; line-height: 1.5; color: #52606d;">'
            .$this->e($this->language->__('email_notifications.digest_intro')).'</p>';

        if ($seen !== []) {
            $outputHTML .= '<h3 style="margin: 12px 0 3px 0; font-size: 14px;">'.$this->language->__('email_notifications.digest_new_heading').'</h3>';
        }

        foreach ($this->groups($new) as $group) {
            $outputHTML .= $this->formatGroup($group, $timezone);
        }

        if ($seen !== []) {
            $outputHTML .= '<h3 style="margin: 18px 0 0 0; font-size: 14px; color: #52606d;">'.$this->language->__('email_notifications.digest_seen_heading').'</h3>';
            $outputHTML .= '<p style="margin: 0 0 3px 0; color: #52606d; font-size: 12px;">'.$this->language->__('email_notifications.digest_seen_hint').'</p>';
            foreach ($seen as $chunk) {
                // One quiet line: when, and what it was about. Plain text; the body, with its
                // link, stays out.
                $what = $chunk['summary'] !== null ? $chunk['summary']['title'].' · '.$chunk['summary']['line'] : strip_tags((string) $chunk['subject']);
                $outputHTML .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top: 1px solid #e4e7eb;"><tr>'
                    .'<td style="padding: 6px 0; color: #52606d; font-size: 12px;">'.$this->e($what).'</td>'
                    .'<td style="padding: 6px 0 6px 12px; color: #52606d; font-size: 12px; white-space: nowrap; text-align: right;">'.$this->e($this->localTime((string) $chunk['thedate'], $timezone)).'</td>'
                    .'</tr></table>';
            }
        }

        return $outputHTML;
    }

    /**
     * Two copies of one line closer together than this came from the same save (one save can
     * raise the same notification twice); further apart, they are separate changes.
     */
    private const SAME_SAVE_SECONDS = 120;

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
     * One item: its project and name, a line per change with its time, a quoted comment under
     * its line, and one button. A row without a summary shows its subject and message as before.
     *
     * @param  array{rows: array<int, array<string, mixed>>, latest: string}  $group
     */
    private function formatGroup(array $group, \DateTimeZone $timezone): string
    {
        $first = $group['rows'][0];
        $html = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top: 1px solid #e4e7eb;"><tr><td style="padding: 18px 0;">';

        if ($first['summary'] === null) {
            $html .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>'
                .'<td style="font-size: 15px; font-weight: 600; line-height: 1.4; color: #1f2933;">'.$this->e(strip_tags((string) $first['subject'])).'</td>'
                .'<td style="padding-left: 12px; font-size: 13px; color: #52606d; white-space: nowrap; text-align: right; vertical-align: top;">'.$this->e($this->localTime((string) $first['thedate'], $timezone)).'</td>'
                .'</tr></table>';
            $html .= '<div style="margin-top: 6px; font-size: 15px; line-height: 1.5; color: #1f2933;">'.$first['message'].'</div>';

            return $html.'</td></tr></table>';
        }

        $project = $this->projectName((int) $first['projectId']);
        if ($project !== '') {
            $html .= '<div style="font-size: 12px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; color: #52606d;">'.$this->e($project).'</div>';
        }
        // The item's name as of its newest change (it may have been renamed); the lines below
        // stay oldest first.
        $newest = $group['rows'][count($group['rows']) - 1];
        $html .= '<div style="margin: 2px 0 10px; font-size: 17px; font-weight: 600; line-height: 1.35; color: #1f2933;">'.$this->e($newest['summary']['title']).'</div>';

        // One row per event: a bullet, the line with its details indented under it (a due date,
        // a quoted comment), and the time. The details belong to their line, not the next one.
        $url = '';
        $html .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">';
        foreach ($group['rows'] as $row) {
            $summary = $row['summary'];
            $html .= '<tr>'
                .'<td width="16" style="width: 16px; padding: 4px 0; font-size: 15px; line-height: 1.5; color: #9aa5b1; vertical-align: top;">&bull;</td>'
                .'<td style="padding: 4px 0; vertical-align: top;">'
                .'<div style="font-size: 15px; line-height: 1.5; color: #1f2933;">'.$this->e($summary['line']).'</div>';
            if ($summary['detail'] !== '') {
                $html .= '<div style="font-size: 14px; line-height: 1.5; color: #52606d;">'.$this->e($summary['detail']).'</div>';
            }
            if ($summary['quote'] !== '') {
                $html .= '<div style="margin: 4px 0 2px; padding: 10px 14px; background: #f5f7fa; border-radius: 8px; font-size: 15px; line-height: 1.5; color: #1f2933;">'.$this->e($summary['quote']).'</div>';
            }
            $html .= '</td>'
                .'<td style="padding: 4px 0 4px 12px; font-size: 13px; line-height: 1.5; color: #52606d; white-space: nowrap; text-align: right; vertical-align: top;">'.$this->e($this->localTime((string) $row['thedate'], $timezone)).'</td>'
                .'</tr>';
            if ($summary['url'] !== '') {
                $url = $summary['url'];
            }
        }
        $html .= '</table>';

        if ($url !== '') {
            $label = str_starts_with($first['summary']['group'], 'tickets:')
                ? $this->language->__('email_notifications.digest_open_task')
                : $this->language->__('email_notifications.digest_open');
            $color = $this->e($this->brandColor());
            $html .= '<table role="presentation" cellpadding="0" cellspacing="0" style="margin-top: 12px;"><tr><td style="border-radius: 8px; background: '.$color.';">'
                .'<a href="'.$this->e($url).'" style="display: inline-block; padding: 11px 18px; font-size: 14px; font-weight: 600; color: #ffffff; text-decoration: none; border-radius: 8px;">'.$this->e($label).'</a>'
                .'</td></tr></table>';
        }

        return $html.'</td></tr></table>';
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

    /** "12:09 PM" today in the person's time zone, "Nov 16, 4:02 PM" on another day. */
    private function localTime(string $utc, \DateTimeZone $timezone): string
    {
        $at = $this->utc($utc);
        if ($at === null) {
            return '';
        }

        $local = $at->setTimezone($timezone);
        $today = (new \DateTimeImmutable('now', $timezone))->format('Y-m-d');

        return $local->format('Y-m-d') === $today ? $local->format('g:i A') : $local->format('M j, g:i A');
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

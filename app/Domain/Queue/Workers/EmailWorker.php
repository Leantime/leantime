<?php

namespace Leantime\Domain\Queue\Workers;

use Leantime\Core\Language;
use Leantime\Core\Mailer;
use Leantime\Domain\Notifications\Repositories\Notifications as NotificationsRepository;
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
        private NotificationsRepository $notifications
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $messages  zp_queue rows of the email channel.
     */
    public function handleQueue($messages): void
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

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
            $this->mailer->setHtml($this->doFormatMail($new, $seen));

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
     * The digest body: the new items in full, then, when there are any, the seen items as one
     * line each.
     *
     * @param  array<string, array<string, mixed>>  $new
     * @param  array<string, array<string, mixed>>  $seen
     */
    private function doFormatMail(array $new, array $seen): string
    {
        $outputHTML = $this->language->__('text.here_are_news')."<br/>\n";

        if ($seen !== []) {
            $outputHTML .= '<h3 style="margin: 12px 0 3px 3px; font-size: 14px;">'.$this->language->__('email_notifications.digest_new_heading').'</h3>';
        }

        foreach ($new as $chunk) {
            $outputHTML .= '<div style="border-top: 1px solid #ddd; margin: 3px; padding: 3px;">';
            $outputHTML .= '<div style="margin: 0px; padding: 0px; float : right">'.$chunk['thedate'].'</div>';
            $outputHTML .= '<div><p><em>'.$chunk['subject'].'</em></p>';
            $outputHTML .= $chunk['message'].'</div>';
            $outputHTML .= '</div>';
        }

        if ($seen !== []) {
            $outputHTML .= '<h3 style="margin: 18px 0 0 3px; font-size: 14px; color: #666;">'.$this->language->__('email_notifications.digest_seen_heading').'</h3>';
            $outputHTML .= '<p style="margin: 0 3px 3px 3px; color: #888; font-size: 12px;">'.$this->language->__('email_notifications.digest_seen_hint').'</p>';
            foreach ($seen as $chunk) {
                // One quiet line: when, and what it was about. The subject is plain text; the
                // body, with its link, stays out.
                $outputHTML .= '<div style="border-top: 1px solid #eee; margin: 3px; padding: 3px; color: #888; font-size: 12px;">';
                $outputHTML .= '<span style="float: right;">'.$chunk['thedate'].'</span>';
                $outputHTML .= htmlspecialchars(strip_tags((string) $chunk['subject']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $outputHTML .= '</div>';
            }
        }

        return $outputHTML;
    }
}

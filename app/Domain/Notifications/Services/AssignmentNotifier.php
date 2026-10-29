<?php

namespace Leantime\Domain\Notifications\Services;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Mailer as MailerCore;
use Leantime\Core\Support\NameSanitizer;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Tickets\Events\TicketAssigned;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;

/**
 * "Assigned to you": tells the new assignee of a to-do, and nobody else.
 *
 * Fired from {@see TicketAssigned}, which Tickets dispatches on creation with an
 * assignee and whenever the assignee changes to a different user, from the web
 * and the mobile app alike. The first notification about the recipient rather
 * than the project: it goes to one person, so it skips the project fan-out
 * (relevance level, category switch, mute) that Projects::notifyProjectUsers
 * applies. Being assigned is a direct action, like an @mention.
 *
 * Channels and the switches they honour:
 *   - inbox (zp_notifications, type 'assigned'): always
 *   - email, through the digest queue: "Receive email notifications"
 *   - mobile push: the per-user push switch (usersettings.{id}.pushNotifications)
 *
 * Nothing is sent when someone assigns a to-do to themselves, when the assignee
 * is deactivated, or when the to-do can't be read back.
 */
class AssignmentNotifier
{
    public const TYPE = 'assigned';

    public function __construct(
        private TicketRepository $ticketRepository,
        private UserRepository $userRepository,
        private Notifications $notificationService,
        private QueueRepository $queueRepository,
        private SettingRepository $settingsRepository,
        private LanguageCore $language,
        private Push $push,
        private TrustedAppUrl $trustedAppUrl,
    ) {}

    public function notify(TicketAssigned $event): void
    {
        if ($event->assignedToSelf) {
            return;
        }

        $ticket = $this->ticketRepository->getTicket($event->ticketId);
        if ($ticket === false) {
            return;
        }

        $assignee = $this->userRepository->getUser($event->assigneeId);
        if ($assignee === false || strtolower((string) ($assignee['status'] ?? '')) !== 'a') {
            return;
        }

        $actorId = (int) session('userdata.id');
        $actorName = $this->actorName($actorId);
        $headline = trim(strip_tags((string) $ticket->headline));
        $projectId = (int) ($event->projectId ?? $ticket->projectId ?? 0);

        // The link is emailed and pushed: point it at the trusted app URL, not the request host.
        $url = $this->trustedAppUrl->rebase(BASE_URL.'/dashboard/home#/tickets/showTicket/'.$event->ticketId);

        $message = sprintf($this->language->__('text.x_assigned_you'), $actorName, $headline);

        // Inbox, always.
        $this->notificationService->addNotifications([[
            'userId' => $event->assigneeId,
            'read' => '0',
            'type' => self::TYPE,
            'module' => 'tickets',
            'moduleId' => $event->ticketId,
            'message' => $message,
            'datetime' => dtHelper()->dbNow()->formatDateTimeForDb(), // UTC; date() is the user's tz here (#3201)
            'url' => $url,
            'authorId' => $actorId,
        ]]);

        // Email, through the same digest queue as project notifications, when the assignee kept email on.
        if ((int) ($assignee['notifications'] ?? 0) !== 0) {
            $emailUrl = MailerCore::notificationEmailUrl($url, 'tickets');
            $this->queueRepository->queueMessageToUsers(
                [$event->assigneeId],
                $message.' <a href="'.$emailUrl.'">'.$this->language->__('text.click_here').'</a>',
                sprintf($this->language->__('email_notifications.assigned_subject'), $headline),
                $projectId
            );
        }

        // Push, when the assignee kept push on. A push outage never breaks the inbox or email above.
        if (! $this->pushEnabled($event->assigneeId)) {
            return;
        }

        try {
            $body = sprintf($this->language->__('notification.push_assigned_body'), $actorName);
            $due = $this->dueText($ticket->dateToFinish);
            if ($due !== '') {
                $body .= ' · '.$due;
            }

            $this->push->send([$event->assigneeId], $headline !== '' ? $headline : 'Leantime', $body, [
                'module' => 'tickets',
                'moduleId' => (string) $event->ticketId,
                'projectId' => $projectId > 0 ? (string) $projectId : '',
                'type' => self::TYPE,
                'threadId' => 'ticket-'.$event->ticketId,
                'url' => $url,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Assignment push failed: '.$e->getMessage());
        }
    }

    private function actorName(int $actorId): string
    {
        $actor = $actorId > 0 ? $this->userRepository->getUser($actorId) : false;
        $name = $actor === false ? '' : NameSanitizer::clean($actor['firstname'] ?? '');

        return $name !== '' ? $name : $this->language->__('label.someone');
    }

    /**
     * The per-user push switch: on unless stored as '0'. Reads the setting the
     * "separate push on/off" change introduces; nothing is stored until a user
     * switches push off, so this is on for everyone until then.
     */
    private function pushEnabled(int $userId): bool
    {
        $stored = $this->settingsRepository->getSetting('usersettings.'.$userId.'.pushNotifications');

        return $stored === false || $stored === null || $stored === '' || (string) $stored === '1';
    }

    /**
     * "Due <date>" in the acting user's date format, or '' when there is no due date.
     */
    private function dueText(mixed $dateToFinish): string
    {
        if (! is_string($dateToFinish) || $dateToFinish === '' || str_starts_with($dateToFinish, '0000-00-00')) {
            return '';
        }

        try {
            $date = dtHelper()->parseDbDateTime($dateToFinish)->formatDateForUser();
        } catch (\Throwable) {
            return '';
        }

        return sprintf($this->language->__('notification.push_due'), $date);
    }
}

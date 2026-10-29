<?php

namespace Leantime\Domain\Notifications\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Mailer as MailerCore;
use Leantime\Core\Support\NameSanitizer;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
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
 * is deactivated or has no access to the to-do's project (skipping the
 * preference filters never skips authorization), or when the to-do can't be
 * read back.
 *
 * The due date in the push is formatted for the assignee: their timezone,
 * date format and language, not the acting user's. A calendar-only due date
 * (stored as midnight, see Tickets::prepareTicketDates) is never shifted.
 */
class AssignmentNotifier
{
    public const TYPE = 'assigned';

    public function __construct(
        private TicketRepository $ticketRepository,
        private UserRepository $userRepository,
        private ProjectRepository $projectRepository,
        private Notifications $notificationService,
        private QueueRepository $queueRepository,
        private SettingRepository $settingsRepository,
        private LanguageCore $language,
        private Push $push,
        private TrustedAppUrl $trustedAppUrl,
        private Environment $config,
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

        // The assignee must be able to see the to-do: an assignment can name anyone, and the
        // headline and due date must not leak to someone outside the project.
        $projectId = (int) ($ticket->projectId ?: ($event->projectId ?? 0));
        if ($projectId <= 0 || ! $this->projectRepository->isUserAssignedToProject($event->assigneeId, $projectId)) {
            return;
        }

        $actorId = (int) session('userdata.id');
        $actorName = $this->actorName($actorId);
        $headline = trim(strip_tags((string) $ticket->headline));

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
            $due = $this->dueText($ticket->dateToFinish, $event->assigneeId);
            if ($due !== '') {
                $body .= ' · '.$due;
            }

            $this->push->send([$event->assigneeId], $headline !== '' ? $headline : 'Leantime', $body, [
                'module' => 'tickets',
                'moduleId' => (string) $event->ticketId,
                'projectId' => (string) $projectId,
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
     * "Due <date>" for the assignee, or '' when there is no due date.
     *
     * A due date saved without a time is stored as midnight (Y-m-d 00:00:00) and means that
     * calendar day everywhere, so it is formatted as-is. A timed deadline is stored in UTC and
     * shown in the assignee's timezone. Format and language are the assignee's settings, with
     * the same fallbacks DateTimeHelper uses for the session user.
     */
    private function dueText(mixed $dateToFinish, int $assigneeId): string
    {
        if (! is_string($dateToFinish) || $dateToFinish === '' || str_starts_with($dateToFinish, '0000-00-00')) {
            return '';
        }

        $keys = [
            'timezone' => 'usersettings.'.$assigneeId.'.timezone',
            'date_format' => 'usersettings.'.$assigneeId.'.date_format',
            'language' => 'usersettings.'.$assigneeId.'.language',
        ];
        $settings = $this->settingsRepository->getSettingsForKeys(array_values($keys));
        $setting = static fn (string $name): ?string => is_string($settings[$keys[$name]] ?? null) && $settings[$keys[$name]] !== '' ? $settings[$keys[$name]] : null;

        $timezone = $setting('timezone') ?? (string) $this->config->defaultTimezone;
        $languageCode = $setting('language') ?? (string) $this->config->language;
        if (preg_match('/^[A-Za-z]{2,3}(-[A-Za-z0-9]{2,8})?$/', $languageCode) !== 1) {
            $languageCode = 'en-US';
        }
        // Not Language::__('language.dateformat'): that returns the SESSION user's format when
        // one is set, i.e. the assigner's. The assignee's own language file decides instead.
        $format = $setting('date_format') ?? $this->defaultDateFormat($languageCode);
        $locale = str_replace('-', '_', $languageCode);

        try {
            if (preg_match('/^(\d{4}-\d{2}-\d{2}) 00:00:00$/', $dateToFinish, $m) === 1) {
                $date = CarbonImmutable::createFromFormat('!Y-m-d', $m[1], 'UTC');
            } else {
                $date = CarbonImmutable::parse($dateToFinish, 'UTC')->setTimezone($timezone);
            }
            $formatted = $date->locale($locale)->translatedFormat($format);
        } catch (\Throwable) {
            return '';
        }

        return sprintf($this->language->__('notification.push_due'), $formatted);
    }

    /** @var array<string, string> language code → its ini date format, per request */
    private static array $iniDateFormats = [];

    /**
     * The `language.dateformat` of a language's ini files (custom/Language overriding
     * app/Language), falling back to en-US and then to ISO. Reads the files directly so no
     * session setting can get in between; Language::__() would.
     */
    private function defaultDateFormat(string $languageCode): string
    {
        if (isset(self::$iniDateFormats[$languageCode])) {
            return self::$iniDateFormats[$languageCode];
        }

        $format = '';
        foreach ([APP_ROOT.'/app/Language/', APP_ROOT.'/custom/Language/'] as $folder) {
            $file = $folder.$languageCode.'.ini';
            if (! is_file($file)) {
                continue;
            }
            $ini = @parse_ini_file($file, false, INI_SCANNER_RAW);
            if (is_array($ini) && ! empty($ini['language.dateformat'])) {
                $format = (string) $ini['language.dateformat'];
            }
        }
        if ($format === '' && $languageCode !== 'en-US') {
            $format = $this->defaultDateFormat('en-US');
        }

        return self::$iniDateFormats[$languageCode] = $format !== '' ? $format : 'Y-m-d';
    }
}

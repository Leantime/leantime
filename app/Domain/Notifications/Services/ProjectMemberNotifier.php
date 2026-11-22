<?php

namespace Leantime\Domain\Notifications\Services;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Mailer as MailerCore;
use Leantime\Core\Support\NameSanitizer;
use Leantime\Domain\Projects\Events\ProjectMemberAdded;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;

/**
 * "Added you to a project": tells the person who was added to a project's team, and nobody else.
 *
 * Fired from {@see ProjectMemberAdded}, which Projects dispatches for a new membership only (the
 * Team tab, the user's project list, addUserToProject, a new project's team), never for members
 * who were already there. Like "assigned to you", it is about the recipient, so it skips the
 * project fan-out and its relevance filters.
 *
 * Channels and the switches they honour:
 *   - inbox (zp_notifications, type 'project_added'): always
 *   - email, through the digest queue: "Receive email notifications"
 *   - mobile push: the per-user push switch, at the workspace's push content level
 *
 * Nothing is sent when people add themselves, when nobody is signed in (setup scripts, a new
 * workspace's first project), when the person is deactivated or only invited, when the project
 * can't be read back, or when they aren't actually on its team (the membership wasn't saved).
 * Someone added to many projects at once (the user's project list) gets at most
 * MAX_PUSHES_PER_REQUEST pushes; the inbox and the digest still list every project.
 */
class ProjectMemberNotifier
{
    public const TYPE = 'project_added';

    private const MAX_PUSHES_PER_REQUEST = 3;

    /** @var array<int, int> pushes sent this request, by user id */
    private static array $pushed = [];

    public function __construct(
        private UserRepository $userRepository,
        private ProjectRepository $projectRepository,
        private Notifications $notificationService,
        private QueueRepository $queueRepository,
        private SettingRepository $settingsRepository,
        private LanguageCore $language,
        private Push $push,
        private TrustedAppUrl $trustedAppUrl,
    ) {}

    /**
     * Tell the added person on every channel they kept on. Sends nothing unless they are an
     * active user who is now actually on the project's team and someone else added them.
     */
    public function notify(ProjectMemberAdded $event): void
    {
        $actorId = (int) session('userdata.id');
        if ($actorId <= 0 || $actorId === $event->userId) {
            return;
        }

        $member = $this->userRepository->getUser($event->userId);
        if ($member === false || strtolower((string) ($member['status'] ?? '')) !== 'a') {
            return;
        }

        $project = $this->projectRepository->getProject($event->projectId);
        if (! is_array($project)) {
            return;
        }

        // Only someone actually on the team hears about it (and the project's name): a caller
        // that fired the event for a membership that wasn't saved tells nobody.
        if (! $this->projectRepository->isUserMemberOfProject($event->userId, $event->projectId)) {
            return;
        }

        $projectName = trim(strip_tags((string) ($project['name'] ?? '')));
        $actorName = $this->actorName($actorId);

        // The link is emailed and pushed: point it at the trusted app URL, not the request host.
        $url = $this->trustedAppUrl->rebase(BASE_URL.'/projects/changeCurrentProject/'.$event->projectId);
        $line = sprintf($this->language->__('notification.push_added_to_project_body'), $actorName);
        $message = sprintf($this->language->__('text.x_added_you_to_project'), $actorName, $projectName);

        // Inbox, always.
        $this->notificationService->addNotifications([[
            'userId' => $event->userId,
            'read' => '0',
            'type' => self::TYPE,
            'module' => 'projects',
            'moduleId' => $event->projectId,
            'message' => $message,
            'datetime' => dtHelper()->dbNow()->formatDateTimeForDb(),
            'url' => $url,
            'authorId' => $actorId,
        ]]);

        // Email, through the digest queue, when they kept email on.
        if ((int) ($member['notifications'] ?? 0) !== 0) {
            $emailUrl = MailerCore::notificationEmailUrl($url, 'projects');
            $this->queueRepository->queueMessageToUsers(
                [$event->userId],
                $message.' <a href="'.$emailUrl.'">'.$this->language->__('text.click_here').'</a>',
                sprintf($this->language->__('email_notifications.added_to_project_subject'), $projectName),
                $event->projectId,
                'projects',
                $event->projectId,
                [
                    'group' => 'projects:'.$event->projectId,
                    'title' => $projectName,
                    'line' => $line,
                    'url' => $emailUrl,
                ]
            );
        }

        if (! $this->pushEnabled($event->userId) || (self::$pushed[$event->userId] ?? 0) >= self::MAX_PUSHES_PER_REQUEST) {
            return;
        }

        try {
            $this->push->send([$event->userId], $projectName !== '' ? $projectName : 'Leantime', $line, [
                'module' => 'projects',
                'moduleId' => (string) $event->projectId,
                'projectId' => (string) $event->projectId,
                'type' => self::TYPE,
                'threadId' => 'project-'.$event->projectId,
                'url' => $url,
                'tier' => Push::TIER_DIRECT,
                'category' => Push::CATEGORY_NONE,
            ]);
            self::$pushed[$event->userId] = (self::$pushed[$event->userId] ?? 0) + 1;
        } catch (\Throwable $e) {
            Log::warning('Project member push failed: '.$e->getMessage());
        }
    }

    /** Test hook: forget the per-request push count. */
    public static function resetPushCount(): void
    {
        self::$pushed = [];
    }

    private function actorName(int $actorId): string
    {
        $actor = $this->userRepository->getUser($actorId);
        $name = $actor === false ? '' : NameSanitizer::clean($actor['firstname'] ?? '');

        return $name !== '' ? $name : $this->language->__('label.someone');
    }

    /** The per-user push switch: on unless stored as '0' (same rule as AssignmentNotifier). */
    private function pushEnabled(int $userId): bool
    {
        $stored = $this->settingsRepository->getSetting('usersettings.'.$userId.'.pushNotifications');

        return $stored === false || $stored === null || $stored === '' || (string) $stored === '1';
    }
}

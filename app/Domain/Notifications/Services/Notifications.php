<?php

namespace Leantime\Domain\Notifications\Services;

use DOMDocument;
use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Mailer as MailerCore;
use Leantime\Domain\Notifications\Repositories\DeviceTokens as DeviceTokensRepository;
use Leantime\Domain\Notifications\Repositories\Notifications as NotificationRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;

/**
 * @api
 */
class Notifications
{
    private DbCore $db;

    private NotificationRepository $notificationsRepo;

    private UserRepository $userRepository;

    private LanguageCore $language;

    private DeviceTokensRepository $deviceTokensRepo;

    /**
     * __construct - get database connection
     *
     *
     * @api
     */
    public function __construct(
        DbCore $db,
        NotificationRepository $notificationsRepo,
        UserRepository $userRepository,
        LanguageCore $language,
        DeviceTokensRepository $deviceTokensRepo
    ) {
        $this->db = $db;
        $this->notificationsRepo = $notificationsRepo;
        $this->userRepository = $userRepository;
        $this->language = $language;
        $this->deviceTokensRepo = $deviceTokensRepo;
    }

    /**
     * @api
     */
    /**
     * @api
     */
    public function getAllNotifications($userId, int $showNewOnly = 0, int $limitStart = 0, int $limitEnd = 100, array $filterOptions = []): false|array
    {

        return $this->notificationsRepo->getAllNotifications($userId, $showNewOnly, $limitStart, $limitEnd, $filterOptions);
    }

    /**
     * @api
     */
    public function addNotifications(array $notifications): ?bool
    {

        return $this->notificationsRepo->addNotifications($notifications);
    }

    /**
     * @api
     */
    /**
     * @api
     */
    public function markNotificationRead($id, $userId): bool
    {

        if ($id == 'all') {
            return $this->notificationsRepo->markAllNotificationRead($userId);
        } else {
            return $this->notificationsRepo->markNotificationRead($id);
        }
    }

    /**
     * Flip a previously-read notification back to unread. Powers the
     * swipe-to-mark-unread inbox gesture on mobile — symmetric to
     * markNotificationRead so users can re-surface something they
     * tapped open accidentally or want to come back to.
     *
     * Per the mobile-owns-explicit-RPC-params convention, userId is
     * passed by the client. Scoping isn't critical here (the id is
     * the primary key) but the param keeps audit logs consistent
     * with the rest of the Notifications RPC surface.
     *
     * @api
     */
    public function markNotificationUnread(int $id, int $userId): bool
    {
        if ($id <= 0) {
            return false;
        }

        return $this->notificationsRepo->markNotificationUnread($id);
    }

    /**
     * Unread notification count for the authenticated user. Mobile uses
     * this for the app-icon badge and the inbox tab unread dot. Cheap
     * because the (userId, read) composite index on zp_notifications
     * makes it a fast count.
     *
     * @api
     */
    public function getUnreadCount(): int
    {
        $userId = (int) session('userdata.id');
        if ($userId === 0) {
            return 0;
        }

        // Delegated to the Repository — Service stores the raw DbCore
        // (no query-builder helpers), Repository stores the resolved
        // Illuminate ConnectionInterface. Following the established
        // pattern that all SQL lives in the Repository layer.
        return $this->notificationsRepo->getUnreadCount($userId);
    }

    /**
     * Register a mobile device's Expo push token for the authenticated
     * user. Mobile calls this on every login (idempotent — backend
     * upserts on (userId, token)). Refreshes lastSeenAt + clears any
     * prior invalidatedAt.
     *
     * Per [[feedback-mobile-owns-explicit-rpc-params]] convention,
     * userId is resolved server-side from session — we don't accept it
     * from the client (a stolen bearer token shouldn't be able to
     * register push devices on someone else's account).
     *
     * @param  string  $token  Expo push token, format "ExponentPushToken[xxx]"
     * @param  string  $platform  'ios' or 'android'
     * @param  string|null  $deviceName  Optional human-readable device label
     *
     * @api
     */
    public function registerPushToken(string $token, string $platform, ?string $deviceName = null): bool
    {
        $userId = (int) session('userdata.id');
        if ($userId === 0) {
            return false;
        }

        // Light validation — Expo token format is documented; reject
        // obvious junk before storing.
        if (! str_starts_with($token, 'ExponentPushToken[') && ! str_starts_with($token, 'ExpoPushToken[')) {
            return false;
        }
        if (! in_array($platform, ['ios', 'android'], true)) {
            return false;
        }

        return $this->deviceTokensRepo->save($userId, $token, $platform, $deviceName);
    }

    /**
     * Unregister a token (called on mobile logout BEFORE credentials
     * clear, so we still have a valid bearer for the RPC). Soft-deletes
     * via invalidatedAt — audit trail preserved; prune job sweeps stale
     * rows periodically.
     *
     * Scoped to the authenticated user so a bearer token can only
     * invalidate its own devices.
     *
     * @api
     */
    public function unregisterPushToken(string $token): bool
    {
        $userId = (int) session('userdata.id');
        if ($userId === 0) {
            return false;
        }

        return $this->deviceTokensRepo->invalidateForUser($userId, $token);
    }

    /**
     * @throws BindingResolutionException
     *
     * @api
     */
    public function processMentions(string $content, string $module, int $moduleId, int $authorId, string $url): void
    {

        $dom = new DOMDocument;

        // Content may not be well formatted. Suppress warnings.
        @$dom->loadHTML($content);
        $links = $dom->getElementsByTagName('a');

        $author = $this->userRepository->getUser($authorId);
        if ($author === false) {
            return;
        }

        $authorName = htmlentities($author['firstname']) ?? $this->language->__('label.team_mate');

        for ($i = 0; $i < $links->count(); $i++) {
            $taggedUser = $links->item($i)->getAttribute('data-tagged-user-id');

            if ($taggedUser !== '' && is_numeric($taggedUser)) {
                // Check if user was mentioned before
                $userMentions = $this->getAllNotifications(
                    $taggedUser,
                    false,
                    0,
                    10,
                    ['type' => 'mention', 'module' => $module, 'moduleId' => $moduleId]
                );

                if ($userMentions === false || (is_array($userMentions) && count($userMentions) == 0)) {
                    $notification = [
                        'userId' => $taggedUser,
                        'read' => '0',
                        'type' => 'mention',
                        'module' => $module,
                        'moduleId' => $moduleId,
                        'message' => sprintf($this->language->__('text.x_mentioned_you'), $authorName),
                        'datetime' => date('Y-m-d H:i:s'),
                        'url' => $url,
                        'authorId' => $authorId,
                    ];

                    $this->addNotifications([$notification]);

                    // send email
                    $mailer = app()->make(MailerCore::class);
                    $mailer->setContext('notify_project_users');

                    $subject = sprintf($this->language->__('text.x_mentioned_you'), $authorName);
                    $mailer->setSubject($subject);

                    $emailMessage = $subject.' <a href="'.$url.'">'.$this->language->__('text.click_here').'</a>';
                    $mailer->setHtml($emailMessage);

                    $taggedUserObject = $this->userRepository->getUser($taggedUser);
                    if (isset($taggedUserObject['username'])) {
                        $mailer->sendMail([$taggedUserObject['username']], $authorName);
                    }
                }
            }
        }
    }
}

<?php

namespace Leantime\Domain\Notifications\Services;

use DOMDocument;
use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Mailer as MailerCore;
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
        LanguageCore $language
    ) {
        $this->db = $db;
        $this->notificationsRepo = $notificationsRepo;
        $this->userRepository = $userRepository;
        $this->language = $language;
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

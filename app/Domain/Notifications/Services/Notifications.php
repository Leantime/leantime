<?php

namespace Leantime\Domain\Notifications\Services {

    use DOMDocument;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Db\Db as DbCore;
    use Leantime\Core\Language as LanguageCore;
    use Leantime\Core\Mailer as MailerCore;
    use Leantime\Domain\Notifications\Repositories\Notifications as NotificationRepository;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;

    /**
     *
     *
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
         * @access public
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
         * @param $userId
         * @param $showNewOnly
         * @param $limitStart
         * @param $limitEnd
         * @param $filterOptions
         * @return array|false
         *
     * @api
     */
        /**
         * @param $userId
         * @param int    $showNewOnly
         * @param int    $limitStart
         * @param int    $limitEnd
         * @param array  $filterOptions
         * @return array|false
         *
     * @api
     */
        public function getAllNotifications($userId, int $showNewOnly = 0, int $limitStart = 0, int $limitEnd = 100, array $filterOptions = array()): false|array
        {

            return $this->notificationsRepo->getAllNotifications($userId, $showNewOnly, $limitStart, $limitEnd, $filterOptions);
        }


        /**
         * @param array $notifications
         * @return bool|null
         *
     * @api
     */
        public function addNotifications(array $notifications): ?bool
        {

            return $this->notificationsRepo->addNotifications($notifications);
        }

        /**
         * @param $id
         * @param $userId
         * @return bool
         *
     * @api
     */
        /**
         * @param $id
         * @param $userId
         * @return bool
         *
     * @api
     */
        public function markNotificationRead($id, $userId): bool
        {

            if ($id == "all") {
                return $this->notificationsRepo->markAllNotificationRead($userId);
            } else {
                return $this->notificationsRepo->markNotificationRead($id);
            }
        }

        /**
         * @param string $content
         * @param string $module
         * @param int    $moduleId
         * @param int    $authorId
         * @param string $url
         * @return void
         * @throws BindingResolutionException
         *
     * @api
     */
        public function processMentions(string $content, string $module, int $moduleId, int $authorId, string $url): void
        {

            $dom = new DOMDocument();

            //Content may not be well formatted. Suppress warnings.
            @$dom->loadHTML($content);
            $links = $dom->getElementsByTagName("a");

            $author = $this->userRepository->getUser($authorId);
            $authorName = $author['firstname'] ?? $this->language->__('label.team_mate');

            for ($i = 0; $i < $links->count(); $i++) {
                $taggedUser = $links->item($i)->getAttribute('data-tagged-user-id');

                if ($taggedUser !== '' && is_numeric($taggedUser)) {
                    //Check if user was mentioned before
                    $userMentions = $this->getAllNotifications(
                        $taggedUser,
                        false,
                        0,
                        10,
                        array("type" => "mention", "module" => $module, "moduleId" => $moduleId)
                    );

                    if ($userMentions === false || (is_array($userMentions) && count($userMentions) == 0)) {
                        $notification = array(
                            "userId" => $taggedUser,
                            "read" => '0',
                            "type" => 'mention',
                            "module" => $module,
                            "moduleId" => $moduleId,
                            "message" => sprintf($this->language->__('text.x_mentioned_you'), $authorName),
                            "datetime" => date("Y-m-d H:i:s"),
                            "url" => $url,
                            "authorId" => $authorId,
                        );

                        $this->addNotifications(array($notification));

                        //send email
                        $mailer = app()->make(MailerCore::class);
                        $mailer->setContext('notify_project_users');

                        $subject = sprintf($this->language->__('text.x_mentioned_you'), $authorName);
                        $mailer->setSubject($subject);

                        $emailMessage = $subject . " " . sprintf($this->language->__('text.click_here'), $url);
                        $mailer->setHtml($emailMessage);

                        $taggedUserObject = $this->userRepository->getUser($taggedUser);
                        if (isset($taggedUserObject['username'])) {
                            $mailer->sendMail(array($taggedUserObject['username']), $authorName);
                        }
                    }
                }
            }
        }
    }

}

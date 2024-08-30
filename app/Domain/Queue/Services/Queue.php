<?php

namespace Leantime\Domain\Queue\Services {

    use Leantime\Core\Mailer as MailerCore;
    use Leantime\Core\Language as LanguageCore;
    use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
    use Leantime\Domain\Queue\Workers\DefaultWorker;
    use Leantime\Domain\Queue\Workers\EmailWorker;
    use Leantime\Domain\Queue\Workers\HttpRequestWorker;
    use Leantime\Domain\Queue\Workers\Workers;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;
    use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
    use PHPMailer\PHPMailer\Exception;

    /**
     *
     *
     * @api
     */
    class Queue
    {
        private QueueRepository $queue;
        private UserRepository $userRepo;
        private SettingRepository $settingsRepo;
        private MailerCore $mailer;
        private LanguageCore $language;

        public $availableWorkers = ["email", "httprequest"];

        /**
         * Class constructor.
         *
         * @param QueueRepository $queue The queue repository.
         * @param UserRepository $userRepo The user repository.
         * @param SettingRepository $settingsRepo The settings repository.
         * @param MailerCore $mailer The mailer core.
         * @param LanguageCore $language The language core.
         *
     */
        public function __construct(
            QueueRepository $queue,
            UserRepository $userRepo,
            SettingRepository $settingsRepo,
            MailerCore $mailer,
            LanguageCore $language
        ) {
            // NEW Queuing messaging system
            $this->queue = $queue;

            // We need users and settings and a mailer
            $this->userRepo = $userRepo;
            $this->settingsRepo = $settingsRepo;
            $this->mailer = $mailer;
            $this->language = $language;
        }


        /**
         * Process the queue for a specific worker.
         *
         * @param Workers $worker The worker for which to process the queue.
         * @return bool Returns true if the queue was processed successfully, false otherwise.
         *
     * @api
     */
        public function processQueue(Workers $worker): bool
        {

            $messages = $this->queue->listMessageInQueue($worker);

            if($worker == Workers::EMAILS){
                $worker = app()->make(EmailWorker::class);
                $worker->handleQueue($messages);
            }

            if($worker == Workers::HTTPREQUESTS){
                $worker = app()->make(HttpRequestWorker::class);
                $worker->handleQueue($messages);
            }

            if($worker == Workers::DEFAULT){
                $worker = app()->make(DefaultWorker::class);
                $worker->handleQueue($messages);
            }

            return true;
        }


        public function addToQueue(Workers $channel, string $subject, string $message, $projectId) {

            return $this->queue->addMessageToQueue(
                    channel: $channel,
                    subject: $subject,
                    message: $message,
                    projectId: $projectId,
                    userId: session("userdata.id"));

        }

        public static function addJob(Workers $channel, string $subject, mixed $message, ?int $userId = null, ?int $projectId = null) {

            $queue = app()->make(QueueRepository::class);

            return $queue->addMessageToQueue(
                channel: $channel,
                subject: $subject,
                message: serialize($message),
                projectId: $projectId ?? session('currentProject'),
                userId:$userId ?? session('userdata.id')
            );

        }
    }

}

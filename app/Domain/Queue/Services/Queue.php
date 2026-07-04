<?php

namespace Leantime\Domain\Queue\Services;

use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Leantime\Domain\Queue\Workers\DefaultWorker;
use Leantime\Domain\Queue\Workers\EmailWorker;
use Leantime\Domain\Queue\Workers\HttpRequestWorker;
use Leantime\Domain\Queue\Workers\Workers;

/**
 * @api
 */
class Queue
{
    private QueueRepository $queue;

    public $availableWorkers = ['email', 'httprequest'];

    /**
     * Class constructor.
     *
     * @param  QueueRepository  $queue  The queue repository.
     */
    public function __construct(
        QueueRepository $queue
    ) {
        // NEW Queuing messaging system
        $this->queue = $queue;
    }

    /**
     * Process the queue for a specific worker.
     *
     * @param  Workers  $worker  The worker for which to process the queue.
     * @return bool Returns true if the queue was processed successfully, false otherwise.
     *
     * @api
     */
    public function processQueue(Workers $worker): bool
    {

        $messages = $this->queue->listMessageInQueue($worker);

        if ($worker == Workers::EMAILS) {
            $worker = app()->make(EmailWorker::class);
            $worker->handleQueue($messages);
        }

        if ($worker == Workers::HTTPREQUESTS) {
            $worker = app()->make(HttpRequestWorker::class);
            $worker->handleQueue($messages);
        }

        if ($worker == Workers::DEFAULT) {
            $worker = app()->make(DefaultWorker::class);
            $worker->handleQueue($messages);
        }

        return true;
    }

    public function addToQueue(Workers $channel, string $subject, string $message, $projectId)
    {

        $this->queue->addMessageToQueue(
            channel: $channel,
            subject: $subject,
            message: $message,
            projectId: $projectId,
            userId: session('userdata.id'));

    }

    public static function addJob(Workers $channel, string $subject, mixed $message, ?int $userId = null, ?int $projectId = null)
    {

        $queue = app()->make(QueueRepository::class);

        $queue->addMessageToQueue(
            channel: $channel,
            subject: $subject,
            message: serialize($message),
            projectId: $projectId ?? session('currentProject'),
            userId: $userId ?? session('userdata.id')
        );

    }
}

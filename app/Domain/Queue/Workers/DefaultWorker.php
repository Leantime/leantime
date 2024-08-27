<?php

namespace Leantime\Domain\Queue\Workers;

use GuzzleHttp\Exception\GuzzleException;
use Leantime\Core\Mailer;
use Leantime\Domain\Queue\Repositories\Queue;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Users\Repositories\Users;
use GuzzleHttp\Client;
use PHPUnit\Exception;

class DefaultWorker
{
    public function __construct(
        private Users $userRepo,
        private Setting $settingsRepo,
        private Queue $queue,
        private Client $client
    ) {
    }

    public function handleQueue($messages)
    {


        foreach ($messages as $message) {
            try {
                $payload = unserialize($message['message']);
                $subjectClass = $message['subject'];

                $jobClass = app()->make($subjectClass);

                $result = $jobClass->handle($payload);

                if ($result) {
                    $this->queue->deleteMessageInQueue($message['msghash']);
                    return true;
                }

            } catch (Exception $e) {
                error_log($e);
            }

            return false;
        }
    }
}

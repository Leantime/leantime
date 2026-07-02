<?php

namespace Leantime\Domain\Queue\Workers;

use Illuminate\Support\Facades\Log;
use Leantime\Domain\Queue\Repositories\Queue;
use PHPUnit\Exception;

class DefaultWorker
{
    public function __construct(
        private Queue $queue
    ) {}

    public function handleQueue($messages)
    {

        foreach ($messages as $message) {
            try {
                $payload = safe_unserialize($message['message']);
                $subjectClass = $message['subject'];

                $jobClass = app()->make($subjectClass);

                $result = $jobClass->handle($payload);

                if ($result) {
                    $this->queue->deleteMessageInQueue($message['msghash']);

                    return true;
                } else {
                    Log::error('Worker was not successful');
                }

            } catch (Exception $e) {
                Log::error($e);
            }

            return false;
        }
    }
}

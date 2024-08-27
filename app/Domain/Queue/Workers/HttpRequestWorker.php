<?php

namespace Leantime\Domain\Queue\Workers;

use GuzzleHttp\Exception\GuzzleException;
use Leantime\Core\Mailer;
use Leantime\Domain\Queue\Repositories\Queue;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Users\Repositories\Users;
use GuzzleHttp\Client;

class HttpRequestWorker {


    public function __construct(
        private Users $userRepo,
        private Setting $settingsRepo,
        private Mailer $mailer,
        private Queue $queue,
        private Client $client
    ) {
    }

    public function handleQueue($messages) {


        foreach($messages as $request) {

            try {

                $subjectArray = unserialize($request["subject"]);
                $messageArray = unserialize($request['message']);

                $response = $this->client->request(
                    $subjectArray["method"],
                    $subjectArray["url"],
                    $messageArray
                );

                $this->queue->deleteMessageInQueue($request['msghash']);

            } catch (GuzzleException $e) {
                report($e);
            }

        }


    }


}

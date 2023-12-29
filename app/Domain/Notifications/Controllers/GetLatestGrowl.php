<?php

/**
 * Controller / Delete Canvas
 */

namespace Leantime\Domain\Notifications\Controllers {

    use Symfony\Component\HttpFoundation\JsonResponse;

    /**
     *
     */
    class GetLatestGrowl
    {
        public function init(

        ): void {}

        public function get() {

            $_SESSION['notification'];
            $_SESSION['notifcationType'];
            $_SESSION['event_id'];

            $jsonEncoded = false;
            if($_SESSION['notification'] != ''){
                $notificationArray = array(
                    "notification" => $_SESSION['notification'],
                    "type" => $_SESSION['notifcationType'],
                    "eventId" => $_SESSION['eventId']
                );

                $_SESSION['notification'] = '';
                $_SESSION['notifcationType'] = '';
                $_SESSION['eventId'] = '';

                $jsonEncoded = json_encode($notificationArray);
            }
            return new JsonResponse($jsonEncoded);

        }
    }
}

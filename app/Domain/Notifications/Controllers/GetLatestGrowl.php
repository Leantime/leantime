<?php

/**
 * Controller / Delete Canvas
 */

namespace Leantime\Domain\Notifications\Controllers {

    use Leantime\Core\Controller;
    use Symfony\Component\HttpFoundation\JsonResponse;

    /**
     *
     */
    class GetLatestGrowl extends Controller
    {
        public function init(

        ): void {}

        public function get() {


            $jsonEncoded = false;
            if($_SESSION['notification'] != ''){
                $notificationArray = array(
                    "notification" => $_SESSION['notification'] ?? '',
                    "type" => $_SESSION['notificationType'] ?? '',
                    "eventId" => $_SESSION['eventId'] ?? ''
                );

                $_SESSION['notification'] = '';
                $_SESSION['notificationType'] = '';
                $_SESSION['eventId'] = '';

                $jsonEncoded = json_encode($notificationArray);
            }
            return new JsonResponse($jsonEncoded);

        }
    }
}

<?php

/**
 * Controller / Delete Canvas
 */

namespace Leantime\Domain\Notifications\Controllers {

    use Illuminate\Support\Facades\Cache;
    use Leantime\Core\Controller\Controller;
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

            if(session("notification") != ''){
                $notificationArray = array(
                    "notification" => session("notification") ?? '',
                    "type" => session("notificationType") ?? '',
                    "eventId" => session("eventId") ?? ''
                );

                session(["notification" => '']);
                session(["notificationType" => '']);
                session(["eventId" => '']);

                $jsonEncoded = json_encode($notificationArray);
            }
            return new JsonResponse($jsonEncoded);

        }
    }
}

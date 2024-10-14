<?php

namespace Leantime\Domain\Auth\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeping the session alive when not active
 * @Deprecated With laravels new session management we should not need this anymore
 */
class KeepAlive extends Controller
{
    private AuthService $authService;

    /**
     * init - initialize private variables
     *
     * @access public
     *
     * @param AuthService $authService
     *
     * @return void
     */
    public function init(AuthService $authService): void
    {
        $this->authService = $authService;
    }

    /**
     * get - handle get requests
     *
     * @access public
     *
     * @param array $params
     *
     * @return Response
     */
    public function get(array $params): Response
    {

        $userId = session("userdata.id");
        $sessionId = session()->getId();

        // @TODO: Once we have a session table, check the session is valid in there as well as
        //        added security layer. If not we can log the user out.
        $return = $this->authService->updateUserSessionDB($userId, $sessionId);

        $response = array("status" => "ok");
        if (!$return) {
            $response["status"] = "logout";
        }

        return new JsonResponse($response);
    }
}

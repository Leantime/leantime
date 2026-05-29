<?php

namespace Leantime\Domain\ModuleManager\Controllers;

use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Events\DispatchesEvents;
use Symfony\Component\HttpFoundation\Response;

class Notavailable
{
    /**
     * Redirects to the error page or a filtered alternative.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        $redirect = BASE_URL.'errors/error404';
        $redirect = DispatchesEvents::dispatch_filter('notAvailableRedirect', $redirect, $params);

        return Frontcontroller::redirect($redirect);
    }
}

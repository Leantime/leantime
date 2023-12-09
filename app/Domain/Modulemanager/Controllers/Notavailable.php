<?php

/**
 * Controller / Delete Canvas
 */

namespace Leantime\Domain\ModuleManager\Controllers {

    use Leantime\Core\Eventhelpers;
    use Leantime\Core\Frontcontroller;

    /**
     *
     */
    class Notavailable
    {
        public function run($params)
        {

            $redirect = BASE_URL . "errors/error404";
            $redirect = Eventhelpers::dispatch_filter("notAvailableRedirect", $redirect, $params);

            return Frontcontroller::redirect($redirect);
        }
    }
}

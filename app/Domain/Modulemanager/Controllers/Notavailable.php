<?php

/**
 * Controller / Delete Canvas
 */

namespace Leantime\Domain\ModuleManager\Controllers {

    use Leantime\Core\Controller\Frontcontroller;

    class Notavailable
    {
        use DispatchesEvents;

        public function run($params)
        {

            $redirect = BASE_URL.'errors/error404';
            $redirect = self::dispatchFilter('notAvailableRedirect', $redirect, $params);

            return Frontcontroller::redirect($redirect);
        }
    }
}

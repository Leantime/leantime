<?php

/**
 * Controller / Delete Canvas
 */

namespace Leantime\Domain\ModuleManager\Controllers {

    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Core\Events\DispatchesEvents;

    class Notavailable
    {
        public function run($params)
        {

            $redirect = BASE_URL.'errors/error404';
            $redirect = DispatchesEvents::dispatchFilter('notAvailableRedirect', $redirect, $params);

            return Frontcontroller::redirect($redirect);
        }
    }
}

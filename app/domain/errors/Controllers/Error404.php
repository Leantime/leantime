<?php

namespace Leantime\Domain\Errors\Controllers {

    use Leantime\Core\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\Controller;

    class Error404 extends Controller
    {
        public function run()
        {

            FrontcontrollerCore::setResponseCode(404);
            $this->tpl->display('errors.error404');
        }
    }

}

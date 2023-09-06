<?php

namespace Leantime\Domain\Errors\Controllers {

    use Leantime\Core\Frontcontroller as FrontcontrollerCore;
    use Leantime\Core\Controller;

    class Error500 extends Controller
    {
        public function run()
        {

            FrontcontrollerCore::setResponseCode(500);
            $this->tpl->display('errors.error500');
        }
    }

}

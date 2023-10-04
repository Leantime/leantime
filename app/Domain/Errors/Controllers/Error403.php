<?php

namespace Leantime\Domain\Errors\Controllers {

    use Leantime\Core\Frontcontroller as FrontcontrollerCore;
    use Leantime\Core\Controller;

    /**
     *
     */
    class Error403 extends Controller
    {
        /**
         * @return void
         * @throws \Exception
         */
        public function run(): void
        {

            FrontcontrollerCore::setResponseCode(403);
            $this->tpl->display('errors.error403');
        }
    }
}

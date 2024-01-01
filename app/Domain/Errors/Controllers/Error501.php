<?php

namespace Leantime\Domain\Errors\Controllers {

    use Leantime\Core\Frontcontroller as FrontcontrollerCore;
    use Leantime\Core\Controller;

    /**
     *
     */
    class Error501 extends Controller
    {
        /**
         * @return void
         * @throws \Exception
         */
        public function run(): void
        {

            FrontcontrollerCore::setResponseCode(501);
            $this->tpl->display('errors.error501', layout:"error",);
        }
    }

}

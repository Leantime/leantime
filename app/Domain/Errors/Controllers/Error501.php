<?php

namespace Leantime\Domain\Errors\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;

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
            $this->tpl->display(
                template: 'errors.error501',
                layout:"error",
                responseCode: 501);
        }
    }

}

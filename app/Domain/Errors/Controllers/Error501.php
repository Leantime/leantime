<?php

namespace Leantime\Domain\Errors\Controllers {

    use Leantime\Core\Controller\Controller;

    class Error501 extends Controller
    {
        /**
         * @throws \Exception
         */
        public function run(): void
        {
            $this->tpl->display(
                template: 'errors.error501',
                layout: 'error',
                responseCode: 501);
        }
    }

}

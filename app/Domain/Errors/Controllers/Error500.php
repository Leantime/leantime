<?php

namespace Leantime\Domain\Errors\Controllers {

    use Leantime\Core\Frontcontroller as FrontcontrollerCore;
    use Leantime\Core\Controller;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class Error500 extends Controller
    {
        /**
         * @return Response
         * @throws \Exception
         */
        public function run(): Response
        {
            return $this->tpl->display('errors.error500', layout:"error", responseCode: 500,);
        }
    }

}

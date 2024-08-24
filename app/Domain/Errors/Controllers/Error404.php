<?php

namespace Leantime\Domain\Errors\Controllers {

    use Leantime\Core\Controller\Controller;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class Error404 extends Controller
    {
        /**
         * @return Response
         * @throws \Exception
         */
        public function run(): Response
        {
            return $this->tpl->display('errors.error404', layout:"error", responseCode: 404);
        }
    }

}

<?php

namespace Leantime\Domain\Errors\Controllers;

use Leantime\Core\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class Error403 extends Controller
{
    /**
     * @throws \Exception
     *
     * @return Response
     */
    public function run(): Response
    {
        return $this->tpl->display('errors.error403', layout:'error', responseCode: 403);
    }
}

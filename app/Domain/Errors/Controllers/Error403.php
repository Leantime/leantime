<?php

namespace Leantime\Domain\Errors\Controllers;

use Leantime\Core\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class Error403 extends Controller
{
    /**
     * Displays the 403 Forbidden error page.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        return $this->tpl->display('errors.error403', layout: 'error', responseCode: 403);
    }
}

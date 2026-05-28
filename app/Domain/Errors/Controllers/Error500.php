<?php

namespace Leantime\Domain\Errors\Controllers;

use Leantime\Core\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class Error500 extends Controller
{
    /**
     * Displays the 500 Internal Server Error page.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        return $this->tpl->display('errors.error500', layout: 'error', responseCode: 500);
    }
}

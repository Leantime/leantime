<?php

namespace Leantime\Domain\Errors\Controllers;

use Leantime\Core\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class Error404 extends Controller
{
    /**
     * Displays the 404 Not Found error page.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        return $this->tpl->display('errors.error404', layout: 'error', responseCode: 404);
    }
}

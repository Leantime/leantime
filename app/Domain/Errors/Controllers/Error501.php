<?php

namespace Leantime\Domain\Errors\Controllers;

use Leantime\Core\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class Error501 extends Controller
{
    /**
     * Displays the 501 Not Implemented error page.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        return $this->tpl->display(
            template: 'errors.error501',
            layout: 'error',
            responseCode: 501
        );
    }
}

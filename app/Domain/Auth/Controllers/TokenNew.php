<?php

namespace Leantime\Domain\Auth\Controllers;

use Leantime\Core\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Renders the "create personal access token" modal.
 */
class TokenNew extends Controller
{
    /**
     * Displays the new token form (loaded into a modal via #/auth/tokenNew).
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        return $this->tpl->displayPartial('auth.tokenNew');
    }
}

<?php

namespace Leantime\Domain\Wiki\Controllers;

use Leantime\Core\Http\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class Templates extends Controller
{
    public function init(): void {}

    /**
     * @throws \Exception
     */
    public function get($params): Response
    {
        return $this->tpl->displayPartial('wiki.templates');
    }
}

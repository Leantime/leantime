<?php

namespace Leantime\Domain\Wiki\Controllers;

use Leantime\Core\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class Templates extends Controller
{
    /**
     * @return void
     */
    public function init(): void
    {
    }

    /**
     * @param $params
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function get($params): Response
    {
        return $this->tpl->displayPartial('wiki.templates');
    }
}

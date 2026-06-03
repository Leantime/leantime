<?php

namespace Leantime\Domain\Wiki\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Wiki\Permissions\WikiPermissions;
use Symfony\Component\HttpFoundation\Response;

class Templates extends Controller
{
    public function init(): void {}

    /**
     * @throws \Exception
     */
    #[RequiresPermission(WikiPermissions::VIEW)]
    public function get($params): Response
    {
        return $this->tpl->displayPartial('wiki.templates');
    }
}

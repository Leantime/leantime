<?php

namespace Leantime\Domain\Plugins\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Plugins\Permissions\PluginsPermissions;
use Symfony\Component\HttpFoundation\Response;

class Marketplace extends Controller
{
    #[RequiresPermission(PluginsPermissions::MANAGE, global: true)]
    public function get(): Response
    {

        $this->tpl->assign('plugins', []);

        return $this->tpl->display('plugins.marketplace');
    }
}

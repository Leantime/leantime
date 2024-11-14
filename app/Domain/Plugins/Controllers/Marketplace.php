<?php

namespace Leantime\Domain\Plugins\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;
use Symfony\Component\HttpFoundation\Response;

class Marketplace extends Controller
{
    private PluginService $pluginService;

    public function init(
        PluginService $pluginService,
    ): void {
        $this->pluginService = $pluginService;
    }

    public function get(): Response
    {

        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        $this->tpl->assign('plugins', []);

        return $this->tpl->display('plugins.marketplace');
    }
}

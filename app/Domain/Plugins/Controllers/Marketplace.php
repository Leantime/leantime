<?php

namespace Leantime\Domain\Plugins\Controllers;

use Leantime\Core\Controller;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;
use Symfony\Component\HttpFoundation\Response;

class Marketplace extends Controller
{
    /**
     * @var PluginService
     */
    private PluginService $pluginService;

    /**
     * @return void
     */
    public function init(
        PluginService $pluginService,
    ): void {
        $this->pluginService = $pluginService;
    }

    /**
     * @return Response
     */
    public function get(): Response
    {
        $this->tpl->assign('plugins', []);

        return $this->tpl->display('plugins.marketplace');
    }
}

<?php

namespace Leantime\Domain\Plugins\Controllers;

use Leantime\Core\Controller;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;

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
     * @return void
     */
    public function get(): void
    {

        $this->tpl->assign('plugins', []);

        $this->tpl->display('plugins.marketplace');
    }
}

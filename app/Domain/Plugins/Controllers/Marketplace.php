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
        /**
         * @var \Illuminate\Support\Collection
         */
        $plugins = $this->pluginService->getMarketplacePlugins(
            $this->incomingRequest->query->get('page', 1),
            $this->incomingRequest->query->get('search', ''),
        );

        $this->tpl->assign('plugins', $plugins->toArray());

        $this->tpl->display('plugins.marketplace');
    }
}

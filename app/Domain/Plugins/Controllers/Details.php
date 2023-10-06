<?php

namespace Leantime\Domain\Plugins\Controllers;

use Leantime\Core\Controller;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;

class Details extends Controller
{
    /**
     * @var PluginService
     */
    private PluginService $pluginService;

    /**
     * @param PluginService $pluginService
     * @return void
     */
    public function init(PluginService $pluginService): void
    {
        $this->pluginService = $pluginService;
    }

    /**
     * @return void
     */
    public function get(): void
    {
        if (! $this->incomingRequest->query->has('id')) {
            throw new \Exception('Plugin Identifier is required');
        }

        /**
         * @var \Illuminate\Support\Collection
         */
        $versions = $this->pluginService->getMarketplacePlugin(
            $this->incomingRequest->query->get('id'),
        );

        $this->tpl->assign('versions', $versions->toArray());

        $this->tpl->display('plugins.plugindetails', 'blank');
    }
}

<?php

namespace Leantime\Domain\Plugins\Hxcontrollers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Http\Controller\HtmxController;
use Leantime\Domain\Plugins\Models\MarketplacePlugin;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;

class Marketplaceplugins extends HtmxController
{
    protected static string $view = 'plugins::partials.pluginlist';

    private PluginService $pluginService;

    public function init(
        PluginService $pluginService,
    ): void {
        $this->pluginService = $pluginService;
    }

    /**
     * @throws BindingResolutionException
     */
    public function getlist(): void
    {
        /** @var MarketplacePlugin[] $plugins */
        $plugins = $this->pluginService->getMarketplacePlugins(
            $this->incomingRequest->query->get('page', 1),
            $this->incomingRequest->query->get('search', ''),
        );

        $this->tpl->assign('plugins', $plugins);
    }

    public function search(): void {}
}

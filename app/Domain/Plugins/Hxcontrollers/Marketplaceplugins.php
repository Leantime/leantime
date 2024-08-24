<?php

namespace Leantime\Domain\Plugins\Hxcontrollers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Plugins\Models\MarketplacePlugin;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;

/**
 *
 */
class Marketplaceplugins extends HtmxController
{
    /**
     * @var string
     */
    protected static string $view = 'plugins::partials.pluginlist';

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

    /**
     * @return void
     */
    public function search(): void
    {
    }
}

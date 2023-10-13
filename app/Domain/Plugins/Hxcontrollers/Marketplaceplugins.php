<?php

namespace Leantime\Domain\Plugins\Hxcontrollers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\HtmxController;
use Leantime\Domain\Menu\Services\Menu;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Timesheets\Services\Timesheets;

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

        $plugins = $this->pluginService->getMarketplacePlugins(
            $this->incomingRequest->query->get('page', 1),
            $this->incomingRequest->query->get('search', ''),
        );

        $this->tpl->assign('plugins', $plugins->toArray());

    }

    /**
     * @return void
     */
    public function search(): void
    {
    }
}

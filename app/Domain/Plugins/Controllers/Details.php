<?php

namespace Leantime\Domain\Plugins\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Plugins\Permissions\PluginsPermissions;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;
use Symfony\Component\HttpFoundation\Response;

class Details extends Controller
{
    private PluginService $pluginService;

    public function init(PluginService $pluginService): void
    {
        $this->pluginService = $pluginService;
    }

    #[RequiresPermission(PluginsPermissions::MANAGE, global: true)]
    public function get(): Response
    {

        if (! $this->incomingRequest->query->has('id')) {
            throw new \Exception('Plugin Identifier is required');
        }

        /**
         * @var \Leantime\Domain\Plugins\Models\MarketplacePlugin|false $plugin
         */
        $plugin = $this->pluginService->getMarketplacePlugin(
            $this->incomingRequest->query->get('id'),
        );

        if (! $plugin) {
            return $this->tpl->display('errors.error404', 'blank');
        }

        $this->tpl->assign('isBundle', $this->pluginService->isBundle($plugin));
        $this->tpl->assign('plugin', $plugin);

        return $this->tpl->display('plugins.plugindetails', 'blank');
    }
}

<?php

namespace Leantime\Domain\Plugins\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;
use Symfony\Component\HttpFoundation\Response;

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
     * @return Response
     */
    public function get(): Response
    {

        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

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
            return $this->tpl->display('error.error404', 'blank');
        }

        $this->tpl->assign('plugin', $plugin);

        return $this->tpl->display('plugins.plugindetails', 'blank');
    }
}

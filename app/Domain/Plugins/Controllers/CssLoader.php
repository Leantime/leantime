<?php

namespace Leantime\Domain\Plugins\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Plugins\Permissions\PluginsPermissions;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;
use Symfony\Component\HttpFoundation\Response;

class CssLoader extends Controller
{
    private PluginService $pluginService;

    public function init(PluginService $pluginService): void
    {
        $this->pluginService = $pluginService;
    }

    #[RequiresPermission(PluginsPermissions::MANAGE, global: true)]
    public function get(): Response
    {
        $response = new Response($this->pluginService->getAggregatedPluginCss());
        $response->headers->set('Content-Type', 'text/css');

        return $response;
    }
}

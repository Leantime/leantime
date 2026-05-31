<?php

namespace Leantime\Domain\Plugins\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;
use Symfony\Component\HttpFoundation\Response;

class CssLoader extends Controller
{
    private PluginService $pluginService;

    public function init(PluginService $pluginService): void
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin]);
        $this->pluginService = $pluginService;
    }

    public function get(): Response
    {
        $response = new Response($this->pluginService->getAggregatedPluginCss());
        $response->headers->set('Content-Type', 'text/css');

        return $response;
    }
}

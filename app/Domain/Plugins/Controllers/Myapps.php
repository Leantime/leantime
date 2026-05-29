<?php

namespace Leantime\Domain\Plugins\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;
use Symfony\Component\HttpFoundation\Response;

class Myapps extends Controller
{
    private PluginService $pluginService;

    public function init(PluginService $pluginService): void
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);
        $this->pluginService = $pluginService;
    }

    /**
     * @throws BindingResolutionException
     */
    public function get(): Response
    {
        foreach (['install', 'enable', 'disable', 'remove'] as $action) {
            $id = $this->incomingRequest->query->get($action);

            if (empty($id)) {
                continue;
            }

            try {
                $this->tpl->setNotification(...$this->pluginService->performPluginAction($action, $id));
            } catch (\Exception $e) {
                $this->tpl->setNotification($e->getMessage(), 'error');
            }

            return Frontcontroller::redirect(BASE_URL.'/plugins/myapps');
        }

        $this->tpl->assign('newPlugins', $this->pluginService->discoverNewPlugins());
        $this->tpl->assign('installedPlugins', $this->pluginService->getAllPlugins());

        return $this->tpl->display('plugins.myapps');
    }

    public function post($params): Response
    {
        return Frontcontroller::redirect(BASE_URL.'/plugins/myapps');
    }
}

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
     * Lists new and installed plugins.
     *
     * @throws BindingResolutionException
     */
    public function get(): Response
    {
        $this->tpl->assign('newPlugins', $this->pluginService->discoverNewPlugins());
        $this->tpl->assign('installedPlugins', $this->pluginService->getAllPlugins());

        return $this->tpl->display('plugins.myapps');
    }

    /**
     * Installs, enables, disables or removes a plugin. These change the installation, so they
     * are only accepted as POST.
     *
     * @param  array  $params  Request parameters; one of install/enable/disable/remove => plugin id.
     */
    public function post($params): Response
    {
        foreach (['install', 'enable', 'disable', 'remove'] as $action) {
            $id = $this->incomingRequest->request->get($action);

            if (empty($id)) {
                continue;
            }

            try {
                $this->tpl->setNotification(...$this->pluginService->performPluginAction($action, $id));
            } catch (\Exception $e) {
                $this->tpl->setNotification($e->getMessage(), 'error');
            }

            break;
        }

        return Frontcontroller::redirect(BASE_URL.'/plugins/myapps');
    }
}

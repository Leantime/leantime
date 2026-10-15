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
        $this->tpl->assign('autoUpdate', $this->pluginService->isAutoUpdateEnabled());

        return $this->tpl->display('plugins.myapps');
    }

    /**
     * Installs, enables, disables or removes a plugin. These change the installation, so they
     * are only accepted as POST.
     *
     * @param  array  $params  Request parameters; one of install/enable/disable/remove/update => plugin id,
     *                         checkUpdates, or autoUpdateShown (+ autoUpdate when switched on).
     */
    public function post($params): Response
    {
        if ($this->incomingRequest->request->has('autoUpdateShown')) {
            $this->pluginService->setAutoUpdate($this->incomingRequest->request->has('autoUpdate'));
            $this->tpl->setNotification(__('notification.plugin_auto_update_saved'), 'success');

            return Frontcontroller::redirect(BASE_URL.'/plugins/myapps');
        }

        if ($this->incomingRequest->request->has('checkUpdates')) {
            try {
                ['updates' => $updates, 'failed' => $failed] = $this->pluginService->checkForUpdates();

                if ($failed !== []) {
                    $this->tpl->setNotification(sprintf(__('notification.plugin_updates_check_failed'), implode(', ', $failed)), 'error');
                } else {
                    $this->tpl->setNotification(
                        count($updates) > 0 ? sprintf(__('notification.plugin_updates_found'), count($updates)) : __('notification.plugin_updates_none'),
                        'success'
                    );
                }
            } catch (\Exception $e) {
                $this->tpl->setNotification($e->getMessage(), 'error');
            }

            return Frontcontroller::redirect(BASE_URL.'/plugins/myapps');
        }

        foreach (['install', 'enable', 'disable', 'remove', 'update'] as $action) {
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

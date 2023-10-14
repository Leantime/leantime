<?php

namespace Leantime\Domain\Plugins\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Controller;
    use Leantime\Domain\Plugins\Services\Plugins as PluginService;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Auth\Models\Roles;

    /**
     *
     */
    class Myapps extends Controller
    {
        private PluginService $pluginService;

        /**
         * @param PluginService $pluginService
         * @return void
         */
        public function init(PluginService $pluginService): void
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin]);
            $this->pluginService = $pluginService;
        }

        /**
         * @return void
         * @throws BindingResolutionException
         */
        public function get(): void
        {

            if (isset($_GET['install']) && $_GET['install'] != '') {
                $result = $this->pluginService->installPlugin($_GET['install']);
                if ($result) {
                    $this->tpl->setNotification("notification.plugin_install_success", "success");
                } else {
                    $this->tpl->setNotification("notification.plugin_install_error", "error");
                }

                $this->tpl->redirect(BASE_URL . "/plugins/show");
            }

            if (isset($_GET['enable']) && $_GET['enable'] != '') {
                $result = $this->pluginService->enablePlugin((int) $_GET['enable']);
                if ($result) {
                    $this->tpl->setNotification("notification.plugin_activation_success", "success");
                } else {
                    $this->tpl->setNotification("notification.plugin_activation_error", "error");
                }

                $this->tpl->redirect(BASE_URL . "/plugins/show");
            }

            if (isset($_GET['disable']) && $_GET['disable'] != '') {
                $result = $this->pluginService->disablePlugin((int) $_GET['disable']);
                if ($result) {
                    $this->tpl->setNotification("notification.plugin_disable_success", "success");
                } else {
                    $this->tpl->setNotification("notification.plugin_disable_error", "error");
                }

                $this->tpl->redirect(BASE_URL . "/plugins/show");
            }

            if (isset($_GET['remove']) && $_GET['remove'] != '') {
                $result = $this->pluginService->removePlugin((int) $_GET['remove']);
                if ($result) {
                    $this->tpl->setNotification("notification.plugin_remove_success", "success");
                } else {
                    $this->tpl->setNotification("notification.plugin_remove_error", "error");
                }

                $this->tpl->redirect(BASE_URL . "/plugins/show");
            }

            $newPlugins = $this->pluginService->discoverNewPlugins();
            $installedPlugins = $this->pluginService->getAllPlugins();

            $this->tpl->assign("newPlugins", $newPlugins);
            $this->tpl->assign("installedPlugins", $installedPlugins);
            $this->tpl->display("plugins.myapps");
        }

        /**
         * @param $params
         * @return void
         */
        /**
         * @param $params
         * @return void
         */
        public function post($params): void
        {

            $this->tpl->redirect(BASE_URL . "/plugins/show");
        }
    }
}

<?php

namespace Leantime\Domain\Plugins\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Plugins\Services\Plugins as PluginService;
    use Symfony\Component\HttpFoundation\Response;

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
            Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);
            $this->pluginService = $pluginService;
        }

        /**
         * @return Response
         * @throws BindingResolutionException
         */
        public function get(): Response
        {
            foreach (['install', 'enable', 'disable', 'remove'] as $varName) {
                if (empty($_GET[$varName])) {
                    continue;
                }

                try {
                    $notification = $this->pluginService->{"{$varName}Plugin"}($_GET[$varName])
                        ? ["notification.plugin_{$varName}_success", "success"]
                        : ["notification.plugin_{$varName}_error", "error"];

                    $this->tpl->setNotification(...$notification);
                    return Frontcontroller::redirect(BASE_URL . "/plugins/myapps");
                } catch (\Exception $e) {
                    $this->tpl->setNotification($e->getMessage(), "error");
                    return Frontcontroller::redirect(BASE_URL . "/plugins/myapps");
                }
            }

            $newPlugins = $this->pluginService->discoverNewPlugins();
            $installedPlugins = $this->pluginService->getAllPlugins();

            $this->tpl->assign("newPlugins", $newPlugins);
            $this->tpl->assign("installedPlugins", $installedPlugins);
            return $this->tpl->display("plugins.myapps");
        }

        /**
         * @param $params
         * @return Response
         */
        public function post($params): Response
        {
            return Frontcontroller::redirect(BASE_URL . "/plugins/myapps");
        }
    }
}

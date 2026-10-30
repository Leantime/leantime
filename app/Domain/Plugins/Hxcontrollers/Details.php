<?php

namespace Leantime\Domain\Plugins\Hxcontrollers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Client\RequestException;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Plugins\Permissions\PluginsPermissions;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;

class Details extends HtmxController
{
    protected static string $view = 'plugins::plugindetails';

    private PluginService $pluginService;

    public function init(
        PluginService $pluginService,
    ): void {
        $this->pluginService = $pluginService;
    }

    /**
     * @throws BindingResolutionException
     */
    #[RequiresPermission(PluginsPermissions::MANAGE, global: true)]
    public function install(): string
    {
        $pluginProps = $this->incomingRequest->request->all()['plugin'];
        $version = $pluginProps['version'];
        unset($pluginProps['version']);

        $pluginModel = $this->pluginService->buildMarketplacePluginFromRequest($pluginProps);

        $this->tpl->assign('plugin', $pluginModel);
        $this->tpl->assign('isBundle', $this->pluginService->isBundle($pluginModel));

        try {
            $this->pluginService->installMarketplacePlugin($pluginModel, $version);
        } catch (RequestException $e) {
            report($e);

            $this->tpl->assign('formError', $this->pluginService->parseMarketplaceError($e));

            return 'plugin-installation';
        } catch (\Exception $e) {
            // Failures after the download (signature check, unzip, file permissions) carry a message
            // meant for the admin; without this they ended in an error response and no message.
            report($e);

            $this->tpl->assign('formError', $e->getMessage());

            return 'plugin-installation';
        }

        if ($this->pluginService->isEnabled($pluginModel->identifier)) {
            $this->tpl->assign('formNotification', __('marketplace.updated'));

            return 'plugin-installation';
        }

        $this->tpl->assign('formNotification', sprintf(__('marketplace.installed'), '/plugins/myapps'));

        return 'plugin-installation';
    }
}

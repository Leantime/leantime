<?php

namespace Leantime\Domain\Plugins\Hxcontrollers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\HtmxController;
use Leantime\Domain\Plugins\Models\MarketplacePlugin;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;
use Illuminate\Support\Str;

/**
 *
 */
class Details extends HtmxController
{
    /**
     * @var string
     */
    protected static string $view = 'plugins::plugindetails';

    /**
     * @var PluginService
     */
    private PluginService $pluginService;

    /**
     * @return void
     */
    public function init(
        PluginService $pluginService,
    ): void {
        $this->pluginService = $pluginService;
    }

    /**
     * @return string
     * @throws BindingResolutionException
     */
    public function install(): string
    {
        $pluginModel = app(MarketplacePlugin::class);
        $pluginProps = $this->incomingRequest->request->all()['plugin'];
        collect($pluginProps)->each(fn($value, $key) => $pluginModel->{$key} = $value ?? '');

        if (! empty($pluginModel->identifier)) {
            $pluginModel->identifier = Str::studly($pluginModel->identifier);
        }

        $this->tpl->assign('plugin', $pluginModel);

        try {
            $this->pluginService->installMarketplacePlugin($pluginModel);
        } catch (\Throwable $e) {
            $this->tpl->assign('formError', $e->getMessage());
            return 'plugin-installation';
        }

        if ($this->pluginService->isPluginEnabled($pluginModel->identifier)) {
            $this->tpl->assign('formNotification', 'Plugin Updated Successfully!');
            return 'plugin-installation';
        }

        $this->tpl->assign('formNotification', 'Plugin installed successfully! Head to <a href="/plugins/myapps">My Apps</a> to activate.');
        return 'plugin-installation';
    }
}

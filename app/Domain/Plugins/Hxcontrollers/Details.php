<?php

namespace Leantime\Domain\Plugins\Hxcontrollers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Leantime\Core\HtmxController;
use Leantime\Domain\Plugins\Models\MarketplacePlugin;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;
use Illuminate\Support\Str;
use Leantime\Core\Frontcontroller;

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
        $pluginProps = $this->incomingRequest->request->all()['plugin'];
        $version = $pluginProps['version'];
        unset($pluginProps['version']);
        $builder = build(new MarketplacePlugin);

        foreach ($pluginProps as $key => $value) {
            $newValue = json_decode(json: $value, flags: JSON_OBJECT_AS_ARRAY);

            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $newValue;
            }

            $builder->set($key, $value);
        }

        $pluginModel = $builder->get();

        $this->tpl->assign('plugin', $pluginModel);

        try {
            $this->pluginService->installMarketplacePlugin($pluginModel, $version);
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

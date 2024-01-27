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

            //Parse and clean up error message
            $errorJson = str_replace("HTTP request returned status code 500:", "", $e->getMessage());
            $errors = json_decode(trim($errorJson));

            $this->tpl->assign('formError', $errors->error ?? "There was an error installing the plugin");
            return 'plugin-installation';

        }

        if ($this->pluginService->isPluginEnabled($pluginModel->identifier)) {
            $this->tpl->assign('formNotification', __('marketplace.updated'));
            return 'plugin-installation';
        }

        $this->tpl->assign('formNotification', sprintf(__('marketplace.installed'), '/plugins/myapps'));
        return 'plugin-installation';
    }
}




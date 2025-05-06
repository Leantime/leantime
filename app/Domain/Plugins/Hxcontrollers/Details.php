<?php

namespace Leantime\Domain\Plugins\Hxcontrollers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Client\RequestException;
use Leantime\Core\Http\Controller\HtmxController;
use Leantime\Domain\Plugins\Models\MarketplacePlugin;
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

        /**
         * @var \Leantime\Domain\Plugins\Models\MarketplacePlugin|false $plugin
         */
        $isBundle = false;
        if (collect($pluginModel->categories)->where('slug', '=', 'bundles')->count() > 0) {
            $isBundle = true;
        }

        $this->tpl->assign('isBundle', $isBundle);

        try {
            $this->pluginService->installMarketplacePlugin($pluginModel, $version);
        } catch (RequestException $e) {

            // Parse and clean up error message
            $errorJson = str_replace('HTTP request returned status code 500:', '', $e->getMessage());
            $errorJson = str_replace('HTTP request returned status code 200:', '', $errorJson);
            $errors = json_decode(trim($errorJson));
            report($e);

            $this->tpl->assign('formError', $errors->error ?? $errors->message ?? 'There was an error installing the plugin');

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

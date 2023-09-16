<?php

namespace Leantime\Domain\Plugins\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Plugins\Services\Plugins as PluginService;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Auth\Models\Roles;

    class CssLoader extends Controller
    {
        private PluginService $pluginService;

        public function init(PluginService $pluginService)
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin]);
            $this->pluginService = $pluginService;
        }

        /**
         * @return void
         */
        public function get()
        {
            $cssFiles = array();

            $cssFiles = self::dispatch_filter("pluginCss", $cssFiles);

            $cssString = '';
            foreach ($cssFiles as $file) {
                if (file_exists(APP_ROOT . "/plugins/" . $file)) {
                    $cssString = file_get_contents(APP_ROOT . "/plugins/" . $file);
                }
            }
            header("Content-Type: text/css");
            echo $cssString;
        }
    }
}

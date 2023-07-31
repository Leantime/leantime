<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;
    use leantime\domain\models\auth\roles;

    class cssLoader extends controller
    {
        private services\plugins $pluginService;

        public function init(services\plugins $pluginService)
        {
            auth::authOrRedirect([roles::$owner, roles::$admin]);
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
            foreach($cssFiles as $file) {
                if(file_exists(APP_ROOT . "/plugins/".$file)){
                    $cssString = file_get_contents(APP_ROOT . "/plugins/".$file);

                }
            }
            header("Content-Type: text/css");
            echo $cssString;

        }

    }
}

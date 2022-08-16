<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use DebugBar\StandardDebugBar;

    class header
    {

        public function run()
        {

            $tpl = new core\template();
            $appSettings = new core\appSettings();

            $debugbarRenderer = "";

            if($appSettings->debug == 1) {

                $debugbar = new StandardDebugBar();

                $debugbarRenderer = $debugbar->getJavascriptRenderer(
                    "/js/libs/DebugBar/Resources",
                    "/js/libs/debugbar/"
                );
            }

            $tpl->assign('debugRenderer', $debugbarRenderer);
            $tpl->assign('appSettings', $appSettings);
            $tpl->displayPartial('general.header');
        }
    }
}

<?php

namespace leantime\domain\controllers {

    use DebugBar\StandardDebugBar;
    use leantime\core;

    class pageBottom
    {

        private $tpl;
        private $settings;

        public function __construct()
        {
            $this->tpl = new core\template();
            $this->settings = new core\appSettings();
        }

        public function run()
        {

            $debugbarRenderer = "";

            if($this->settings->debug == 1) {

                $debugbar = new StandardDebugBar();

                $debugbarRenderer = $debugbar->getJavascriptRenderer(
                    "/js/libs/DebugBar/Resources",
                    "/js/libs/debugbar/"
                );
            }

            $this->tpl->assign('debugRenderer', $debugbarRenderer);

            $this->tpl->assign('appSettings', $this->settings);
            $this->tpl->displayPartial('general.pageBottom');
        }
    }
}

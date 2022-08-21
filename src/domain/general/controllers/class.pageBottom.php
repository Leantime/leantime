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

            $this->tpl->displayPartial('general.pageBottom');
        }
    }
}

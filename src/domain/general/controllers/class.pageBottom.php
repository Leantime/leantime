<?php

namespace leantime\domain\controllers {

    use DebugBar\StandardDebugBar;
    use leantime\core;
    use leantime\base\controller;

    class pageBottom extends controller
    {

        private $settings;

        public function __construct()
        {

            $this->settings = new core\appSettings();

        }

        public function run()
        {

            $this->tpl->displayPartial('general.pageBottom');

        }

    }

}

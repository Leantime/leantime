<?php

namespace leantime\domain\controllers {

    use DebugBar\StandardDebugBar;
    use leantime\core;
    use leantime\core\controller;

    class pageBottom extends controller
    {
        private $settings;

        public function init()
        {

            $this->settings = new core\appSettings();
        }

        public function run()
        {

            $this->tpl->displayPartial('pageparts.pageBottom');
        }
    }

}

<?php

namespace leantime\domain\controllers {

    use DebugBar\StandardDebugBar;
    use leantime\core;
    use leantime\core\controller;

    class pageBottom extends controller
    {
        private $settings;

        public function init(core\appSettings $appSettings)
        {
            $this->settings = $appSettings;
        }

        public function run()
        {
            $this->tpl->assign("appSettings", $this->settings);
            $this->tpl->displayPartial('pageparts.pageBottom');
        }
    }

}

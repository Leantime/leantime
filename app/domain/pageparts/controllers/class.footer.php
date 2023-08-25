<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;

    class footer extends controller
    {
        private core\appSettings $settings;

        public function init(core\appSettings $settings)
        {
            $this->settings = $settings;
        }

        public function run()
        {
            $this->tpl->assign("version", $this->settings->appVersion);
            $this->tpl->displayPartial('pageparts.footer');
        }
    }

}

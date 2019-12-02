<?php

namespace leantime\domain\controllers {

    use leantime\core;

    class footer
    {

        private $tpl;
        private $settings;


        public function __construct()
        {
            $this->tpl = new core\template();
            $this->settings = new core\settings();
        }

        public function run()
        {
            $this->tpl->assign("version", $this->settings->appVersion);
            $this->tpl->displayPartial('general.footer');
        }
    }
}

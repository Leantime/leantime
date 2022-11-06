<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\base\controller;

    class footer extends controller
    {

        private $settings;

        public function __construct()
        {

            $this->settings = new core\appSettings();

        }

        public function run()
        {

            $this->tpl->assign("version", $this->settings->appVersion);
            $this->tpl->displayPartial('general.footer');

        }

    }

}

<?php

namespace Leantime\Domain\Pageparts\Controllers {

    use Leantime\Core\AppSettings as AppSettingCore;
    use Leantime\Core\Controller;

    class Footer extends Controller
    {
        private AppSettingCore $settings;

        public function init(AppSettingCore $settings)
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

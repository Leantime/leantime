<?php

namespace Leantime\Domain\Pageparts\Controllers {

    use Leantime\Core\AppSettings as AppSettingCore;
    use Leantime\Core\Controller;

    /**
     *
     */

    /**
     *
     */
    class Footer extends Controller
    {
        private AppSettingCore $settings;

        /**
         * @param AppSettingCore $settings
         * @return void
         */
        public function init(AppSettingCore $settings): void
        {
            $this->settings = $settings;
        }

        /**
         * @return void
         */
        public function run(): void
        {
            $this->tpl->assign("version", $this->settings->appVersion);
            $this->tpl->displayPartial('pageparts.footer');
        }
    }

}

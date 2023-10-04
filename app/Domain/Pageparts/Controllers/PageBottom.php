<?php

namespace Leantime\Domain\Pageparts\Controllers {

    use Leantime\Core\AppSettings as AppSettingCore;
    use Leantime\Core\Controller;

    /**
     *
     */
    class PageBottom extends Controller
    {
        private AppSettingCore $settings;

        /**
         * @param AppSettingCore $appSettings
         * @return void
         */
        public function init(AppSettingCore $appSettings): void
        {
            $this->settings = $appSettings;
        }

        /**
         * @return void
         * @throws \Exception
         */
        public function run(): void
        {
            $this->tpl->assign("appSettings", $this->settings);
            $this->tpl->displayPartial('pageparts.pageBottom');
        }
    }

}

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
    class PageBottom extends Controller
    {
        private $settings;

        /**
         * @param AppSettingCore $appSettings
         * @return void
         */
        public function init(AppSettingCore $appSettings)
        {
            $this->settings = $appSettings;
        }

        /**
         * @return void
         */
        public function run()
        {
            $this->tpl->assign("appSettings", $this->settings);
            $this->tpl->displayPartial('pageparts.pageBottom');
        }
    }

}

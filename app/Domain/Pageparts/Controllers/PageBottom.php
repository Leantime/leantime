<?php

namespace Leantime\Domain\Pageparts\Controllers {

    use Leantime\Core\AppSettings as AppSettingCore;
    use Leantime\Core\Controller;
    use Symfony\Component\HttpFoundation\Response;

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
         * @return Response
         * @throws \Exception
         */
        public function run(): Response
        {
            $this->tpl->assign("appSettings", $this->settings);
            return $this->tpl->displayPartial('pageparts.pageBottom');
        }
    }

}

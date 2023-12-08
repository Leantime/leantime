<?php

namespace Leantime\Domain\Pageparts\Controllers {

    use Leantime\Core\AppSettings as AppSettingCore;
    use Leantime\Core\Controller;
    use Symfony\Component\HttpFoundation\Response;

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
         * @return Response
         * @throws \Exception
         */
        public function run(): Response
        {
            $this->tpl->assign("version", $this->settings->appVersion);
            return $this->tpl->displayPartial('pageparts.footer');
        }
    }

}

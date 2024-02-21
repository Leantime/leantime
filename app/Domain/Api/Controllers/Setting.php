<?php

namespace Leantime\Domain\Api\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Setting\Services\Setting as SettingService;

    /**
     *
     */
    class Setting extends Controller
    {
        private SettingService $settingService;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(SettingService $settingService)
        {
            $this->settingService = $settingService;
        }

        /**
         * get - handle get requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {
        }

        /**
         * post - Updatind User Image
         *
         * @access public
         * @params parameters or body of the request
         */
        public function post($params)
        {
            if (! isset($_FILES['file'])) {
                return $this->tpl->displayJson(['status' => 'failure'], 500);
            }


            $_FILES['file']['name'] = "logo.png";

            $this->settingService->setLogo($_FILES);

            $_SESSION['msg'] = "PICTURE_CHANGED";
            $_SESSION['msgT'] = "success";

            return $this->tpl->displayJson(['status' => 'ok']);
        }

        /**
         * put - handle put requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function patch($params)
        {
        }

        /**
         * delete - handle delete requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function delete($params)
        {
        }
    }
}

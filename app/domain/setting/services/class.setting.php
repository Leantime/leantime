<?php

namespace leantime\domain\services {

    use leantime\core;
    use leantime\domain\repositories;

    class setting
    {
        private $userRepo;
        private $tpl;
        private repositories\setting $settingsRepo;

        public function __construct()
        {
            $this->tpl = new core\template();
            $this->settingsRepo = new repositories\setting();
        }

        public function setLogo($file)
        {

            $upload = new core\fileupload();

            $upload->initFile($file['file']);

            $newname = md5($_SESSION['userdata']['id'] . time());
            $upload->renameFile($newname);

            if ($upload->error == '') {
                $url = $upload->uploadPublic();

                if ($url !== false) {
                    $this->settingsRepo->saveSetting("companysettings.logoPath", $url);

                    if (strpos($url, 'http') === 0) {
                        $_SESSION["companysettings.logoPath"] = $url;
                    } else {
                        $_SESSION["companysettings.logoPath"] = BASE_URL . $url;
                    }

                    return true;
                }
            }
        }

        public function saveSetting($key, $value){
            return $this->settingsRepo->saveSetting($key, $value);
        }

        public function getSetting($key){
            return $this->settingsRepo->getSetting($key);
        }
    }

}

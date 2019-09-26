<?php

namespace leantime\domain\services {

    use leantime\core;
    use leantime\domain\repositories;

    class setting
    {

        private $userRepo;
        private $tpl;

        public function __construct()
        {
            $this->tpl = new core\template();
            $this->settingsRepo = new repositories\setting();

        }

        //GET


        //POST
        public function setLogo($file)
        {

            $upload = new core\fileupload();

            $upload->initFile($file['file']);

            $newname = md5($_SESSION['userdata']['id'].time());
            $upload->renameFile($newname);

            if ($upload->error == '') {

                $url = $upload->uploadPublic();

                if ($url!==false) {

                    $this->settingsRepo->saveSetting("companysettings.logoPath", $url);
                    $_SESSION["companysettings.logoPath"] = $url;

                    return true;

                }
            }


        }

    }

}

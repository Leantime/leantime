<?php

namespace Leantime\Domain\Setting\Services {

    use Leantime\Core\Template as TemplateCore;
use Leantime\Core\Fileupload as FileuploadCore;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
class Setting
    {
        private TemplateCore $tpl;
        public SettingRepository $settingsRepo;

        public function __construct(
            TemplateCore $tpl,
            SettingRepository $settingsRepo
        ) {
            $this->tpl = $tpl;
            $this->settingsRepo = $settingsRepo;
        }

        public function setLogo($file)
        {

            $upload = app()->make(FileuploadCore::class);

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

        public function resetLogo()
        {

            $url = '/dist/images/logo.svg';

            $this->settingsRepo->saveSetting("companysettings.logoPath", $url);

            $_SESSION["companysettings.logoPath"] = BASE_URL . $url;
        }

        public function saveSetting($key, $value)
        {
            return $this->settingsRepo->saveSetting($key, $value);
        }

        public function getSetting($key)
        {
            return $this->settingsRepo->getSetting($key);
        }

        /**
         * @return SettingRepository
         */
        public function getSettingsRepo(): SettingRepository
        {
            return $this->settingsRepo;
        }

        /**
         * @param SettingRepository $settingsRepo
         */
        public function setSettingsRepo(SettingRepository $settingsRepo): void
        {
            $this->settingsRepo = $settingsRepo;
        }
    }

}

<?php

namespace Leantime\Domain\Setting\Services {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Template as TemplateCore;
    use Leantime\Core\Fileupload as FileuploadCore;
    use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;

    /**
     *
     */
    class Setting
    {
        private TemplateCore $tpl;
        public SettingRepository $settingsRepo;

        /**
         * @param TemplateCore      $tpl
         * @param SettingRepository $settingsRepo
         */
        public function __construct(
            TemplateCore $tpl,
            SettingRepository $settingsRepo
        ) {
            $this->tpl = $tpl;
            $this->settingsRepo = $settingsRepo;
        }

        /**
         * @param $file
         * @return bool
         * @throws BindingResolutionException
         */
        public function setLogo($file): bool
        {

            $upload = app()->make(FileuploadCore::class);

            $upload->initFile($file['file']);

            $newname = md5($_SESSION['userdata']['id'] . time());
            $upload->renameFile($newname);

            if ($upload->error == '') {
                $url = $upload->uploadPublic();

                if ($url !== false) {
                    $this->settingsRepo->saveSetting("companysettings.logoPath", $url);

                    if (str_starts_with($url, 'http')) {
                        $_SESSION["companysettings.logoPath"] = $url;
                    } else {
                        $_SESSION["companysettings.logoPath"] = BASE_URL . $url;
                    }

                    return true;
                }
            }

            return false;
        }

        /**
         * @return void
         */
        public function resetLogo(): void
        {

            $url = '/dist/images/logo.svg';

            $this->settingsRepo->saveSetting("companysettings.logoPath", $url);

            $_SESSION["companysettings.logoPath"] = BASE_URL . $url;
        }

        /**
         * @param $key
         * @param $value
         * @return bool
         */
        /**
         * @param $key
         * @param $value
         * @return bool
         */
        public function saveSetting($key, $value): bool
        {
            return $this->settingsRepo->saveSetting($key, $value);
        }

        /**
         * @param $key
         * @return false|mixed
         */
        /**
         * @param $key
         * @return false|mixed
         */
        public function getSetting($key): mixed
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

<?php

namespace Leantime\Domain\Setting\Services {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Fileupload as FileuploadCore;
    use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
    use Ramsey\Uuid\Uuid;

    /**
     *
     *
     * @api
     */
    class Setting
    {
        /**
         * @param SettingRepository $settingsRepo
         *
     */
        public function __construct(
            public SettingRepository $settingsRepo,
        ) {
            //
        }

        /**
         * @param $file
         * @return bool
         * @throws BindingResolutionException
         *
     * @api
     */
        public function setLogo($file): bool
        {

            $upload = app()->make(FileuploadCore::class);

            $upload->initFile($file['file']);

            $newname = md5(session("userdata.id") . time());
            $upload->renameFile($newname);

            if ($upload->error == '') {
                $url = $upload->uploadPublic();

                if ($url !== false) {
                    $this->settingsRepo->saveSetting("companysettings.logoPath", $url);

                    if (str_starts_with($url, 'http')) {
                        session(["companysettings.logoPath" => $url]);
                    } else {
                        session(["companysettings.logoPath" => BASE_URL . $url]);
                    }

                    return true;
                }
            }

            return false;
        }

        /**
         * @return void
         *
     * @api
     */
        public function resetLogo(): void
        {

            $this->settingsRepo->deleteSetting("companysettings.logoPath");
            session()->forget("companysettings.logoPath");
            session(["companysettings.logoPath" => ""]);
        }

        /**
         * @param $key
         * @param $value
         * @return bool
         *
     * @api
     */
        public function saveSetting($key, $value): bool
        {
            return $this->settingsRepo->saveSetting($key, $value);
        }

        /**
         * @param $key
         * @return false|mixed
         *
     * @api
     */
        /**
         * @param $key
         * @return false|mixed
         *
     * @api
     */
        public function getSetting($key): mixed
        {
            return $this->settingsRepo->getSetting($key);
        }

        /**
         * @return SettingRepository
         *
     * @api
     */
        public function getSettingsRepo(): SettingRepository
        {
            return $this->settingsRepo;
        }

        /**
         * @param SettingRepository $settingsRepo
         *
     * @api
     */
        public function setSettingsRepo(SettingRepository $settingsRepo): void
        {
            $this->settingsRepo = $settingsRepo;
        }

        /**
         * Gets the company id (Sets if it's not set)
         *
         * @return string
         **/
        public function getCompanyId(): string
        {
            $companyId = $this->getSetting('companysettings.telemetry.anonymousId');

            if (! $companyId) {
                $companyId = Uuid::uuid4()->toString();
                $this->saveSetting('companysettings.telemetry.anonymousId', $companyId);
            }

            return $companyId;
        }
    }
}

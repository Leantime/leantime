<?php

namespace Leantime\Domain\Setting\Controllers {


    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Api\Services\Api as ApiService;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Reports\Services\Reports as ReportService;
    use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
    use Leantime\Domain\Setting\Services\Setting as SettingService;

    /**
     *
     */
    class EditCompanySettings extends Controller
    {
        private SettingRepository $settingsRepo;
        private ApiService $APIService;
        private SettingService $settingsSvc;

        /**
         * constructor - initialize private variables
         *
         * @access public
         *
         */
        public function init(
            SettingRepository $settingsRepo,
            ApiService $APIService,
            SettingService $settingsSvc
        ) {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

            $this->settingsRepo = $settingsRepo;
            $this->APIService = $APIService;
            $this->settingsSvc = $settingsSvc;
        }

        /**
         * get - handle get requests
         *
         * @access public
         *
         */
        public function get($params)
        {
            if (! Auth::userIsAtLeast(Roles::$owner)) {
                return $this->tpl->display('errors.error403', responseCode: 403);
            }

            if (isset($_GET['resetLogo'])) {
                $this->settingsSvc->resetLogo();
                return Frontcontroller::redirect(BASE_URL . "/setting/editCompanySettings#look");
            }

            $companySettings = array(
                "logo" => session("companysettings.logoPath") ?? '',
                "primarycolor" => session("companysettings.primarycolor") ?? '',
                "secondarycolor" => session("companysettings.secondarycolor") ?? '',
                "name" => session("companysettings.sitename"),
                "language" => session("companysettings.language"),
                "telemetryActive" => true,
                "messageFrequency" => '',
            );

            $logoPath = $this->settingsRepo->getSetting("companysettings.logoPath");
            if ($logoPath !== false) {
                if (str_starts_with($logoPath, 'http')) {
                    $companySettings["logo"] = $logoPath;
                } else {
                    $companySettings["logo"] = BASE_URL . $logoPath;
                }
            }

            $mainColor = $this->settingsRepo->getSetting("companysettings.mainColor");
            if ($mainColor !== false) {
                $companySettings["primarycolor"] = "#" . $mainColor;
                $companySettings["secondarycolor"] = "#" . $mainColor;
            }

            $primaryColor = $this->settingsRepo->getSetting("companysettings.primarycolor");
            if ($primaryColor !== false) {
                $companySettings["primarycolor"] = $primaryColor;
            }

            $secondaryColor = $this->settingsRepo->getSetting("companysettings.secondarycolor");
            if ($secondaryColor !== false) {
                $companySettings["secondarycolor"] = $secondaryColor;
            }

            $sitename = $this->settingsRepo->getSetting("companysettings.sitename");
            if ($sitename !== false) {
                $companySettings["name"] = $sitename;
            }

            $language = $this->settingsRepo->getSetting("companysettings.language");
            if ($language !== false) {
                $companySettings["language"] = $language;
            }

            $telemetryActive = $this->settingsRepo->getSetting("companysettings.telemetry.active");
            if ($telemetryActive !== false) {
                $companySettings["telemetryActive"] = $telemetryActive;
            }

            $messageFrequency = $this->settingsRepo->getSetting("companysettings.messageFrequency");
            if ($messageFrequency !== false) {
                $companySettings["messageFrequency"] = $messageFrequency;
            }

            $apiKeys = $this->APIService->getAPIKeys();

            $this->tpl->assign("apiKeys", $apiKeys);
            $this->tpl->assign("languageList", $this->language->getLanguageList());
            $this->tpl->assign("companySettings", $companySettings);

            return $this->tpl->display('setting.editCompanySettings');
        }

        /**
         * post - handle post requests
         *
         * @access public
         *
         */
        public function post($params)
        {
            //Look & feel updates
            if (isset($params['primarycolor']) && $params['primarycolor'] != "") {
                $this->settingsRepo->saveSetting("companysettings.primarycolor", htmlentities(addslashes($params['primarycolor'])));
                $this->settingsRepo->saveSetting("companysettings.secondarycolor", htmlentities(addslashes($params['secondarycolor'])));

                //Check if main color is still in the system
                //if so remove. This call should be removed in a few versions.
                $mainColor = $this->settingsRepo->getSetting("companysettings.mainColor");
                if ($mainColor !== false) {
                    $this->settingsRepo->deleteSetting("companysettings.mainColor");
                }

                session(["companysettings.primarycolor" => htmlentities(addslashes($params['primarycolor']))]);
                session(["companysettings.secondarycolor" => htmlentities(addslashes($params['secondarycolor']))]);

                $this->tpl->setNotification($this->language->__("notifications.company_settings_edited_successfully"), "success");
            }

            //Main Details
            if (isset($params['name']) && $params['name'] != "" && isset($params['language']) && $params['language'] != "") {
                $this->settingsRepo->saveSetting("companysettings.sitename", htmlspecialchars(addslashes($params['name'])));
                $this->settingsRepo->saveSetting("companysettings.language", htmlentities(addslashes($params['language'])));
                $this->settingsRepo->saveSetting("companysettings.messageFrequency", (int) $params['messageFrequency']);

                session(["companysettings.sitename" => htmlspecialchars(addslashes($params['name']))]);
                session(["companysettings.language" => htmlentities(addslashes($params['language']))]);

                if (isset($_POST['telemetryActive'])) {
                    $this->settingsRepo->saveSetting("companysettings.telemetry.active", "true");
                } else {
                    //Set remote telemetry to false:
                    app()->make(ReportService::class)->optOutTelemetry();
                }

                $this->tpl->setNotification($this->language->__("notifications.company_settings_edited_successfully"), "success");
            }

            return Frontcontroller::redirect(BASE_URL . "/setting/editCompanySettings");
        }

        /**
         * put - handle put requests
         *
         * @access public
         *
         */
        public function put($params)
        {
        }

        /**
         * delete - handle delete requests
         *
         * @access public
         *
         */
        public function delete($params)
        {
        }
    }
}

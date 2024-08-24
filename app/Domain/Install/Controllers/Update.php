<?php

namespace Leantime\Domain\Install\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Configuration\AppSettings as AppSettingCore;
    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;
    use Leantime\Domain\Install\Repositories\Install as InstallRepository;
    use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class Update extends Controller
    {
        private InstallRepository $installRepo;
        private SettingRepository $settingsRepo;
        private AppSettingCore $appSettings;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(
            InstallRepository $installRepo,
            SettingRepository $settingsRepo,
            AppSettingCore $appSettings
        ) {
            $this->installRepo = $installRepo;
            $this->settingsRepo = $settingsRepo;
            $this->appSettings = $appSettings;
        }

        /**
         * get - handle get requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {
            $dbVersion = $this->settingsRepo->getSetting("db-version");
            if ($this->appSettings->dbVersion == $dbVersion) {
                return FrontcontrollerCore::redirect(BASE_URL . "/auth/login");
            }

            $updatePage = self::dispatch_filter('customUpdatePage', 'install.update');
            return $this->tpl->display($updatePage, "entry");
        }

        /**
         * @param $params
         * @return Response
         * @throws BindingResolutionException
         */
        public function post($params): Response
        {
            if (isset($_POST['updateDB'])) {
                $success = $this->installRepo->updateDB();

                if (is_array($success) === true) {
                    foreach ($success as $errorMessage) {
                        $this->tpl->setNotification("There was a problem. Please reach out to support@leantime.io for assistance.", "error");
                        //report($errorMessage);
                    }
                    $this->tpl->setNotification("There was a problem updating your database. Please check your error logs to verify your database is up to date.", "error");
                    return FrontcontrollerCore::redirect(BASE_URL . "/install/update");
                }

                if ($success === true) {
                    return FrontcontrollerCore::redirect(BASE_URL);
                }
            }

            $this->tpl->setNotification("There was a problem. Please reach out to support@leantime.io for assistance.", "error");
            return FrontcontrollerCore::redirect(BASE_URL . "/install/update");
        }
    }
}

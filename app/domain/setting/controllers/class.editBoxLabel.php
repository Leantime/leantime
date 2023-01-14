<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    use DateTime;
    use DateInterval;
    use leantime\domain\services\auth;

    class editBoxLabel extends controller
    {

        private $ticketsRepo;
        private $settingsRepo;
        private $canvasRepo;
        private $retroRepo;
        private $ideaRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         *
         */
        public function init()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager]);

            $this->ticketsRepo = new repositories\tickets();
            $this->settingsRepo = new repositories\setting();
            $this->canvasRepo = new repositories\leancanvas();
            $this->retroRepo = new repositories\retroscanvas();
            $this->ideaRepo = new repositories\ideas();

        }

        /**
         * get - handle get requests
         *
         * @access public
         *
         */
        public function get($params)
        {

            if(auth::userIsAtLeast(roles::$manager)) {

                $currentLabel = "";

                if (isset($params['module']) && isset($params['label'])) {

                    //Move to settings service
                    if ($params['module'] == "ticketlabels") {
                        $stateLabels = $this->ticketsRepo->getStateLabels();
                        $currentLabel = $stateLabels[$params['label']]["name"];
                    }

                    if ($params['module'] == "retrolabels") {
                        $stateLabels = $this->retroRepo->getCanvasLabels();
                        $currentLabel = $stateLabels[$params['label']];
                    }

                    if ($params['module'] == "researchlabels") {
                        $stateLabels = $this->canvasRepo->getCanvasLabels();
                        $currentLabel = $stateLabels[$params['label']];
                    }

                    if ($params['module'] == "idealabels") {
                        $stateLabels = $this->ideaRepo->getCanvasLabels();
                        $currentLabel = $stateLabels[$params['label']]["name"];
                    }

                }

                $this->tpl->assign('currentLabel', $currentLabel);

                $this->tpl->displayPartial('setting.editBoxDialog');

            } else {
                $this->tpl->display('errors.error403');
            }
        }

        /**
         * post - handle post requests
         *
         * @access public
         *
         */
        public function post($params)
        {
            //If ID is set its an update

            if (isset($_GET['module']) && isset($_GET['label'])) {

                $sanitizedString = htmlentities($params['newLabel']);

                //Move to settings service
                if ($_GET['module'] == "ticketlabels") {

                    $currentStateLabels = $this->ticketsRepo->getStateLabels();

                    $statusKey = filter_var($_GET['label'], FILTER_SANITIZE_NUMBER_INT);

                    if(isset($currentStateLabels[$statusKey]) && is_array($currentStateLabels[$statusKey])){

                        $currentStateLabels[$statusKey]['name'] = $sanitizedString;

                        unset($_SESSION["projectsettings"]["ticketlabels"]);
                        $this->settingsRepo->saveSetting("projectsettings." . $_SESSION['currentProject'] . ".ticketlabels",
                            serialize($currentStateLabels));
                    }


                }

                if ($_GET['module'] == "retrolabels") {
                    $stateLabels = $this->retroRepo->getCanvasLabels();
                    $stateLabels[$_GET['label']] = $sanitizedString;
                    unset($_SESSION["projectsettings"]["retrolabels"]);
                    $this->settingsRepo->saveSetting("projectsettings." . $_SESSION['currentProject'] . ".retrolabels",
                        serialize($stateLabels));
                }

                if ($_GET['module'] == "researchlabels") {
                    $stateLabels = $this->canvasRepo->getCanvasLabels();
                    $stateLabels[$_GET['label']] = $sanitizedString;
                    unset($_SESSION["projectsettings"]["researchlabels"]);
                    $this->settingsRepo->saveSetting("projectsettings." . $_SESSION['currentProject'] . ".researchlabels",
                        serialize($stateLabels));
                }

                if ($_GET['module'] == "idealabels") {

                    $stateLabels = $this->ideaRepo->getCanvasLabels();
                    $newStateLabels = array();
                    foreach ($stateLabels as $key => $label) {
                        $newStateLabels[$key] = $label["name"];
                    }
                    $newStateLabels[$_GET['label']] = $sanitizedString;

                    unset($_SESSION["projectsettings"]["idealabels"]);
                    $this->settingsRepo->saveSetting("projectsettings." . $_SESSION['currentProject'] . ".idealabels",
                        serialize($newStateLabels));
                }

                $this->tpl->setNotification($this->language->__("notifications.label_changed_successfully"), "success");

            }

            $this->tpl->assign('currentLabel', $sanitizedString);
            $this->tpl->displayPartial('setting.editBoxDialog');

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

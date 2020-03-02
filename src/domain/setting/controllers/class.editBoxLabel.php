<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    use \DateTime;
    use \DateInterval;


    class editBoxLabel
    {

        private $tpl;
        private $projects;
        private $sprintService;

        /**
         * constructor - initialize private variables
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function __construct()
        {

            $this->tpl = new core\template();
            $this->ticketsRepo = new repositories\tickets();
            $this->settingsRepo = new repositories\setting();
            $this->canvasRepo = new repositories\leancanvas();
            $this->retroRepo = new repositories\retrospectives();
            $this->ideaRepo = new repositories\ideas();

        }

        /**
         * get - handle get requests
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function get($params)
        {

            if($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager') {

                $currentLabel = "";

                if(isset($params['module']) && isset($params['label'])) {

                    if($params['module'] == "ticketlabels") {
                        $stateLabels = $this->ticketsRepo->getStateLabels();
                        $currentLabel = $stateLabels[$params['label']];
                    }

                    if($params['module'] == "retrolabels") {
                            $stateLabels = $this->retroRepo->getCanvasLabels();
                            $currentLabel = $stateLabels[$params['label']];
                    }

                    if($params['module'] == "researchlabels") {
                            $stateLabels = $this->canvasRepo->getCanvasLabels();
                            $currentLabel = $stateLabels[$params['label']];
                    }

                    if($params['module'] == "idealabels") {
                        $stateLabels = $this->ideaRepo->getCanvasLabels();
                        $currentLabel = $stateLabels[$params['label']];
                    }

                }

                $this->tpl->assign('currentLabel', $currentLabel);

                $this->tpl->displayPartial('setting.editBoxDialog');

            }else{
                $this->tpl->display('general.error');
            }
        }

        /**
         * post - handle post requests
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function post($params)
        {
            //If ID is set its an update

                if (isset($_GET['module']) && isset($_GET['label'])) {

                    $sanatizedString = preg_replace("/[^a-zA-Z0-9 ]+/", '', $params['newLabel']);

                    if ($_GET['module'] == "ticketlabels") {
                        $stateLabels = $this->ticketsRepo->getStateLabels();
                        $stateLabels[$_GET['label']] = $sanatizedString;
                        unset($_SESSION["projectsettings"]["ticketlabels"]);
                        $this->settingsRepo->saveSetting("projectsettings." . $_SESSION['currentProject'] . ".ticketlabels", serialize($stateLabels));
                    }

                    if ($_GET['module'] == "retrolabels") {
                        $stateLabels = $this->retroRepo->getCanvasLabels();
                        $stateLabels[$_GET['label']] = $sanatizedString;
                        unset($_SESSION["projectsettings"]["retrolabels"]);
                        $this->settingsRepo->saveSetting("projectsettings." . $_SESSION['currentProject'] . ".retrolabels", serialize($stateLabels));
                    }

                    if ($_GET['module'] == "researchlabels") {
                        $stateLabels = $this->canvasRepo->getCanvasLabels();
                        $stateLabels[$_GET['label']] = $sanatizedString;
                        unset($_SESSION["projectsettings"]["researchlabels"]);
                        $this->settingsRepo->saveSetting("projectsettings." . $_SESSION['currentProject'] . ".researchlabels", serialize($stateLabels));
                    }

                    if ($_GET['module'] == "idealabels") {
                        $stateLabels = $this->ideaRepo->getCanvasLabels();
                        $stateLabels[$_GET['label']] = $sanatizedString;
                        unset($_SESSION["projectsettings"]["idealabels"]);
                        $this->settingsRepo->saveSetting("projectsettings." . $_SESSION['currentProject'] . ".idealabels", serialize($stateLabels));
                    }

                    $this->tpl->setNotification("Label changed successfully", "success");

                }

                $this->tpl->assign('currentLabel', $sanatizedString);
                $this->tpl->displayPartial('setting.editBoxDialog');

        }

        /**
         * put - handle put requests
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function put($params)
        {

        }

        /**
         * delete - handle delete requests
         *
         * @access public
         * @param  paramters or body of the request
         */
        public function delete($params)
        {

        }

    }

}
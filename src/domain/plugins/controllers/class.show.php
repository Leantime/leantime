<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\services\auth;
    use leantime\domain\models\auth\roles;

    class show extends controller
    {

        private $dashboardRepo;
        private $projectService;
        private $sprintService;
        private $ticketService;
        private $userService;
        private $timesheetService;
        private $reportService;


        public function init()
        {
            auth::authOrRedirect([roles::$owner, roles::$admin]);
            $this->pluginService = new services\plugins();
        }

        /**
         * @return void
         */
        public function get()
        {

            if(isset($_GET['install']) && $_GET['install'] != ''){
               $result = $this->pluginService->installPlugin($_GET['install']);
               if($result) {
                   $this->tpl->setNotification("notification.plugin_install_success", "success");
               }else{
                   $this->tpl->setNotification("notification.plugin_install_error", "error");
               }

               $this->tpl->redirect(BASE_URL."/plugins/show");
            }

            if(isset($_GET['enable']) && $_GET['enable'] != ''){
                $result = $this->pluginService->enablePlugin((int) $_GET['enable']);
                if($result) {
                    $this->tpl->setNotification("notification.plugin_activation_success", "success");
                }else{
                    $this->tpl->setNotification("notification.plugin_activation_error", "error");
                }

                $this->tpl->redirect(BASE_URL."/plugins/show");
            }

            if(isset($_GET['disable']) && $_GET['disable'] != ''){
                $result = $this->pluginService->disablePlugin((int) $_GET['disable']);
                if($result) {
                    $this->tpl->setNotification("notification.plugin_disable_success", "success");
                }else{
                    $this->tpl->setNotification("notification.plugin_disable_error", "error");
                }

                $this->tpl->redirect(BASE_URL."/plugins/show");
            }

            if(isset($_GET['remove']) && $_GET['remove'] != ''){
                $result = $this->pluginService->removePlugin((int) $_GET['remove']);
                if($result) {
                    $this->tpl->setNotification("notification.plugin_remove_success", "success");
                }else{
                    $this->tpl->setNotification("notification.plugin_remove_error", "error");
                }

                $this->tpl->redirect(BASE_URL."/plugins/show");
            }

            $newPlugins = $this->pluginService->discoverNewPlugins();
            $installedPlugins =$this->pluginService->getAllPlugins();

            $this->tpl->assign("newPlugins", $newPlugins);
            $this->tpl->assign("installedPlugins", $installedPlugins);
            $this->tpl->display("plugins.show");

        }

        public function post($params)
        {

            $this->tpl->redirect(BASE_URL . "/plugins/show");

        }

    }
}

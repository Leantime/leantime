<?php
namespace leantime\plugins\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories\goalcanvas;
    use leantime\domain\services\comments;
    use leantime\domain\services\tickets;
    use leantime\plugins\services\llamadorian;

    class statusCollector extends controller
    {

        private llamadorian $llamadorianSvc;
        private comments $commentsSvc;
        private tickets $ticketSvc;

        public function init()
        {
            $this->llamadorianSvc = new llamadorian();
            $this->commentsSvc = new comments();
            $this->ticketSvc = new tickets();

        }

        /**
         * @return void
         */
        public function get()
        {

            $projectsDue = $this->llamadorianSvc->getProjectsWithUpdatesDue($_SESSION['userdata']['id']);

            $items = $this->llamadorianSvc->getEntitiesForUpdates($projectsDue, $_SESSION['userdata']['id']);

            $this->tpl->assign("items", $items);
            $this->tpl->display("llamadorian.statusCollector");
        }

        public function post ($params) {

            // Manage Post comment

            if (isset($_POST['text']) === true && isset($_POST['moduleId']) === true && isset($_POST['module']) === true) {

                if($_POST['module'] == 'ticket') {
                    $entity = $this->ticketSvc->getTicket($_POST['moduleId']);
                    $module = "ticket";
                }else{
                    $canvasString = $_POST['module'].'canvasitem';
                    $canvasSvc = new $canvasString();
                    $module = $canvasString;
                    $entity = $canvasSvc->getSingleCanvasItem($_POST['moduleId']);
                }

                if ($this->commentsSvc->addComment($_POST, $module, $_POST['moduleId'], $entity)) {
                    $this->tpl->setNotification($this->language->__("notifications.comment_create_success"), "success");
                } else {
                    $this->tpl->setNotification($this->language->__("notifications.comment_create_error"), "error");
                }
            }

            $this->tpl->redirect(BASE_URL."/llamadorian/statusCollector");

        }
    }
}

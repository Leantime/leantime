<?php


namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class delWiki extends controller
    {

        private $wikiRepo;

        /**
         * init - init
         *
         * @access public
         */
        public function init()
        {

            $this->wikiRepo = new repositories\wiki();

        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);

            if (isset($_GET['id'])) {
                $id = (int)($_GET['id']);
            }

            if (isset($_POST['del']) && isset($id)) {

                $this->wikiRepo->delWiki($id);

                unset($_SESSION['currentIdeaCanvas']);
                $this->tpl->setNotification($this->language->__("notification.wiki_deleted"), "success");
                $this->tpl->redirect(BASE_URL."/wiki/show");

            }

            $this->tpl->displayPartial('wiki.delWiki');

        }

    }
}

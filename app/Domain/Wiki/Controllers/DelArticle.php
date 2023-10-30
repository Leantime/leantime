<?php

namespace Leantime\Domain\Wiki\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Wiki\Repositories\Wiki as WikiRepository;
    use Leantime\Domain\Auth\Services\Auth;

    /**
     *
     */
    class DelArticle extends Controller
    {
        private WikiRepository $wikiRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(WikiRepository $wikiRepo)
        {
            $this->wikiRepo = $wikiRepo;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

            if (isset($_GET['id'])) {
                $id = (int)($_GET['id']);
            }

            if (isset($_POST['del']) && isset($id)) {
                $this->wikiRepo->delArticle($id);

                $this->tpl->setNotification($this->language->__("notification.article_deleted"), "success", "article_deleted");

                $this->tpl->redirect(BASE_URL . "/wiki/show");
            }

            $this->tpl->displayPartial('wiki.delArticle');
        }
    }

}

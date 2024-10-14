<?php

namespace Leantime\Domain\Wiki\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Wiki\Repositories\Wiki as WikiRepository;

    class DelArticle extends Controller
    {
        private WikiRepository $wikiRepo;

        /**
         * init - initialize private variables
         */
        public function init(WikiRepository $wikiRepo)
        {
            $this->wikiRepo = $wikiRepo;
        }

        /**
         * run - display template and edit data
         */
        public function run()
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

            if (isset($_GET['id'])) {
                $id = (int) ($_GET['id']);
            }

            if (isset($_POST['del']) && isset($id)) {
                $this->wikiRepo->delArticle($id);

                $this->tpl->setNotification($this->language->__('notification.article_deleted'), 'success', 'article_deleted');

                $this->tpl->closeModal();
                $this->tpl->htmxRefresh();

                return $this->tpl->emptyResponse();
            }

            return $this->tpl->displayPartial('wiki::partials.delArticle');
        }
    }
}

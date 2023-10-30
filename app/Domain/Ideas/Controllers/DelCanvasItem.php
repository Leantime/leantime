<?php

namespace Leantime\Domain\Ideas\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
    use Leantime\Domain\Auth\Services\Auth;

    /**
     *
     */
    class DelCanvasItem extends Controller
    {
        private IdeaRepository $ideasRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(IdeaRepository $ideasRepo)
        {
            $this->ideasRepo = $ideasRepo;
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
                $this->ideasRepo->delCanvasItem($id);

                $this->tpl->setNotification($this->language->__("notification.idea_board_item_deleted"), "success", "ideaitem_deleted");

                $this->tpl->redirect(BASE_URL . "/ideas/showBoards");
            }

            $this->tpl->displayPartial('ideas.delCanvasItem');
        }
    }

}

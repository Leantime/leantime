<?php

namespace Leantime\Domain\Ideas\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;

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

                return Frontcontroller::redirect(BASE_URL . "/ideas/showBoards");
            }

            return $this->tpl->displayPartial('ideas.delCanvasItem');
        }
    }

}

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
    class DelCanvas extends Controller
    {
        private IdeaRepository $ideaRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(IdeaRepository $ideaRepo)
        {
            $this->ideaRepo = $ideaRepo;
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
                $this->ideaRepo->deleteCanvas($id);

                session()->forget("currentIdeaCanvas");
                $this->tpl->setNotification($this->language->__("notification.idea_board_deleted"), "success", "ideaboard_deleted");
                return Frontcontroller::redirect(BASE_URL . "/ideas/showBoards");
            }

            return $this->tpl->display('ideas.delCanvas');
        }
    }
}

<?php

namespace Leantime\Domain\Sprints\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Sprints\Repositories\Sprints as SprintRepository;
    use Leantime\Domain\Auth\Services\Auth;

    class DelSprint extends Controller
    {
        private SprintRepository $sprintRepo;

        /**
         * init - initialize private variables
         *
         * @access private
         */
        public function init(SprintRepository $sprintRepo)
        {
            $this->sprintRepo = $sprintRepo;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

            //Only admins
            if (Auth::userIsAtLeast(Roles::$editor)) {
                if (isset($_GET['id'])) {
                    $id = (int)($_GET['id']);
                }

                if (isset($_POST['del'])) {
                    $this->sprintRepo->delSprint($id);

                    $this->tpl->setNotification($this->language->__('notifications.sprint_deleted_successfully'), "success");

                    $_SESSION["currentSprint"] = "";

                    if (isset($_SESSION['lastPage'])) {
                        $this->tpl->redirect($_SESSION['lastPage']);
                    } else {
                        $this->tpl->redirect(BASE_URL . "/tickets/showKanban");
                    }
                }

                $this->tpl->assign('id', $id);
                $this->tpl->displayPartial('sprints.delSprint');
            } else {
                $this->tpl->displayPartial('errors.error403');
            }
        }
    }

}

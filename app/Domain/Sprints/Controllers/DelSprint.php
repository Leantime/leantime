<?php

namespace Leantime\Domain\Sprints\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Sprints\Repositories\Sprints as SprintRepository;

    /**
     *
     */
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
        public function run($params)
        {

            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

            //Only admins
            if (Auth::userIsAtLeast(Roles::$editor)) {

                if (isset($params['id'])) {
                    $id = (int)($params['id']);
                }

                if (isset($_POST['del'])) {
                    $this->sprintRepo->delSprint($id);

                    $this->tpl->setNotification($this->language->__('notifications.sprint_deleted_successfully'), "success");

                    session(["currentSprint" => ""]);

                    $this->tpl->closeModal();
                    $this->tpl->htmxRefresh();

                    return $this->tpl->emptyResponse();
                }

                $this->tpl->assign('id', $id);
                return $this->tpl->displayPartial('sprints::partials.delSprint');
            } else {
                return $this->tpl->displayPartial('errors.error403', responseCode: 403);
            }
        }
    }
}

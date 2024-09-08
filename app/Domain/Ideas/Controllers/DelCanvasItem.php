<?php

namespace Leantime\Domain\Ideas\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Core\Controller\Frontcontroller;

    use Symfony\Component\HttpFoundation\Response;
    use Leantime\Domain\Ideas\Services\Ideas as IdeaService;

    /**
     *
     */
    class DelCanvasItem extends Controller
    {
        private IdeaService $ideaService;
        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(
            IdeaService $ideaService
        ) {
            $this->ideaService = $ideaService;
        }


        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function get(): Response
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

            return $this->tpl->displayPartial('ideas::partials.delCanvasItem');
        }

        public function post($params): Response
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

            $result = $this->ideaService->deleteCanvasItem($params);

            if ($result) {
                $this->tpl->setNotification(
                    $this->language->__("notification.idea_board_item_deleted"),
                    "success",
                    "ideaitem_deleted"
                );
            } else {
                $this->tpl->setNotification(
                    $this->language->__("notification.deletion_failed"),
                    "error"
                );
            }

            $this->tpl->closeModal();
            $this->tpl->htmxRefresh();

            return $this->tpl->emptyResponse();
        }
    }

}

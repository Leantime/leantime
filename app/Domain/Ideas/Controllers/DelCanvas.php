<?php

namespace Leantime\Domain\Ideas\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
    use Leantime\Domain\Ideas\Services\Ideas as IdeaService;
    use Symfony\Component\HttpFoundation\Response;

    class DelCanvas extends Controller
    {
        private IdeaRepository $ideaRepo;

        private IdeaService $ideaService;

        /**
         * init - initialize private variables
         */
        public function init(IdeaService $ideaService)
        {
            $this->ideaService = $ideaService;
        }

        public function get($params): Response
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

            return $this->tpl->display('ideas.delCanvas');
        }

        public function post($params): Response
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

            $result = $this->ideaService->deleteCanvas($params);

            if ($result) {
                $this->tpl->setNotification(
                    $this->language->__('notification.idea_board_deleted'),
                    'success',
                    'ideaboard_deleted'
                );
            } else {
                $this->tpl->setNotification(
                    $this->language->__('notification.deletion_failed'),
                    'error'
                );
            }

            $this->tpl->closeModal();
            $this->tpl->htmxRefresh();

            return $this->tpl->emptyResponse();
        }
    }
}

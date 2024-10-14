<?php

namespace Leantime\Domain\Ideas\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
    use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
    use Leantime\Domain\Ideas\Services\Ideas as IdeaService;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Symfony\Component\HttpFoundation\Response;

    class IdeaDialog extends Controller
    {
        private IdeaRepository $ideaRepo;

        private CommentRepository $commentsRepo;

        private TicketService $ticketService;

        private ProjectService $projectService;

        private IdeaService $ideaService;

        /**
         * init - initialize private variables
         */
        public function init(
            IdeaRepository $ideaRepo,
            CommentRepository $commentsRepo,
            TicketService $ticketService,
            ProjectService $projectService,
            IdeaService $ideaService
        ) {
            $this->ideaRepo = $ideaRepo;
            $this->commentsRepo = $commentsRepo;
            $this->ticketService = $ticketService;
            $this->projectService = $projectService;
            $this->ideaService = $ideaService;
        }

        /**
         * get - handle get requests
         */
        public function get($params): Response
        {
            $result = $this->ideaService->processIdeaDialogGetRequest($params);
            $allCanvas = $this->ideaRepo->getAllCanvas(session('currentProject'));
            if (session()->exists('currentIdeaCanvas')) {
                $currentCanvasId = session('currentIdeaCanvas');
            } else {
                $this->tpl->setNotification('Currenct canvas Id is missing', $result['notification']['type'], $result['notification']['key'] ?? null);

                return Frontcontroller::redirect(BASE_URL.'/ideas/'.session('lastIdeaView'));
            }

            if (isset($result['notification'])) {
                $this->tpl->setNotification($result['notification']['message'], $result['notification']['type'], $result['notification']['key'] ?? null);
            }

            $this->tpl->assign('id', '');
            $this->tpl->assign('currentCanvas', $currentCanvasId);
            $this->tpl->assign('comments', $result['comments']);
            $this->tpl->assign('numComments', $result['numComments'] ?? 0);
            $this->tpl->assign('milestones', $result['milestones']);
            $this->tpl->assign('canvasTypes', $result['canvasTypes']);
            $this->tpl->assign('canvasItem', $result['canvasItem']);

            return $this->tpl->displayPartial('ideas::partials.ideaDialog');
        }

        /**
         * post - handle post requests
         */
        public function post($params): Response
        {
            $result = $this->ideaService->processPostRequest($params);
            if (isset($result['notification'])) {
                $this->tpl->setNotification($result['notification']['message'], $result['notification']['type'], $result['notification']['key'] ?? '');
            }

            if (isset($result['redirect'])) {
                return Frontcontroller::redirect($result['redirect']);
            }


            $this->tpl->assign('canvasTypes', $result['canvasTypes']??'');
            $this->tpl->assign('canvasItem', $result['canvasItem']??'');

            $this->tpl->closeModal();
            $this->tpl->htmxRefresh();

            return $this->tpl->emptyResponse();
        }

        /**
         * put - handle put requests
         */
        public function put($params) {}

        /**
         * delete - handle delete requests
         */
        public function delete($params) {}
    }

}

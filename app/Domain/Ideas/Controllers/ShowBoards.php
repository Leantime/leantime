<?php

namespace Leantime\Domain\Ideas\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
    use Leantime\Domain\Ideas\Services\Ideas as IdeasService;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Symfony\Component\HttpFoundation\Response;

    class ShowBoards extends Controller
    {
        private IdeaRepository $ideaRepo;

        private ProjectService $projectService;

        private IdeasService $ideasService;

        /**
         * init - initialize private variables
         */
        public function init(IdeaRepository $ideaRepo, ProjectService $projectService, IdeasService $ideasService)
        {
            $this->ideaRepo = $ideaRepo;
            $this->projectService = $projectService;
            $this->ideasService = $ideasService;

            session(['lastPage' => CURRENT_URL]);
            session(['lastIdeaView' => 'board']);
        }

        public function get($params): Response
        {
            $result = $this->ideasService->handleShowBoardGetRequest($params);
            $this->assignTemplateVariables($result);

            return $this->tpl->display('ideas.showBoards');
        }

        public function post($params): Response
        {
            $result = $this->ideasService->handleShowboardPostRequest($params);
            if (isset($result['notification'])) {
                $this->tpl->setNotification(
                    $result['notification']['message'],
                    $result['notification']['type'],
                    $result['notification']['key'] ?? null
                );
            }

            if (isset($result['redirect'])) {
                return Frontcontroller::redirect(BASE_URL.'ideas/showBoards');
            }

            return $this->tpl->display($result['template'] ?? 'ideas.showBoards');
            // if (!isset($_GET["raw"])) {
            // }
        }

        /**
         * run - display template and edit data
         */
        private function assignTemplateVariables($result)
        {
            $this->tpl->assign('currentCanvas', $result['currentCanvasId']);
            $this->tpl->assign('canvasLabels', $result['canvasLabels']);
            $this->tpl->assign('allCanvas', $result['allCanvas']);
            $this->tpl->assign('canvasItems', $result['canvasItems']);
            $this->tpl->assign('users', $result['users']);
        }
    }

}

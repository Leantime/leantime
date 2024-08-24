<?php

/**
 * showCanvas class - Generic canvas controller
 */

namespace Leantime\Domain\Ideas\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Core\Mailer as MailerCore;
    use Leantime\Domain\Ideas\Repositories\Ideas;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
    use Leantime\Domain\Canvas\Services\Canvas as CanvaService;
    use Illuminate\Support\Str;
    use Symfony\Component\HttpFoundation\Response;
    use Leantime\Domain\Ideas\Services\Ideas as IdeasService;

    /**
     *
     */
    class BoardDialog extends Controller
    {
        private ProjectService $projectService;

        private IdeasService $ideasService;

        /**
         * init - initialize private variables
         */
        public function init(
            ProjectService $projectService,
            IdeaService $ideaService,
        ) {
            $this->ideasService = $ideaService;
            $this->projectService = $projectService;
        }

        public function get($params): Response
        {
            $data = $this->ideasService->prepareCanvasData($params['id'] ?? null);
            $this->assignTemplateVariables($data);

            return $this->tpl->displayPartial('ideas.boardDialog');
        }

        public function post($params): Response
        {
            if (isset($params['newCanvas'])) {
                $result = $this->ideasService->createNewCanvas($params);
                if ($result['success']) {
                    $this->tpl->setNotification($this->language->__('notification.board_created'), 'success', static::CANVAS_NAME . "board_created");
                    return Frontcontroller::redirect(BASE_URL . '/ideas/boardDialog/' . $result['canvasId']);
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                }
            }

            if (isset($params['editCanvas'])) {

                $result = [];
                if (!empty($params['id'])) {
                    $currentCanvasId = (int)$params['id'];
                    $result = $this->ideasService->editCanvas($params, $currentCanvasId);
                }

                if (!empty($result['success'])) {
                    $this->tpl->setNotification($this->language->__('notification.board_edited'), 'success');
                    return Frontcontroller::redirect(BASE_URL . '/ideas/boardDialog/' . $result['canvasId']);
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                }
            }

            $data = $this->ideasService->prepareCanvasData();
            $this->assignTemplateVariables($data);

            return $this->tpl->displayPartial('ideas.boardDialog');

            if (!isset($params['raw'])) {
                return $this->tpl->displayPartial('ideas.boardDialog');
            }
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        private function assignTemplateVariables($data)
        {
            $this->tpl->assign('canvasTitle', $data['canvasTitle']);
            $this->tpl->assign('currentCanvas', $data['currentCanvasId']);
            $this->tpl->assign('canvasname', "idea");
            $this->tpl->assign('users', $data['users']);
        }
    }
}

<?php

/**
 * showCanvas class - Generic canvas controller
 */

namespace Leantime\Domain\Ideas\Controllers {

    use Leantime\Core\Mailer as MailerCore;
    use Leantime\Core\Controller;
    use Leantime\Domain\Ideas\Repositories\Ideas;
    use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Canvas\Services\Canvas as CanvaService;
    use Illuminate\Support\Str;
    use Leantime\Core\Frontcontroller;
    use Symfony\Component\HttpFoundation\Response;
    use Leantime\Domain\Ideas\Services\Ideas as IdeasService;

    /**
     *
     */
    class BoardDialog extends Controller
    {
        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = '??';

        private ProjectService $projectService;
        private object $canvasRepo;

        private IdeasService $ideasService;

        /**
         * init - initialize private variables
         */
        public function init(ProjectService $projectService)
        {
            $this->ideasService = app()->make(IdeasService::class);
            $this->projectService = $projectService;
            $canvasName = "Ideas";
            $this->canvasRepo = app()->make(Ideas::class);
        }

        public function get($params): Response
        {
            $data = $this->ideasService->prepareCanvasData($params['id'] ?? null);
            $this->assignTemplateVariables($data);

            // if (!isset($params['raw'])) {
            //     return $this->tpl->displayPartial('ideas.boardDialog');
            // }

            return $this->tpl->displayPartial('ideas.boardDialog');
            // if (!isset($params['raw'])) {
            // }
        }

        public function post($params):Response
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
            
            if(!empty($params['id'])){
                $currentCanvasId = (int)$params['id'];
            }
            $result = $this->ideasService->editCanvas($params,$currentCanvasId);
            if ($result['success']) {
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

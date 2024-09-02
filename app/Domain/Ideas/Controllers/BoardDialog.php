<?php

/**
 * showCanvas class - Generic canvas controller
 */

namespace Leantime\Domain\Ideas\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Symfony\Component\HttpFoundation\Response;
    use Leantime\Domain\Ideas\Services\Ideas as IdeasService;

    /**
     *
     */
    class BoardDialog extends Controller
    {
        private IdeasService $ideasService;

        /**
         * init - initialize private variables
         */
        public function init()
        {
            $this->ideasService = app()->make(IdeasService::class);
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
                    $this->tpl->setNotification($this->language->__('notification.board_created'), 'success', "board_created");
                    return Frontcontroller::redirect(BASE_URL . '/ideas/boardDialog/' . $result['canvasId']);
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');
                }
            }

            if (isset($params['editCanvas'])) {

                if (!empty($params['id'])) {
                    $currentCanvasId = (int)$params['id'];
                }
                $result = $this->ideasService->editCanvas($params, $currentCanvasId);
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

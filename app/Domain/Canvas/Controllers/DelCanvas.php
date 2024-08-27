<?php

/**
 * delCanvas class - Generic canvas controller / Delete Canvas
 */

namespace Leantime\Domain\Canvas\Controllers {

    use Illuminate\Support\Str;
    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;

    /**
     *
     */
    class DelCanvas extends Controller
    {
        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = '??';

        private mixed $canvasRepo;

        /**
         * init - initialize private variables
         */
        public function init()
        {
            $canvasName = Str::studly(static::CANVAS_NAME) . 'canvas';
            $repoName = app()->getNamespace() . "Domain\\$canvasName\\Repositories\\$canvasName";
            $this->canvasRepo = app()->make($repoName);
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

            if (isset($_POST['del']) && isset($_GET['id'])) {
                $id = (int)($_GET['id']);
                $this->canvasRepo->deleteCanvas($id);

                $allCanvas = $this->canvasRepo->getAllCanvas(session("currentProject"));
                session(['current' . strtoupper(static::CANVAS_NAME) . 'Canvas' => $allCanvas[0]['id'] ?? -1]);

                $this->tpl->setNotification($this->language->__('notification.board_deleted'), 'success', strtoupper(static::CANVAS_NAME) . 'canvas_deleted');

                $allCanvas = $this->canvasRepo->getAllCanvas(session("currentProject"));

                //Create default canvas.
                if (!$allCanvas || count($allCanvas) == 0) {
                    return Frontcontroller::redirect(BASE_URL . '/strategy/showBoards');
                } else {
                    return Frontcontroller::redirect(BASE_URL . '/' . static::CANVAS_NAME . 'canvas/showCanvas');
                }
            }

            return $this->tpl->displayPartial(static::CANVAS_NAME . 'canvas.delCanvas');
        }
    }
}

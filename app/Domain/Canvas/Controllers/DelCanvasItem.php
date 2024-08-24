<?php

/**
 * delCanvasItem class - Generic canvas controller / Delete Canvas Item
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
    class DelCanvasItem extends Controller
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
                $this->canvasRepo->delCanvasItem($id);

                $this->tpl->setNotification($this->language->__('notification.element_deleted'), 'success', strtoupper(static::CANVAS_NAME) . 'canvasitem_deleted');
                return Frontcontroller::redirect(BASE_URL . '/' . static::CANVAS_NAME . 'canvas/showCanvas');
            }

            return $this->tpl->displayPartial(static::CANVAS_NAME . 'canvas.delCanvasItem');
        }
    }
}

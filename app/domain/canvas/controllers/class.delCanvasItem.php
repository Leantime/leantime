<?php

/**
 * delCanvasItem class - Generic canvas controller / Delete Canvas Item
 */

namespace leantime\domain\controllers\canvas {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class delCanvasItem extends controller
    {
        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = '??';

        private $canvasRepo;

        /**
         * init - initialize private variables
         */
        public function init()
        {
            $canvasRepoName = "leantime\\domain\\repositories\\" . static::CANVAS_NAME . 'canvas';
            $this->canvasRepo = new $canvasRepoName();
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);

            if (isset($_POST['del']) && isset($_GET['id'])) {
                $id = (int)($_GET['id']);
                $this->canvasRepo->delCanvasItem($id);

                $this->tpl->setNotification($this->language->__('notification.element_deleted'), 'success');
                $this->tpl->redirect(BASE_URL . '/' . static::CANVAS_NAME . 'canvas/showCanvas');
            }

            $this->tpl->displayPartial(static::CANVAS_NAME . 'canvas.delCanvasItem');
        }
    }

}

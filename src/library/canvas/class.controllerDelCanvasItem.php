<?php
/**
 * Generic / Template of canvas controller / Delete Canvas Item
 */
namespace leantime\library\canvas {

    use leantime\core;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class controllerDelCanvasItem
    {

        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = 'xx';
        protected const CANVAS_TEMPLATE = '';

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);

            $tpl = new core\template();
            $canvasRepoName = "leantime\\domain\\repositories\\".static::CANVAS_NAME.'canvas';
            $canvasRepo = new $canvasRepoName();
            $language = new core\language();

            if (isset($_GET['id'])) {
                $id = (int)($_GET['id']);
            }
            
            $canvasTemplate = ($_SESSION[self::CANVAS_NAME]['template'] ?? static::CANVAS_TEMPLATE).static::CANVAS_NAME;

            if (isset($_POST['del']) && isset($id)) {

                $canvasRepo->delCanvasItem($id);

                $tpl->setNotification($language->__("notification.element_deleted"), "success");
                $tpl->redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas/'.$canvasTemplate.'Canvas');

            }
            
            $tpl->assign('canvasTemplate', $canvasTemplate);
            $tpl->displayPartial(static::CANVAS_NAME.'canvas.delCanvasItem');

        }

    }

}

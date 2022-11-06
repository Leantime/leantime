<?php
/**
 * delCanvas class - Generic canvas controller / Delete Canvas
 */
namespace leantime\domain\controllers\canvas {
    
    use leantime\core;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;
    
    class delCanvas
    {
        
        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = '??';
        
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

            if(isset($_POST['del']) && isset($_GET['id'])) {

                $id = (int)($_GET['id']);
                $canvasRepo->deleteCanvas($id);
                
                $allCanvas = $canvasRepo->getAllCanvas($_SESSION['currentProject']);
                $_SESSION['current'.strtoupper(static::CANVAS_NAME).'Canvas'] = $allCanvas[0]['id'] ?? -1;

                $tpl->setNotification($language->__('notification.board_deleted'), 'success');
                $tpl->redirect(BASE_URL.'/'.static::CANVAS_NAME.'canvas/showCanvas');

            }
            
            $tpl->display(static::CANVAS_NAME.'canvas.delCanvas');

        }

    }
}

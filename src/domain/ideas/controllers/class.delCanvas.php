<?php


namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class delCanvas
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $leancanvasRepo = new repositories\leancanvas();


            if (isset($_GET['id'])) {
                $id = (int)($_GET['id']);
            }

            $msgKey = '';

            if (isset($_POST['del']) && isset($id)) {

                $leancanvasRepo->deleteCanvas($id);

                $_SESSION["msg"] = "CANVAS_DELETED";
                $_SESSION["msgT"] = "success";
                header("Location: /leancanvas/showCanvas/");

            }

            $tpl->display('leancanvas.delCanvasItem');

        }

    }
}

<?php

namespace leantime\domain\controllers {

    use leantime\core;

    class error404
    {

        public function run()
        {

            $tpl = new core\template();

            core\frontcontroller::setResponseCode(404);
            $tpl->display('errors.error404');
        }
    }
}

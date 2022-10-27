<?php

namespace leantime\domain\controllers {

    use leantime\core;

    class error500
    {

        public function run()
        {

            $tpl = new core\template();

            core\frontcontroller::setResponseCode(500);
            $tpl->display('errors.error500');
        }
    }
}

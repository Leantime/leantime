<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;

    class error500 extends controller
    {

        public function run()
        {

            core\frontcontroller::setResponseCode(500);
            $this->tpl->display('errors.error500');

        }

    }

}

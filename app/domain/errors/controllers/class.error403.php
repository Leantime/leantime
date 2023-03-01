<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;

    class error403 extends controller
    {
        public function run()
        {

            core\frontcontroller::setResponseCode(403);
            $this->tpl->display('errors.error403');
        }
    }
}

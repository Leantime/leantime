<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;

    class error404 extends controller
    {

        public function run()
        {

            core\frontcontroller::setResponseCode(404);
            $this->tpl->display('errors.error404');

        }

    }

}

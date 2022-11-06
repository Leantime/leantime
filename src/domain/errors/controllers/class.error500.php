<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\base\controller;

    class error500
    {

        public function run()
        {

            core\frontcontroller::setResponseCode(500);
            $this->tpl->display('errors.error500');

        }

    }

}

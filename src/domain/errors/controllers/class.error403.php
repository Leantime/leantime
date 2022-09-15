<?php

namespace leantime\domain\controllers {

    use leantime\core;

    class error403
    {

        public function run()
        {

            $tpl = new core\template();

            $tpl->display('errors.error403');
        }
    }
}

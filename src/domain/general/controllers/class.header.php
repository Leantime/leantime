<?php

namespace leantime\domain\controllers {

    use leantime\core;

    class header
    {


        public function run()
        {

            $tpl = new core\template();

            $login = new core\login(core\session::getSID());

            $tpl->assign('login', $login);

            $tpl->displayPartial('general.header');
        }
    }
}

<?php

namespace leantime\domain\controllers {

    use leantime\core;

    class header
    {

        public function run()
        {

            $tpl = new core\template();

            $tpl->displayPartial('general.header');
        }
    }
}

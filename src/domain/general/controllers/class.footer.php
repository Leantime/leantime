<?php

namespace leantime\domain\controllers {

    use leantime\core;

    class footer
    {

        public function run()
        {
            $tpl = new core\template();
            $tpl->displayPartial('general.footer');
        }
    }
}

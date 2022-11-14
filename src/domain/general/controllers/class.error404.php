<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\base\controller;

    class error404 extends controller
    {

        public function run() {

            $this->tpl->display("general.error404");

        }

    }

}

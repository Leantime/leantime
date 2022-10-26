<?php

namespace leantime\domain\controllers {

    use leantime\core;

    class error404
    {
        private $tpl;

        public function __construct() {
            $this->tpl = new core\template();
        }

        public function run() {

            $this->tpl->display("general.error404", 404);

        }

    }
}


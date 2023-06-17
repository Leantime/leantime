<?php
namespace leantime\plugins\controllers {

    use leantime\core;
    use leantime\core\controller;

    class statusUpdates extends controller
    {
        public function init()
        {

        }

        /**
         * @return void
         */
        public function get()
        {
          $this->tpl->display("llamadorian.settings");
        }

        public function post ($params) {

        }
    }
}

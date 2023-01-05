<?php

namespace leantime\plugins\controllers {

    use leantime\core;
    use leantime\core\controller;


    class settings extends controller
    {

        public function init()
        {


        }

        /**
         * @return void
         */
        public function get()
        {

           $this->tpl->display("motivationalQuotes.settings");
        }

        public function post($params)
        {



        }

    }
}

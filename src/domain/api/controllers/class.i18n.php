<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class i18n
    {

        private $tpl;
        private $i18n;

        /**
         * constructor - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function __construct()
        {

            $this->tpl = new core\template();
            $this->i18n = new core\language();

        }


        /**
         *
         *
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {

            header('Content-Type: application/javascript');

            $decodedString = json_encode($this->i18n->readIni());

            $result = $decodedString ? $decodedString : '{}';

            echo "var leantime = leantime || {};
                var leantime = {
                    i18n: {
                        dictionary: ".$result.",
                        __: function(index){ return leantime.i18n.dictionary[index];  }
                    }
                };";
/*
            echo "var leantime = leantime || {};
                var leantime = {
                    i18n: {
                        dictionary: " . $decodedString ? $decodedString : '{}' .",
                        __: function(index){ return leantime.i18n.dictionary[index];  }
                    }
                };
            ";
*/

        }

    }

}

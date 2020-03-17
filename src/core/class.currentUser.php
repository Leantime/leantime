<?php

/**
 * currentUser class
 *
 */

namespace leantime\core {

    use leantime\domain\controllers;
    use leantime\domain\repositories;


    class currentUser
    {

        private $id;


        private function __construct()
        {
        }

        /**
         * getInstance - just one instance of the object is allowed (it makes no sense to have more)
         *
         * @access public static
         * @param  $rootPath
         * @return object (instance)
         */
        public static function getInstance()
        {

            if (is_object(self::$instance) === false) {


                self::$instance = new currentUser();
            }

            return self::$instance;
        }

        public function getId() {

            return $this->id;

        }



    }
}

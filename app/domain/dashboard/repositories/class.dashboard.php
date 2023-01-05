<?php

/**
 * dashboard class
 *
 * @author  Jacob Jensen <jjensen@colibrisdesign.com>
 * @version 1.0
 * @package classes
 */
namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;

    class dashboard
    {

        /**
         * @access public
         * @var    object
         */
        public $db;

        /**
         * @access private
         * @var    array
         */
        private $defaultWidgets = array( 1, 3, 9 );

        /**
         * __construct - neu db connection
         *
         * @access public
         * @return
         */
        public function __construct()
        {

            $this->db = core\db::getInstance();

        }

    }


}
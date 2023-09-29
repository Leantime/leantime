<?php

namespace Leantime\Domain\Dashboard\Repositories {

    use Leantime\Core\Db as DbCore;
    class Dashboard
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
        private $defaultWidgets = array(1, 3, 9);

        /**
         * __construct - neu db connection
         *
         * @access public
         * @return
         */
        public function __construct(DbCore $db)
        {
            $this->db = $db;
        }
    }


}

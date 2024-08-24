<?php

namespace Leantime\Domain\Dashboard\Repositories {

    use Leantime\Core\Db\Db as DbCore;

    /**
     *
     */
    class Dashboard
    {
        /**
         * @access public
         * @var    ?DbCore
         */
        public ?DbCore $db;

        /**
         * @access private
         * @var    array
         */
        private array $defaultWidgets = array(1, 3, 9);

        /**
         * __construct - neu db connection
         *
         * @access public
         * @param DbCore $db
         */
        public function __construct(DbCore $db)
        {
            $this->db = $db;
        }
    }


}

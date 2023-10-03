<?php

namespace Leantime\Domain\Auth\Models {

    use Leantime\Core\Eventhelpers;

    /**
     *
     */

    /**
     *
     */
    class Roles
    {
        use Eventhelpers;

        public static $readonly = 'readonly';
        public static $commenter = 'commenter';
        public static $editor = 'editor';
        public static $manager = 'manager';
        public static $admin = 'admin';
        public static $owner = 'owner';

        private static $roleKeys = array(
            5 => 'readonly',      //prev: none
            10 => 'commenter',    //prev: client
            20 => 'editor',       //prev: developer
            30 => 'manager',      //prev: clientmanager
            40 => 'admin',        //prev: manager
            50 => 'owner',         //prev: admin
        );

        /**
         * @return mixed
         */
        /**
         * @return mixed
         */
        private static function getFilteredRoles()
        {
            return self::dispatch_filter('available_roles', self::$roleKeys);
        }

        /**
         * @param $key
         * @return false|mixed
         */
        /**
         * @param $key
         * @return false|mixed
         */
        public static function getRoleString($key)
        {
            return self::getFilteredRoles()[$key] ?? false;
        }

        /**
         * @return mixed
         */
        /**
         * @return mixed
         */
        public static function getRoles()
        {
            return self::getFilteredRoles();
        }
    }

}

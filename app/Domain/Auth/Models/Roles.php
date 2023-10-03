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

        public static string $readonly = 'readonly';
        public static string $commenter = 'commenter';
        public static string $editor = 'editor';
        public static string $manager = 'manager';
        public static string $admin = 'admin';
        public static string $owner = 'owner';

        private static array $roleKeys = array(
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
        private static function getFilteredRoles(): mixed
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
        public static function getRoleString($key): mixed
        {
            return self::getFilteredRoles()[$key] ?? false;
        }

        /**
         * @return mixed
         */
        /**
         * @return mixed
         */
        public static function getRoles(): mixed
        {
            return self::getFilteredRoles();
        }
    }

}

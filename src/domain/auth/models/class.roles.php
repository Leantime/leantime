<?php

namespace leantime\domain\models\auth {

    use leantime\base\eventhelpers;

    class roles
    {

        use eventhelpers;

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
            50 => 'owner'         //prev: admin
        );

        private static function getFilteredRoles()
        {
            return self::dispatch_filter('available_roles', self::$roleKeys);
        }

        public static function getRoleString($key) {
            return self::getFilteredRoles()[$key] ?? '';
        }

        public static function getRoles(){
            return self::getFilteredRoles();
        }

    }

}

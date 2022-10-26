<?php

namespace leantime\domain\services {

    use leantime\core;
    use leantime\domain\repositories;

    class users
    {

        private $userRepo;
        private $tpl;

        public function __construct()
        {
            $this->tpl = new core\template();
            $this->userRepo = new repositories\users();
        }

        //GET
        public function getProfilePicture($id)
        {
            return $this->userRepo->getProfilePicture($id);
        }

        public function getNumberOfUsers()
        {
            return $this->userRepo->getNumberOfUsers();
        }

        public function getAll()
        {
            return $this->userRepo->getAll();
        }

        public function getUser($id)
        {
            return $this->userRepo->getUser($id);
        }

        public function getUserByEmail($email)
        {
            return $this->userRepo->getUserByEmail($email);
        }


        //POST
        public function setProfilePicture($photo, $id)
        {
            $this->userRepo->setPicture($photo, $id);
        }

        public function updateUserSettings($category, $setting, $value)
        {

            $filteredInput = htmlspecialchars($setting);
            $filteredValue = htmlspecialchars($value);

            $_SESSION['userdata']['settings'][$category][$filteredInput] =  $filteredValue;

            $serializeSettings = serialize($_SESSION['userdata']['settings']);

            return $this->userRepo->patchUser($_SESSION['userdata']['id'], array("settings" => $serializeSettings));
        }

    }

}

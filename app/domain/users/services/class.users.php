<?php

namespace leantime\domain\services {

    use leantime\core;
    use leantime\domain\repositories;
    use Ramsey\Uuid\Uuid;

    class users
    {
        private repositories\users $userRepo;
        private core\language $language;
        private repositories\projects $projectRepository;
        private repositories\clients $clientRepo;

        public function __construct()
        {
            $this->userRepo = new repositories\users();
            $this->language = core\language::getInstance();
            $this->projectRepository = new repositories\projects();
            $this->clientRepo = new repositories\clients();
        }

        //GET
        public function getProfilePicture($id)
        {
            return $this->userRepo->getProfilePicture($id);
        }

        public function editUser($values, $id)
        {
            return $this->userRepo->editUser($values, $id);
        }

        public function getNumberOfUsers()
        {
            return $this->userRepo->getNumberOfUsers();
        }

        public function getAll()
        {
            return $this->userRepo->getAll();
        }

        public function getUser($id): array|bool
        {
            return $this->userRepo->getUser($id);
        }

        public function getUserByEmail($email)
        {
            return $this->userRepo->getUserByEmail($email);
        }

        public function getAllBySource($source){
            return $this->userRepo->getAllBySource($source);
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

        /**
         * checkPasswordStrength - Checks password strength for minimum requirements
         * Current requirements are:
         * Password must be at least 8 characters in length.
         * Password must include at least one upper case letter.
         * Password must include at least one number.
         * Password must include at least one special character.
         *
         * @access public
         * @param  string $password The string to be checked
         * @return bool returns true if password meets requirements
         */
        public function checkPasswordStrength(string $password): bool
        {

            // Validate password strength
            // Password must be at least 8 characters in length.
            // Password must include at least one upper case letter.
            // Password must include at least one number.
            // Password must include at least one special character.

            $uppercase = preg_match('@[A-Z]@', $password);
            $lowercase = preg_match('@[a-z]@', $password);
            $number    = preg_match('@[0-9]@', $password);
            $specialChars = preg_match('@[^\w]@', $password);

            if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
                return false;
            } else {
                return true;
            }
        }

        /**
         * createUserInvite - generates a new invite token, creates the user in the db and sends the invitation email
         *
         * TODO: Should accept userModel
         *
         * @access public
         * @param  array $values basic user values
         * @return bool|int returns new user id on success, false on failure
         */
        public function createUserInvite(array $values): bool|int
        {

            //Generate strong password
            $tempPasswordVar =  Uuid::uuid4()->toString();
            $inviteCode = Uuid::uuid4()->toString();

            $values['password'] = password_hash($tempPasswordVar, PASSWORD_DEFAULT);
            $values['status'] = "i";
            $values['pwReset'] = $inviteCode;

            $result = $this->userRepo->addUser($values);

            if ($result === false) {
                return false;
            }

            $mailer = new core\mailer();
            $mailer->setContext('new_user');

            $mailer->setSubject($this->language->__("email_notifications.new_user_subject"));
            $actual_link = BASE_URL . "/auth/userInvite/" . $inviteCode;

            $message = sprintf(
                $this->language->__("email_notifications.user_invite_message"),
                $_SESSION["userdata"]["name"],
                $actual_link,
                $values["user"]
            );

            $mailer->setHtml($message);

            $to = array($values["user"]);

            $mailer->sendMail($to, $_SESSION["userdata"]["name"]);

            return $result;
        }

        /**
         * addUser - simple service wrapper to create a new user
         *
         * TODO: Should accept userModel
         *
         * @access public
         * @param  array $values basic user values
         * @return bool|int returns new user id on success, false on failure
         */
        public function addUser(array $values): bool|int
        {
            return $this->userRepo->addUser($values);
        }


        /**
         * usernameExist - Checks if a given username (email) is already in the db
         *
         * TODO: Should accept userModel
         *
         * @access public
         * @param string $username username
         * @param int $notUserId optional userId to skip. (used when changing email addresses to a new one, skips checking the old one)
         * @return bool returns true or false
         */
        public function usernameExist($username, $notUserId = '')
        {
            return $this->userRepo->usernameExist($username, $notUserId);
        }

        /**
         * getUsersWithProjectAccess - gets all users who can access a project
         *
         * TODO: Should return usermodel
         *
         * @access public
         * @param int $currentUser user who is trying to access the project
         * @param int $projectId project id
         * @return array returns array of users
         */
        public function getUsersWithProjectAccess(int $currentUser, int $projectId): array
        {
            $users = array();

            if ($this->projectRepository->isUserAssignedToProject($currentUser, $projectId)) {
                $project = $this->projectRepository->getProject($projectId);

                if ($project['psettings'] == 'all') {
                    return $this->getAll();
                }

                if ($project['psettings'] == 'clients') {
                    $clientUsers = $this->clientRepo->getClientsUsers($project['clientId']);
                    $projectUsers = $this->projectRepository->getUsersAssignedToProject($projectId);
                    $users = $clientUsers;

                    foreach ($projectUsers as $user) {
                        $column = array_column($users, 'id');
                        $search = array_search($user['id'], $column);
                        if (array_search($user['id'], $column) === false) {
                            $users[] = $user;
                        }
                    }

                    return $users;
                }

                if ($project['psettings'] == 'restricted' || $project['psettings'] == '') {
                    $users = $this->projectRepository->getUsersAssignedToProject($projectId);

                    return $users;
                }
            }

            return [];
        }

        public function editOwn($values, $id)
        {
            $this->userRepo->editOwn($values, $id);

            $user = $this->getUser($id);

            auth::getInstance()->setUserSession($user);

        }
    }
}

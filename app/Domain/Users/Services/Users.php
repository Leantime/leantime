<?php

namespace Leantime\Domain\Users\Services {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Eventhelpers;
    use Leantime\Core\Language as LanguageCore;
    use Leantime\Core\Mailer as MailerCore;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
    use Leantime\Domain\Auth\Services\Auth as AuthService;
    use Ramsey\Uuid\Uuid;
    use SVG\SVG;

    /**
     *
     */

    /**
     *
     */
    class Users
    {
        use Eventhelpers;

        private UserRepository $userRepo;
        private LanguageCore $language;
        private ProjectRepository $projectRepository;
        private ClientRepository $clientRepo;
        private AuthService $authService;

        /**
         * @param UserRepository    $userRepo
         * @param LanguageCore      $language
         * @param ProjectRepository $projectRepository
         * @param ClientRepository  $clientRepo
         * @param AuthService       $authService
         */
        public function __construct(
            UserRepository $userRepo,
            LanguageCore $language,
            ProjectRepository $projectRepository,
            ClientRepository $clientRepo,
            AuthService $authService
        ) {
            $this->userRepo = $userRepo;
            $this->language = $language;
            $this->projectRepository = $projectRepository;
            $this->clientRepo = $clientRepo;
            $this->authService = $authService;
        }

        //GET

        /**
         * @param $id
         * @return string[]|SVG
         */
        /**
         * @param $id
         * @return string[]|SVG
         * @throws BindingResolutionException
         */
        public function getProfilePicture($id)
        {
            return $this->userRepo->getProfilePicture($id);
        }

        /**
         * @param $values
         * @param $id
         * @return bool
         */
        /**
         * @param $values
         * @param $id
         * @return boolean
         */
        public function editUser($values, $id)
        {

            $results = $this->userRepo->editUser($values, $id);
            self::dispatch_event("editUser", ["id" => $id, "values" => $values]);

            return $results;
        }

        /**
         * @return int
         */
        /**
         * @return integer
         */
        public function getNumberOfUsers()
        {
            return $this->userRepo->getNumberOfUsers();
        }

        /**
         * @param $activeOnly
         * @return mixed
         */
        /**
         * @param $activeOnly
         * @return mixed
         */
        public function getAll($activeOnly = false)
        {
            $users =  $this->userRepo->getAll($activeOnly);

            $users = self::dispatch_filter("getAll", $users);

            return $users;
        }

        /**
         * @param $id
         * @return array|bool
         */
        /**
         * @param $id
         * @return array|boolean
         */
        public function getUser($id): array|bool
        {
            return $this->userRepo->getUser($id);
        }

        /**
         * @param $email
         * @return array|false
         */
        /**
         * @param $email
         * @return array|false
         */
        public function getUserByEmail($email)
        {
            return $this->userRepo->getUserByEmail($email);
        }

        /**
         * @param $source
         * @return array|false
         */
        /**
         * @param $source
         * @return array|false
         */
        public function getAllBySource($source)
        {
            return $this->userRepo->getAllBySource($source);
        }


        //POST

        /**
         * @param $photo
         * @param $id
         * @return void
         */
        /**
         * @param $photo
         * @param $id
         * @return void
         * @throws BindingResolutionException
         */
        public function setProfilePicture($photo, $id)
        {
            $this->userRepo->setPicture($photo, $id);
        }

        /**
         * @param $category
         * @param $setting
         * @param $value
         * @return bool
         */
        /**
         * @param $category
         * @param $setting
         * @param $value
         * @return boolean
         */
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
         * @return boolean returns true if password meets requirements
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
         * @param array $values basic user values
         * @return boolean|integer returns new user id on success, false on failure
         * @throws BindingResolutionException
         */
        public function createUserInvite(array $values): bool|int
        {

            //Generate strong password
            $tempPasswordVar =  Uuid::uuid4()->toString();
            $inviteCode = Uuid::uuid4()->toString();

            $values['password'] = $tempPasswordVar;
            $values['status'] = "i";
            $values['pwReset'] = $inviteCode;

            $result = $this->userRepo->addUser($values);

            if ($result === false) {
                return false;
            }

            $mailer = app()->make(MailerCore::class);
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
         * @return boolean|integer returns new user id on success, false on failure
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
         * @param string  $username  username
         * @param integer $notUserId optional userId to skip. (used when changing email addresses to a new one, skips checking the old one)
         * @return boolean returns true or false
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
         * @param integer $currentUser user who is trying to access the project
         * @param integer $projectId   project id
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

        /**
         * @param $values
         * @param $id
         * @return void
         */
        /**
         * @param $values
         * @param $id
         * @return void
         */
        public function editOwn($values, $id)
        {
            $this->userRepo->editOwn($values, $id);

            $user = $this->getUser($id);

            $this->authService->setUserSession($user);

            self::dispatch_event("editUser", ["id" => $id, "values" => $values]);
        }
    }
}

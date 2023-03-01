<?php

namespace leantime\domain\services {

    use Exception;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\core;
    use leantime\core\eventhelpers;
    use RobThree\Auth\TwoFactorAuth;

    class auth
    {
        use eventhelpers;

        /**
         * @access private
         * @var    int user id from DB
         */
        private $userId = null;

        /**
         * @access private
         * @var    int user id from DB
         */
        private $clientId = null;

        /**
         * @access private
         * @var    string username from db
         */
        private $username = null;

        /**
         * @access private
         * @var    string username from db
         */
        private $name = '';

        /**
         * @access private
         * @var    string profileid (image) from db
         */
        private $profileId = '';

        /**
         * @access private
         * @var    string
         */
        private $password = null;

        /**
         * @access private
         * @var    string username (emailaddress)
         */
        private $user = null;

        /**
         * @access private
         * @var    string username (emailaddress)
         */
        private $mail = null;

        /**
         * @access private
         * @var    bool $twoFAEnabled
         */
        private $twoFAEnabled;

        /**
         * @access private
         * @var    string $twoFASecret
         */
        private $twoFASecret;

        /**
         * @access private
         * @var    string
         */
        private $session = null;

        /**
         * @access public
         * @var    string userrole (admin, client, employee)
         */
        public $role = '';
        public $settings = '';

        /**
         * @access public
         * @var    int time for cookie
         */
        public $cookieTime;

        /**
         * @access public
         * @var    string
         */
        public $error = "";

        /**
         * @access public
         * @var    string
         */
        public $success = "";

        /**
         * @access public
         * @var    string
         */
        public $resetInProgress = false;

        /**
         * @access public
         * @var    object
         */
        public $hasher;

        private static $instance = null;

        /*
         * How often can a user reset a password before it has to be changed
         */
        public $pwResetLimit = 5;

        private $config;
        public core\language $language;
        public repositories\setting $settingsRepo;
        public repositories\auth $authRepo;
        public repositories\users $userRepo;

        /**
         * __construct - getInstance of session and get sessionId and refers to login if post is set
         *
         * @param  $sessionid
         * @throws Exception
         */
        protected function __construct($sessionid)
        {
            $this->config = \leantime\core\environment::getInstance();
            $this->cookieTime = $this->config->sessionExpiration;
            $this->language = core\language::getInstance();
            $this->settingsRepo = new repositories\setting();
            $this->authRepo = new repositories\auth();
            $this->userRepo = new repositories\users();

            $this->session = $sessionid;
        }

        public static function getInstance($sessionid = "")
        {

            if (self::$instance === null) {
                self::$instance = new self($sessionid);
            }

            return self::$instance;
        }

        /**
         * @param bool $forceGlobalRoleCheck
         * @return string|bool returns role as string or false on failure
         */
        public static function getRoleToCheck(bool $forceGlobalRoleCheck): string|bool
        {
            if (isset($_SESSION['userdata']) === false) {
                return false;
            }

            if ($forceGlobalRoleCheck) {
                $roleToCheck = $_SESSION['userdata']['role'];
                //If projectRole is not defined or if it is set to inherited
            } elseif (!isset($_SESSION['userdata']['projectRole']) || $_SESSION['userdata']['projectRole'] == "inherited" || $_SESSION['userdata']['projectRole'] == "") {
                $roleToCheck = $_SESSION['userdata']['role'];
                //Do not overwrite admin or owner roles
            } elseif ($_SESSION['userdata']['role'] == roles::$owner || $_SESSION['userdata']['role'] == roles::$admin || $_SESSION['userdata']['role'] == roles::$manager) {
                $roleToCheck = $_SESSION['userdata']['role'];
                //In all other cases check the project role
            } else {
                $roleToCheck = $_SESSION['userdata']['projectRole'];
            }

            //Ensure the role is a valid role
            if (in_array($roleToCheck, roles::getRoles()) === false) {
                error_log("Check for invalid role detected: " . $roleToCheck);
                return false;
            }

            return $roleToCheck;
        }

        /**
         * login - Validate POST-data with DB
         *
         * @access private
         * @return bool
         */
        public function login($username, $password)
        {

            self::dispatch_event("beforeLoginCheck", ['username' => $username, 'password' => $password]);

            //different identity providers can live here
            //they all need to
            ////A: ensure the user is in leantime (with a valid role) and if not create the user
            ////B: set the session variables
            ////C: update users from the identity provider
            //Try Ldap
            if ($this->config->useLdap === true && extension_loaded('ldap')) {
                $ldap = new ldap();

                if ($ldap->connect() && $ldap->bind($username, $password)) {
                    //Update username to include domain
                    $usernameWDomain = $ldap->getEmail($username);
                    //Get user
                    $user = $this->userRepo->getUserByEmail($usernameWDomain);

                    $ldapUser = $ldap->getSingleUser($username);

                    if ($ldapUser === false) {
                        return false;
                    }

                    //If user does not exist create user
                    if ($user == false) {
                        $userArray = array(
                            'firstname' => $ldapUser['firstname'],
                            'lastname' => $ldapUser['lastname'],
                            'phone' => $ldapUser['phonenumber'],
                            'user' => $ldapUser['user'],
                            'role' => $ldapUser['role'],
                            'password' => '',
                            'clientId' => '',
                            'source' => 'ldap',
                            'status' => 'a'
                        );

                        $userId = $this->userRepo->addUser($userArray);

                        if ($userId !== false) {
                            $user = $this->userRepo->getUserByEmail($usernameWDomain);
                        } else {
                            error_log("Ldap user creation failed.");
                            return false;
                        }

                        //TODO: create a better login response. This will return that the username or password was not correct
                    } else {
                        $user['firstname'] = $ldapUser['firstname'];
                        $user['lastname'] = $ldapUser['lastname'];
                        $user['phone'] = $ldapUser['phonenumber'];
                        $user['user'] = $user['username'];

                        $this->userRepo->editUser($user, $user['id']);
                    }

                    if ($user !== false && is_array($user)) {
                        $this->setUserSession($user, true);

                        return true;
                    } else {
                        error_log("Could not retrieve user by email");
                        return false;
                    }
                }

                //Don't return false, to allow the standard login provider to check the db for contractors or clients not in ldap
            } elseif ($this->config->useLdap === true && !extension_loaded('ldap')) {
                error_log("Can't use ldap. Extension not installed");
            }

            //TODO: Single Sign On?
            //Standard login
            //Check if the user is in our db
            //Check even if ldap is turned on to allow contractors and clients to have an account
            $user = $this->authRepo->getUserByLogin($username, $password);

            if ($user !== false && is_array($user)) {
                $this->setUserSession($user);

                self::dispatch_event("afterLoginCheck", ['username' => $username, 'password' => $password, 'authService' => self::getInstance()]);
                return true;

            } else {

                self::dispatch_event("afterLoginCheck", ['username' => $username, 'password' => $password, 'authService' => self::getInstance()]);
                return false;
            }

        }

        public function setUserSession($user, $isLdap = false)
        {
            if (!$user || !is_array($user)) {
                return false;
            }

            $this->name = htmlentities($user['firstname']);
            $this->mail = filter_var($user['username'], FILTER_SANITIZE_EMAIL);
            $this->userId = $user['id'];
            $this->settings = $user['settings'] ? unserialize($user['settings']) : array();
            $this->clientId = $user['clientId'];
            $this->twoFAEnabled = $user['twoFAEnabled'];
            $this->twoFASecret = $user['twoFASecret'];
            $this->role = roles::getRoleString($user['role']);
            $this->profileId = $user['profileId'];

            //Set Sessions
            $_SESSION['userdata'] = self::dispatch_filter('user_session_vars', [
                        'role' => $this->role,
                        'id' => $this->userId,
                        'name' => $this->name,
                        'profileId' => $this->profileId,
                        'mail' => $this->mail,
                        'clientId' => $this->clientId,
                        'settings' => $this->settings,
                        'twoFAEnabled' => $this->twoFAEnabled,
                        'twoFAVerified' => false,
                        'twoFASecret' => $this->twoFASecret,
                        'isLdap' => $isLdap
            ]);

            $this->authRepo->updateUserSession($this->userId, $this->session, time());
        }

        /**
         * logged_in - Check if logged in and Update sessions
         *
         * @access public
         * @return bool
         */
        public function logged_in()
        {

            //Check if we actually have a php session available
            if (isset($_SESSION['userdata']) === true) {
                return true;

                //If the session doesn't have any session data we are out of sync. Start again
            } else {
                return false;
            }
        }

        /**
         * logout - destroy sessions and cookies
         *
         * @access private
         */
        public function logout()
        {

            $this->authRepo->invalidateSession($this->session);

            core\session::destroySession();

            if (isset($_SESSION)) {
                $sessionsToDestroy = self::dispatch_filter('sessions_vars_to_destroy', [
                            'userdata',
                            'template',
                            'subdomainData',
                            'currentProject',
                            'currentSprint',
                            'projectsettings',
                            'currentSubscriptions',
                            'lastTicketView',
                            'lastFilterdTicketTableView'
                ]);

                foreach ($sessionsToDestroy as $key) {
                    unset($_SESSION[$key]);
                }
            }
        }

        /**
         * validateResetLink - validates that the password reset link belongs to a user account in the database
         *
         * @access public
         * @param string $hash invite link hash
         * @return bool
         */
        public function validateResetLink(string $hash)
        {

            return $this->authRepo->validateResetLink($hash);
        }

        /**
         * getUserByInviteLink - gets the user by invite link
         *
         * @access public
         * @param string $hash invite link hash
         * @return array|bool
         */
        public function getUserByInviteLink($hash)
        {
            return $this->authRepo->getUserByInviteLink($hash);
        }

        /**
         * generateLinkAndSendEmail - generates an invite link (hash) and sends email to user
         *
         * @access public
         * @param string $username new user to be invited (email)
         * @return bool returns true on success, false on failure
         */
        public function generateLinkAndSendEmail(string $username): bool
        {

            $userFromDB = $this->userRepo->getUserByEmail($_POST["username"]);

            if ($userFromDB !== false && count($userFromDB) > 0) {
                if ($userFromDB['pwResetCount'] < $this->pwResetLimit) {
                    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
                    $resetLink = substr(str_shuffle($permitted_chars), 0, 32);

                    $result = $this->authRepo->setPWResetLink($username, $resetLink);

                    if ($result) {
                        //Don't queue, send right away
                        $mailer = new core\mailer();
                        $mailer->setContext('password_reset');
                        $mailer->setSubject($this->language->__('email_notifications.password_reset_subject'));
                        $actual_link = "" . BASE_URL . "/auth/resetPw/" . $resetLink;
                        $mailer->setHtml(sprintf($this->language->__('email_notifications.password_reset_message'), $actual_link));
                        $to = array($username);
                        $mailer->sendMail($to, "Leantime System");

                        return true;
                    }
                }
            }

            return false;
        }

        public function changePw($password, $hash): bool
        {
            return $this->authRepo->changePW($password, $hash);
        }

        public static function userIsAtLeast(string $role, $forceGlobalRoleCheck = false)
        {

            //Force Global Role check to circumvent projectRole checks for global controllers (users, projects, clients etc)
            $roleToCheck = self::getRoleToCheck($forceGlobalRoleCheck);

            if ($roleToCheck === false) {
                return false;
            }

            $testKey = array_search($role, roles::getRoles());

            if ($role == "" || $testKey === false) {
                error_log("Check for invalid role detected: " . $role);
                return false;
            }

            $currentUserKey = array_search($roleToCheck, roles::getRoles());

            if ($testKey <= $currentUserKey) {
                return true;
            } else {
                return false;
            }
        }

        public static function authOrRedirect($role, $forceGlobalRoleCheck = false): mixed
        {

            if (self::userHasRole($role, $forceGlobalRoleCheck)) {
                return true;
            } else {
                core\frontcontroller::redirect(BASE_URL . "/errors/error403");
            }

            return false;
        }

        public static function userHasRole(string|array $role, $forceGlobalRoleCheck = false): bool
        {

            //Force Global Role check to circumvent projectRole checks for global controllers (users, projects, clients etc)
            $roleToCheck = self::getRoleToCheck($forceGlobalRoleCheck);

            if (is_array($role) && in_array($roleToCheck, $role)) {
                return true;
            } elseif ($role == $roleToCheck) {
                return true;
            }

            return false;
        }

        public static function getRole()
        {
        }

        public static function getUserClientId()
        {
            return $_SESSION['userdata']['clientId'];
        }

        public static function getUserId()
        {
            return $_SESSION['userdata']['id'];
        }

        public function use2FA()
        {
            return $_SESSION['userdata']['twoFAEnabled'];
        }

        public function verify2FA($code)
        {
            $tfa = new TwoFactorAuth('Leantime');
            return $tfa->verifyCode($_SESSION['userdata']['twoFASecret'], $code);
        }

        public function get2FAVerified()
        {
            return $_SESSION['userdata']['twoFAVerified'];
        }

        public function set2FAVerified()
        {
            $_SESSION['userdata']['twoFAVerified'] = true;
        }
    }

}

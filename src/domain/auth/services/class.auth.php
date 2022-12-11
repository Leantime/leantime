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

        /**
         * __construct - getInstance of session and get sessionId and refers to login if post is set
         *
         * @param  $sessionid
         * @return bool
         * @throws Exception
         */
        protected function __construct($sessionid)
        {
            $this->config = new core\config();
            $this->cookieTime = $this->config->sessionExpiration;
            $this->language = core\language::getInstance();
            $this->settingsRepo = new repositories\setting();
            $this->authRepo = new repositories\auth();
            $this->userRepo = new repositories\users();

            $this->session = $sessionid;

            /*
            if(isset($_SESSION['userdata']) && $this->use2FA()) {

                if (isset($_POST['twoFA_code']) === true) {
                    $redirectUrl = filter_var($_POST['redirectUrl'], FILTER_SANITIZE_URL);
                    if($this->verify2FA($_POST['twoFA_code'])){
                        $this->set2FAVerified();
                        header("Location:".$redirectUrl);
                        exit();
                    } else {
                        $this->error =  $this->language->__('notification.incorrect_twoFA_code');
                    }
                }
            }

            */



        }

        public static function getInstance($sessionid="")
        {

            if (self::$instance === null) {

                self::$instance = new self($sessionid);

            }

            return self::$instance;
        }

        /**
         * login - Validate POST-data with DB
         *
         * @access private
         * @return bool
         */
        public function login($username, $password)
        {

            //different identity providers can live here
            //they all need to
            ////A: ensure the user is in leantime (with a valid role) and if not create the user
            ////B: set the session variables
            ////C: update users from the identity provider

            //Try Ldap
            if($this->config->useLdap === true && extension_loaded('ldap')){

                $ldap = new ldap();

                if ($ldap->connect() && $ldap->bind($username, $password)) {

                    //Update username to include domain
                    //$usernameWDomain = $ldap->extractLdapFromUsername($username)."".$ldap->userDomain;
                    $usernameWDomain = $ldap->getEmail($username);
                    //Get user
                    $user = $this->userRepo->getUserByEmail($usernameWDomain);

                    $ldapUser = $ldap->getSingleUser($username);

                    //If user does not exist create user
                    if($user == false) {

                        $userArray = array(
                            'firstname' => $ldapUser['firstname'],
                            'lastname' => $ldapUser['lastname'],
                            'phone' => $ldapUser['phonenumber'],
                            'user' => $ldapUser['user'],
                            'role' => $ldapUser['role'],
                            'password' => '',
                            'clientId' => '',
                            'source' => 'ldap'
                        );

                        $this->userRepo->addUser($userArray);

                        $user = $this->userRepo->getUserByEmail($usernameWDomain);
                        //ldap login successful however the user doesn't exist in the db, admin needs to sync or allow autocreate
                        //TODO: create a better login response. This will return that the username or password was not correct

                    } else {

                        $user['firstname'] = $ldapUser['firstname'];
                        $user['lastname'] = $ldapUser['lastname'];
                        $user['phone'] = $ldapUser['phonenumber'];
                        $user['user'] = $user['username'];

                        $this->userRepo->editUser($user, $user['id']);
                    }

                    //TODO: if user exists in ldap, do an auto update of name
                    $this->setUserSession($user,true);

                    $this->authRepo->updateUserSession($user['id'], $this->session, time());

                    return true;

                }

                //Don't return false, to allow the standard login provider to check the db for contractors or clients not in ldap

            }elseif($this->config->useLdap === true && !extension_loaded('ldap')) {

                error_log("Can't use ldap. Extension not installed");

            }

            //TODO: Single Sign On?

            //Standard login
            //Check if the user is in our db
            //Check even if ldap is turned on to allow contractors and clients to have an account
            $user = $this->authRepo->getUserByLogin($username, $password);

            if($user !== false && is_array($user)) {

                $this->setUserSession($user);

                $this->authRepo->updateUserSession($user['id'], $this->session, time());

                return true;

            }else{

                return false;

            }
        }

        private function setUserSession($user, $isLdap=false) {

            $this->name = strip_tags($user['firstname']);
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

        }


        /**
         * logged_in - Check if logged in and Update sessions
         *
         * @access public
         * @return unknown_type
         */
        public function logged_in(){

            //Check if we actually have a php session available
            if(isset($_SESSION['userdata']) === true) {

                return true;

                //If the session doesn't have any session data we are out of sync. Start again
            }else{

                return false;
            }

            return false;

        }

        /**
         * logout - destroy sessions and cookies
         *
         * @access private
         * @return bool
         */
        public function logout()
        {

            $this->authRepo->invalidateSession($this->session);
            core\session::destroySession();

            if(isset($_SESSION)) {

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

                foreach($sessionsToDestroy as $key) {
                    unset($_SESSION[$key]);
                }

            }

        }

        /**
         * validateResetLink - validates that the password reset link belongs to a user account in the database
         *
         * @access public
         * @param
         * @return bool
         */
        public function validateResetLink($hash)
        {

            return $this->authRepo->validateResetLink($hash);
        }


        public function generateLinkAndSendEmail($username)
        {

            $userFromDB = $this->userRepo->getUserByEmail($_POST["username"]);

            if($userFromDB !== false && count($userFromDB) > 0) {

                if ($userFromDB['pwResetCount'] < $this->pwResetLimit) {

                    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
                    $resetLink = substr(str_shuffle($permitted_chars), 0, 32);

                    $result = $this->authRepo->setPWResetLink($username, $resetLink);

                    if($result) {

                        //Don't queue, send right away
                        $mailer = new core\mailer();
                        $mailer->setContext('password_reset');
                        $mailer->setSubject($this->language->__('email_notifications.password_reset_subject'));
                        $actual_link = "".BASE_URL."/auth/resetPw/".$resetLink;
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

        public static function userIsAtLeast(string $role, $forceGlobalRoleCheck = false) {

            //If statement split up for readability
            //Force Global Role check to circumvent projectRole checks for global controllers (users, projects, clients etc)
            if ($forceGlobalRoleCheck == true){

                $roleToCheck = $_SESSION['userdata']['role'];

                //If projectRole is not defined or if it is set to inherited
            }elseif(!isset($_SESSION['userdata']['projectRole']) || $_SESSION['userdata']['projectRole'] == "inherited" || $_SESSION['userdata']['projectRole'] == "") {

                $roleToCheck = $_SESSION['userdata']['role'];

                //Do not overwrite admin or owner roles
            }elseif($_SESSION['userdata']['role'] == roles::$owner
                || $_SESSION['userdata']['role'] == roles::$admin
                || $_SESSION['userdata']['role'] == roles::$manager) {

                $roleToCheck = $_SESSION['userdata']['role'];

                //In all other cases check the project role
            }else{

                $roleToCheck = $_SESSION['userdata']['projectRole'];

            }

            $testKey = array_search($role, roles::getRoles());

            if($role == "" || $testKey === false){
                error_log("Check for invalid role detected: ".$role);
                return false;
            }

            $currentUserKey = array_search($roleToCheck, roles::getRoles());

            if($testKey <= $currentUserKey){

                return true;
            }else{
                return false;
            }
        }

        public static function authOrRedirect($role, $forceGlobalRoleCheck = false)
        {

            if(self::userHasRole($role, $forceGlobalRoleCheck)) {

                return true;

            }else{

                core\frontcontroller::redirect(BASE_URL."/errors/error403");
            }

        }

        public static function userHasRole (string|array $role, $forceGlobalRoleCheck=false): bool
        {
            //If statement split up for readability
            //Force Global Role check to circumvent projectRole checks for global controllers (users, projects, clients etc)
            if ($forceGlobalRoleCheck == true){

                $roleToCheck = $_SESSION['userdata']['role'];

                //If projectRole is not defined or if it is set to inherited
            }elseif(!isset($_SESSION['userdata']['projectRole']) || $_SESSION['userdata']['projectRole'] == "inherited" || $_SESSION['userdata']['projectRole'] == "") {

                $roleToCheck = $_SESSION['userdata']['role'];

                //Do not overwrite admin or owner roles
            }elseif($_SESSION['userdata']['role'] == roles::$owner
                || $_SESSION['userdata']['role'] == roles::$admin
                || $_SESSION['userdata']['role'] == roles::$manager) {

                $roleToCheck = $_SESSION['userdata']['role'];

                //In all other cases check the project role
            }else{

                $roleToCheck = $_SESSION['userdata']['projectRole'];

            }

            if($_SESSION['userdata']['role'] == roles::$owner || $_SESSION['userdata']['role'] == roles::$admin) {
                $roleToCheck = $_SESSION['userdata']['role'];
            }

            if(is_array($role) && in_array($roleToCheck, $role)){
                return true;
            }else if($role == $roleToCheck){
                return true;
            }

            return false;
        }

        public static function getRole () {
        }

        public static function getUserClientId () {
            return $_SESSION['userdata']['clientId'];
        }

        public static function getUserId () {
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

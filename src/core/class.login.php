<?php

/**
 * Login class - login procedure
 *
 * @author  Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @license GNU/GPL, see license.txt
 */
namespace leantime\core {

    use leantime\domain\services\ldap;
    use PDO;
    use leantime\domain\repositories;
    use RobThree\Auth\TwoFactorAuth;

    class login
    {

        /**
         * @access private
         * @var    integer user id from DB
         */
        private $userId = null;

        /**
         * @access private
         * @var    integer user id from DB
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
         * @access private
         * @var    object - db connection
         */
        private $db = null;

        /**
         * @access public
         * @var    string userrole (admin, client, employee)
         */
        public $role = '';

        public $settings = '';

        /**
         * @access public
         * @var    integer time for cookie
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


        public static $userRoles = array(
            10   => 'client',
            20   => 'developer',
            30   => 'clientManager',
            40   => 'manager',
            50   => 'admin'
        );

        /*
         * Clientmanager roles
         * ClientManagers can only add and remove a set of rules
         */
        public static $clientManagerRoles = array(
            10   => 'client',
            20   => 'developer',
            30   => 'clientManager'
        );

        private static $instance;

        /*
         * How often can a user reset a password before it has to be changed
         */
        public $pwResetLimit = 5;

        private $config;

        /**
         * __construct - getInstance of session and get sessionId and refers to login if post is set
         *
         * @param  $sessionid
         * @return boolean
         * @throws \Exception
         */
        private function __construct($sessionid)
        {
            $this->db = db::getInstance();

            $this->config = new config();
            $this->cookieTime = $this->config->sessionExpiration;
            $this->language = new language();
            $this->settingsRepo = new repositories\setting();
            $this->session = $sessionid;

            if (isset($_POST['login'])===true && isset($_POST['username'])===true && isset($_POST['password'])===true) {

                $redirectUrl = filter_var($_POST['redirectUrl'], FILTER_SANITIZE_URL);
                $this->username = filter_var($_POST['username'], FILTER_SANITIZE_EMAIL);
                $this->password = ($_POST['password']);

                //If login successful redirect to the correct url to avoid post on reload
                if($this->login() === true){
                    $this->checkSessions();

                    if($this->use2FA()) {
                        $this->redirect2FA($redirectUrl);
                    }

                    header("Location:".$redirectUrl);
                    exit();
                }

            }

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

            //Reset password
            if(isset($_POST["resetPassword"])) {

                if(isset($_POST['username']) === true) {
                    //Look for email address and send email
                    $userFromDB = $this->getUser($_POST["username"]);

                    if($userFromDB !== false && count($userFromDB) > 0) {

                        if($userFromDB['pwResetCount'] < $this->pwResetLimit) {

                            $this->generateLinkAndSendEmail($_POST["username"]);
                            $this->success = $this->language->__('notifications.email_was_sent_to_reset');

                        }else{
                            $this->error =  $this->language->__('notifications.could_not_reset_limit_reached');
                        }
                    }else{
                        $this->error =  $this->language->__('notifications.could_not_find_username');
                    }
                }

                if(isset($_POST['password']) === true && isset($_POST['password2']) === true) {

                    if(strlen($_POST['password']) == 0 || $_POST['password'] != $_POST['password2']) {

                        $this->error = $this->language->__('notification.passwords_dont_match');

                    }else{

                        $this->changePW($_POST['password'], $_GET['hash']);
                        $this->success = $this->language->__('notifications.passwords_changed_successfully');

                    }
                }

            }

            if (isset($_GET['logout'])===true && $_GET['logout']==='1') {

                $this->logout();
                header("Location:".BASE_URL."/");
                exit();

            }

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
         * @return boolean
         */
        private function login()
        {

            //different identity providers can live here
            //they all need to
            ////A: ensure the user is in leantime (with a valid role) and if not create the user
            ////B: set the session variables
            ////C: update users from the identity provider

            //Try Ldap
            if($this->config->useLdap === true){

                $ldap = new ldap();

                if ($ldap->connect() && $ldap->bind($this->username, $this->password)) {

                    //Update username to include domain
                    $usernameWDomain = $ldap->extractLdapFromUsername($this->username)."".$ldap->userDomain;

                    //Get user
                    $userCheck = $this->getUser($usernameWDomain);

                    //If user does not exist create user
                    if($userCheck == false) {

                        $ldapUser = $ldap->getSingleUser($this->username);

                        $userArray = array(
                            'firstname' => $ldapUser['firstname'],
                            'lastname' => $ldapUser['lastname'],
                            'phone' => '',
                            'user' => $ldapUser['user'],
                            'role' => $ldapUser['role'],
                            'password' => '',
                            'clientId' => '',
                            'source' => 'ldap'
                        );

                        $users = new repositories\users();
                        $users->addUser($userArray);

                        //ldap login successful however the user doesn't exist in the db, admin needs to sync or allow autocreate
                        //TODO: create a better login response. This will return that the username or password was not correct
                    }

                    //TODO: if user exists in ldap, do an auto update of name

                    //Set username to be ladp+domain to validate and get user info
                    $this->username = $usernameWDomain;

                    // set user session
                    $this->getUserByLogin($this->username, '', true);

                    $this->setSession(true);

                    $this->updateUserSession($this->session, time());

                    $this->setCookie($this->cookieTime);

                    return true;

                }

            }

            //TODO: Single Sign On?

            //Check if the user is in our db
            //Check even if ldap is turned on to allow contractors and clients to have an account
            if($this->getUserByLogin($this->username, $this->password) === true) {

                $this->setSession();

                $this->updateUserSession($this->session, time());

                $this->setCookie($this->cookieTime);

                return true;

            }else{

                $this->error = $this->language->__('notifications.username_or_password_incorrect');

                return false;

            }
        }

        private function setSession($isLdap=false) {
            //Set Sessions
            $_SESSION['userdata']['role'] = $this->role;
            $_SESSION['userdata']['id'] = $this->userId;
            $_SESSION['userdata']['name'] = $this->name;
            $_SESSION['userdata']['mail'] = $this->mail;
            $_SESSION['userdata']['clientId'] = $this->clientId;
            $_SESSION['userdata']['settings'] = $this->settings;
            $_SESSION['userdata']['twoFAEnabled'] = $this->twoFAEnabled;
            $_SESSION['userdata']['twoFAVerified'] = false;
            $_SESSION['userdata']['twoFASecret'] = $this->twoFASecret;
            $_SESSION['userdata']['isLdap'] = $isLdap;

        }

        /**
         * setCookie - set and/or updates the cookie
         *
         * @param  $time
         * @return
         */
        private function setCookie($time)
        {
            $expiry = time()+$time;
            setcookie("sid", $this->session, (int)$expiry, "/");
        }



        /**
         * logged_in - Check if logged in and Update sessions
         *
         * @access public
         * @return unknown_type
         */
        public function logged_in()
        {


            try{

                $query = "SELECT count(username) AS userCounter FROM zp_user 
		          WHERE session = :session LIMIT 1";

                $stmn = $this->db->database->prepare($query);

                $stmn->bindValue(':session', $this->session, PDO::PARAM_STR);

                $stmn->execute();

                $returnValues = $stmn->fetch();

            }catch(\PDOException $e){

               return false;

            }

            $userCounter = $returnValues['userCounter'];

            $stmn->closeCursor();

            if($userCounter !=1) {

                $this->logout();

                return false;

            }else{

                if(isset($_COOKIE['sid']) === true) {

                    if(isset($_SESSION['userdata']) === true) {

                        $this->userId = $_SESSION['userdata']['id'];

                        $this->setCookie($this->cookieTime);

                        $this->updateUserSession($this->session, time());

                        return true;

                    }else{

                        $this->logout();

                        return false;

                    }

                }else{

                    $this->logout();

                    return false;

                }

            }
        }

        /**
         * logout - destroy sessions and cookies
         *
         * @access private
         * @return boolean
         */
        private function logout()
        {

            $query = "UPDATE zp_user SET session = '' 
				 WHERE session = :sessionid LIMIT 1";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':sessionid', $this->session, PDO::PARAM_STR);
            $stmn->execute();
            $stmn->closeCursor();

            $this->setCookie(time()-$this->cookieTime);

            if(isset($_SESSION)) {
                unset($_SESSION['userdata']);
                unset($_SESSION['template']);
                unset($_SESSION["subdomainData"]);
                unset($_SESSION["currentProject"]);
                unset($_SESSION["currentSprint"]);
                unset($_SESSION["projectsettings"]);
                unset($_SESSION['currentSubscription']);
                unset($_SESSION['lastTicketView']);
                unset($_SESSION['lastFilterdTicketTableView']);
            }

            unset($_COOKIE);

            return true;
        }

        /**
         * checkSessions - check all sessions in the database and unset them if necessary
         *
         * @access private
         * @return void
         */
        private function checkSessions()
        {

            $query = "UPDATE zp_user SET session = '' WHERE (".time()." - sessionTime) > ".$this->cookieTime." ";

            $stmn = $this->db->database->prepare($query);
            $stmn->execute();
            $stmn->closeCursor();

        }


        /**
         * getUserByLogin - Check login data and set email vars
         *
         * @access public
         * @param  $username
         * @param  $password
         * @param  $ldapLogin //check if the login is coming from an ldap directory
         * @return boolean
         */
        public function getUserByLogin($username, $password, $ldapLogin = false)
        {

            $user = $this->getUser($username);

            if($user === false || (!password_verify($password, $user['password']) && $ldapLogin == false)) {

                return false;

            }else{

                $this->name = strip_tags($user['firstname']);
                $this->mail = filter_var($user['username'], FILTER_SANITIZE_EMAIL);
                $this->userId = $user['id'];
                $this->settings = unserialize($user['settings']);
                $this->clientId = $user['clientId'];
                $this->twoFAEnabled = $user['twoFAEnabled'];
                $this->twoFASecret = $user['twoFASecret'];
                $this->role = self::$userRoles[$user['role']];

                return true;
            }
        }

        /**
         * updateemailSession - Update the sessiontime of a email
         *
         * @access public
         * @param  $sessionid
         * @param  $time
         * @return
         */
        public function updateUserSession($sessionid, $time)
        {
            //echo "updateUserSession";
            $query = "UPDATE
					zp_user 
				SET 
					lastlogin = NOW(),
					session = :sessionid,
					sessionTime = :time,
					pwReset = NULL,
					pwResetExpiration = NULL
				WHERE 
					id =  :id 
				LIMIT 1";


            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':id', $this->userId, PDO::PARAM_INT);
            $stmn->bindValue(':sessionid', $sessionid, PDO::PARAM_STR);
            $stmn->bindValue(':time', $time, PDO::PARAM_STR);
            $stmn->execute();

            $stmn->closeCursor();
        }

        /**
         * validateResetLink - validates that the password reset link belongs to a user account in the database
         *
         * @access public
         * @param
         * @return bool
         */
        public function validateResetLink()
        {

            $link = stripslashes(htmlentities($_GET["hash"]));

            $query = "SELECT id FROM zp_user WHERE pwReset = :resetLink LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':resetLink', $link, PDO::PARAM_STR);

            $stmn->execute();
            $returnValues = $stmn->fetch();
            $stmn->closeCursor();

            if($returnValues !== false && count($returnValues) > 0) {
                return true;
            }else{
                return false;
            }
        }

        /**
         * getUser - gets the user from the db
         *
         * @access public
         * @param  username - string
         * @return bool
         */
        private function getUser($username)
        {

            $query = "SELECT 
                    id,
					username,
					role,
					firstname AS firstname,
					lastname AS name,
					password,
					settings,
					profileId,
					clientId,
					twoFAEnabled,
					twoFASecret FROM zp_user 
		          WHERE username = :username LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':username', $username, PDO::PARAM_STR);

            $stmn->execute();

            $returnValues = $stmn->fetch();

            $stmn->closeCursor();

            return $returnValues;


        }

        private function generateLinkAndSendEmail($username)
        {

            $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';
            $resetLink = substr(str_shuffle($permitted_chars), 0, 32);

            $query = "UPDATE
					zp_user 
				SET 
					pwReset = :link,
					pwResetExpiration = :time,
					pwResetCount = IFNULL(pwResetCount, 0)+1
				WHERE 
					username = :user
				LIMIT 1";


            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':user', $username, PDO::PARAM_STR);
            $stmn->bindValue(':time', date("Y-m-d h:i:s", time()), PDO::PARAM_STR);
            $stmn->bindValue(':link', $resetLink, PDO::PARAM_STR);
            $stmn->execute();
            $count = $stmn->rowCount();
            $stmn->closeCursor();

            if($count > 0) {
                $mailer = new mailer();
                $mailer->setSubject($this->language->__('email_notifications.password_reset_subject'));
                $actual_link = "".BASE_URL."/resetPassword/".$resetLink;
                $mailer->setHtml(sprintf($this->language->__('email_notifications.password_reset_message'), $actual_link));
                $to = array($username);
                $mailer->sendMail($to, "Leantime System");
            }

        }

        private function changePW($password, $hash)
        {

            $query = "UPDATE
					zp_user 
				SET 
					password = :password,
					pwReset = '',
					pwResetExpiration = '',
					lastpwd_change = :time,
					pwResetCount = 0
				WHERE 
					pwReset = :hash
				LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':time', date("Y-m-d h:i:s", time()), PDO::PARAM_STR);
            $stmn->bindValue(':hash', $hash, PDO::PARAM_STR);
            $stmn->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
            $stmn->execute();
            $count = $stmn->rowCount();
            $stmn->closeCursor();

        }

        public static function userIsAtLeast($role) {

            $testKey = array_search($role, self::$userRoles);

            if($role == "" || $testKey === false){
                throw new Exception("Role not defined");
            }

            $currentUserKey = array_search($_SESSION['userdata']['role'], self::$userRoles);

            if($testKey <= $currentUserKey){
                return true;
            }else{
                return false;
            }
        }

        public static function userHasRole ($role) {

            if($role == $_SESSION['userdata']['role']){
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

        /**
         * @return bool
         */
        private function use2FA()
        {
            return $_SESSION['userdata']['twoFAEnabled'];
        }

        public function redirect2FA($redirectUrl)
        {
            header("Location:".BASE_URL."/index.php?twoFA=1&redirectUrl=$redirectUrl");
            exit();
        }

        private function verify2FA($code)
        {
            $tfa = new TwoFactorAuth('Leantime');
            return $tfa->verifyCode($_SESSION['userdata']['twoFASecret'], $code);
        }

        private function get2FAVerified()
        {
            return $_SESSION['userdata']['twoFAVerified'];
        }

        private function set2FAVerified()
        {
            $_SESSION['userdata']['twoFAVerified'] = true;
        }

    }
}
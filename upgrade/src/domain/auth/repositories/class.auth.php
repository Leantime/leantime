<?php

namespace leantime\domain\repositories {

    use Exception;
    use leantime\domain\services\ldap;
    use PDO;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use PDOException;
    use leantime\core;
    use RobThree\Auth\TwoFactorAuth;

    class auth
    {

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


        private static $instance;

        /*
         * How often can a user reset a password before it has to be changed
         */
        public $pwResetLimit = 5;

        private $config;


        public function __construct()
        {

            $this->db = core\db::getInstance();
            $this->config = new core\config();
            $this->userService = new services\users();
            $this->userRepo = new repositories\users();

        }

        /**
         * logout - destroy sessions and cookies
         *
         * @access private
         * @return bool
         */
        public function invalidateSession($sessionId)
        {

            $query = "UPDATE zp_user SET session = '' 
				 WHERE session = :sessionid LIMIT 1";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':sessionid', $sessionId, PDO::PARAM_STR);
            $result = $stmn->execute();
            $stmn->closeCursor();

            return $result;
        }

        /**
         * checkSessions - check all sessions in the database and unset them if necessary
         *
         * @access private
         * @return void
         */
        private function invalidateExpiredUserSessions(): bool
        {

            $query = "UPDATE zp_user SET session = '' WHERE (".time()." - sessionTime) > ".$this->config->sessionExpiration." ";

            $stmn = $this->db->database->prepare($query);
            $result = $stmn->execute();
            $stmn->closeCursor();

            return $result;

        }


        /**
         * getUserByLogin - Check login data and returns user if correct
         *
         * @access public
         * @param  $username
         * @param  $password
         * @return bool
         */
        public function getUserByLogin($username, $password): array|bool
        {

            $user = $this->userRepo->getUserByEmail($username);

            if($user !== false && password_verify($password, $user['password'])) {

                return $user;

            }

            return false;
        }

        /**
         * updateSession - Update the session time by sessionId
         *
         * @access public
         * @param  $sessionid
         * @param  $time
         * @return
         */
        public function updateUserSession($userId, $sessionid, $time)
        {

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

            $stmn->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmn->bindValue(':sessionid', $sessionid, PDO::PARAM_STR);
            $stmn->bindValue(':time', $time, PDO::PARAM_STR);
            $result = $stmn->execute();

            $stmn->closeCursor();

            return $result;
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

            $query = "SELECT id FROM zp_user WHERE pwReset = :resetLink LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':resetLink', $hash, PDO::PARAM_STR);

            $stmn->execute();
            $returnValues = $stmn->fetch();
            $stmn->closeCursor();

            if($returnValues !== false && count($returnValues) > 0) {
                return true;
            }else{
                return false;
            }
        }


        public function setPWResetLink($username, $resetLink): bool
        {

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
            $result = $stmn->execute();
            $stmn->closeCursor();

            return $result;
        }

        public function changePW($password, $hash)
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
            $result = $stmn->execute();
            $stmn->closeCursor();

            return $result;

        }
    }
}
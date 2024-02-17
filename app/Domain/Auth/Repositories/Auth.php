<?php

namespace Leantime\Domain\Auth\Repositories {

    use Leantime\Core\Db as DbCore;
    use Leantime\Core\Environment as EnvironmentCore;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;
    use PDO;

    /**
     *
     */
    class Auth
    {
        /**
         * @access private
         * @var    int|null user id from DB
         */
        private ?int $userId = null;

        /**
         * @access private
         * @var    int|null user id from DB
         */
        private ?int $clientId = null;

        /**
         * @access private
         * @var    string|null username from db
         */
        private ?string $username = null;

        /**
         * @access private
         * @var    string username from db
         */
        private string $name = '';

        /**
         * @access private
         * @var    string profileid (image) from db
         */
        private string $profileId = '';

        /**
         * @access private
         * @var    string|null
         */
        private ?string $password = null;

        /**
         * @access private
         * @var    string|null username (emailaddress)
         */
        private ?string $user = null;

        /**
         * @access private
         * @var    string|null username (emailaddress)
         */
        private ?string $mail = null;

        /**
         * @access private
         * @var    bool $twoFAEnabled
         */
        private bool $twoFAEnabled;

        /**
         * @access private
         * @var    string $twoFASecret
         */
        private string $twoFASecret;

        /**
         * @access private
         * @var    string|null
         */
        private ?string $session = null;

        /**
         * @access private
         * @var    DbCore|null - db connection
         */
        private null|DbCore $db = null;

        /**
         * @access public
         * @var    string userrole (admin, client, employee)
         */
        public string $role = '';

        public string $settings = '';

        /**
         * @access public
         * @var    int time for cookie
         */
        public int $cookieTime;

        /**
         * @access public
         * @var    string
         */
        public string $error = "";

        /**
         * @access public
         * @var    string
         */
        public string $success = "";

        /**
         * @access public
         * @var    string|bool
         */
        public string|bool $resetInProgress = false;

        /**
         * @access public
         * @var    object
         */
        public object $hasher;

        private static Auth $instance;

        /*
         * How often can a user reset a password before it has to be changed
         */
        public int $pwResetLimit = 5;

        private EnvironmentCore $config;
        private UserRepository $userRepo;

        /**
         * @param DbCore          $db
         * @param EnvironmentCore $config
         * @param UserRepository  $userRepo
         */
        public function __construct(
            DbCore $db,
            EnvironmentCore $config,
            UserRepository $userRepo
        ) {
            $this->db = $db;
            $this->config = $config;
            $this->userRepo = $userRepo;
        }

        /**
         * logout - destroy sessions and cookies
         *
         * @access private
         * @param $sessionId
         * @return bool
         */
        public function invalidateSession($sessionId): bool
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
         * @return bool
         */
        private function invalidateExpiredUserSessions(): bool
        {

            $query = "UPDATE zp_user SET session = '' WHERE (" . time() . " - sessionTime) > " . $this->config->sessionExpiration . " ";

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
         * @return array|false
         */
        public function getUserByLogin($username, $password): array|false
        {

            $user = $this->userRepo->getUserByEmail($username);

            if ($user !== false && password_verify($password, $user['password'])) {
                return $user;
            }

            return false;
        }

        /**
         * @param $username
         * @return array|false
         */
        public function getUserByEmail($username): array|false
        {
            return $this->userRepo->getUserByEmail($username);
        }

        /**
         * updateSession - Update the session time by sessionId
         *
         * @access public
         * @param $userId
         * @param  $sessionid
         * @param  $time
         * @return bool
         */
        public function updateUserSession($userId, $sessionid, $time): bool
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
        public function validateResetLink($hash): bool
        {

            $query = "SELECT id FROM zp_user WHERE pwReset = :resetLink AND status LIKE 'a' LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':resetLink', $hash, PDO::PARAM_STR);

            $stmn->execute();
            $returnValues = $stmn->fetch();
            $stmn->closeCursor();

            if ($returnValues !== false && count($returnValues) > 0) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * getUserByInviteLink - gets an invited user by invite code
         *
         * @access public
         * @param
         * @return array|bool
         */
        public function getUserByInviteLink($hash): bool|array
        {

            $query = "SELECT firstname, lastname, id, jobTitle FROM zp_user WHERE pwReset = :resetLink AND status LIKE 'i' LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':resetLink', $hash, PDO::PARAM_STR);

            $stmn->execute();
            $returnValues = $stmn->fetch();
            $stmn->closeCursor();

            return $returnValues;
        }

        /**
         * @param $username
         * @param $resetLink
         * @return bool
         */
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

        /**
         * @param $password
         * @param $hash
         * @return bool
         */
        public function changePW($password, $hash): bool
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

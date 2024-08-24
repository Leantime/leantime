<?php

namespace Leantime\Domain\Auth\Repositories;

use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use PDO;

/**
 *
 */
class Auth
{
    /**
     * @var int|null user id from DB
     */
    private ?int $userId = null;

    /**
     * @var int|null user id from DB
     */
    private ?int $clientId = null;

    /**
     * @var string|null username from db
     */
    private ?string $username = null;

    /**
     * @var string username from db
     */
    private string $name = '';

    /**
     * @var string profileid (image) from db
     */
    private string $profileId = '';

    /**
     * @var string|null
     */
    private ?string $password = null;

    /**
     * @var string|null username (emailaddress)
     */
    private ?string $user = null;

    /**
     * @var string|null username (emailaddress)
     */
    private ?string $mail = null;

    /**
     * @var bool $twoFAEnabled
     */
    private bool $twoFAEnabled;

    /**
     * @var string $twoFASecret
     */
    private string $twoFASecret;

    /**
     * @var string|null
     */
    private ?string $session = null;

    /**
     * @var DbCore|null - db connection
     */
    private null|DbCore $db = null;

    /**
     * @var string userrole (admin, client, employee)
     */
    public string $role = '';

    /**
     * @var string
     */
    public string $settings = '';

    /**
     * @var int time for cookie
     */
    public int $cookieTime;

    /**
     * @var string
     */
    public string $error = "";

    /**
     * @var string
     */
    public string $success = "";

    /**
     * @var string|bool
     */
    public string|bool $resetInProgress = false;

    /**
     * @var object
     */
    public object $hasher;

    private static Auth $instance;

    /**
     * How often can a user reset a password before it has to be changed
     *
     * @var int
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
     * @param string $sessionId
     *
     * @return bool
     */
    public function invalidateSession(string $sessionId): bool
    {
        $query = "UPDATE zp_user SET session = '' WHERE session = :sessionid LIMIT 1";

        $stmn = $this->db->database->prepare($query);
        $stmn->bindValue(':sessionid', $sessionId);
        $result = $stmn->execute();
        $stmn->closeCursor();

        return $result;
    }

    /**
     * checkSessions - check all sessions in the database and unset them if necessary
     *
     * @return bool
     */
    private function invalidateExpiredUserSessions(): bool
    {
        $query = "UPDATE zp_user SET session = '' WHERE (" . time() . " - sessionTime) > " . $this->config->sessionExpiration;

        $stmn = $this->db->database->prepare($query);
        $result = $stmn->execute();
        $stmn->closeCursor();

        return $result;
    }

    /**
     * getUserByLogin - Check login data and returns user if correct
     *
     * @param string $username
     * @param string $password
     *
     * @return array|false
     */
    public function getUserByLogin(string $username, string $password): array|false
    {
        $user = $this->userRepo->getUserByEmail($username);

        if ($user !== false && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    /**
     * @param string $username
     *
     * @return array|false
     */
    public function getUserByEmail(string $username): array|false
    {
        return $this->userRepo->getUserByEmail($username);
    }

    /**
     * updateSession - Update the session time by sessionId
     *
     * @param int    $userId
     * @param string $sessionid
     * @param string $time
     *
     * @return bool
     */
    public function updateUserSession(int $userId, string $sessionid, string $time): bool
    {

        $query = "UPDATE zp_user
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
        $stmn->bindValue(':sessionid', $sessionid);
        $stmn->bindValue(':time', $time);
        $result = $stmn->execute();

        $stmn->closeCursor();

        return $result;
    }

    /**
     * validateResetLink - validates that the password reset link belongs to a user account in the database
     *
     * @param string $hash
     *
     * @return bool
     */
    public function validateResetLink(string $hash): bool
    {

        $query = "SELECT id FROM zp_user WHERE pwReset = :resetLink AND status LIKE 'a' LIMIT 1";

        $stmn = $this->db->database->prepare($query);
        $stmn->bindValue(':resetLink', $hash);

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
     * @param string $hash
     *
     * @return array|bool
     */
    public function getUserByInviteLink(string $hash): bool|array
    {

        $query = "SELECT * FROM zp_user WHERE pwReset = :resetLink AND status LIKE 'i' LIMIT 1";

        $stmn = $this->db->database->prepare($query);
        $stmn->bindValue(':resetLink', $hash, PDO::PARAM_STR);

        $stmn->execute();
        $returnValues = $stmn->fetch();
        $stmn->closeCursor();

        return $returnValues;
    }

    /**
     * @param string $username
     * @param string $resetLink
     *
     * @return bool
     */
    public function setPWResetLink(string $username, string $resetLink): bool
    {

        $query = "UPDATE zp_user
            SET
                pwReset = :link,
                pwResetExpiration = :time,
                pwResetCount = IFNULL(pwResetCount, 0)+1
            WHERE
                username = :user
            LIMIT 1";

        $stmn = $this->db->database->prepare($query);
        $stmn->bindValue(':user', $username);
        $stmn->bindValue(':time', date("Y-m-d h:i:s", time()));
        $stmn->bindValue(':link', $resetLink);
        $result = $stmn->execute();
        $stmn->closeCursor();

        return $result;
    }

    /**
     * @param string $password
     * @param string $hash
     *
     * @return bool
     */
    public function changePW(string $password, string $hash): bool
    {

        $query = "UPDATE zp_user
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

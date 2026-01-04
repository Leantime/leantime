<?php

namespace Leantime\Domain\Auth\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Domain\Users\Repositories\Users as UserRepository;

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

    private ?string $password = null;

    /**
     * @var string|null username (emailaddress)
     */
    private ?string $user = null;

    /**
     * @var string|null username (emailaddress)
     */
    private ?string $mail = null;

    private bool $twoFAEnabled;

    private string $twoFASecret;

    private ?string $session = null;

    private ConnectionInterface $db;

    /**
     * @var string userrole (admin, client, employee)
     */
    public string $role = '';

    public string $settings = '';

    /**
     * @var int time for cookie
     */
    public int $cookieTime;

    public string $error = '';

    public string $success = '';

    public string|bool $resetInProgress = false;

    public object $hasher;

    private static Auth $instance;

    /**
     * How often can a user reset a password before it has to be changed
     */
    public int $pwResetLimit = 5;

    private EnvironmentCore $config;

    private UserRepository $userRepo;

    public function __construct(
        DbCore $db,
        EnvironmentCore $config,
        UserRepository $userRepo
    ) {
        $this->db = $db->getConnection();
        $this->config = $config;
        $this->userRepo = $userRepo;
    }

    /**
     * logout - destroy sessions and cookies
     */
    public function invalidateSession(string $sessionId): bool
    {
        return $this->db->table('zp_user')
            ->where('session', $sessionId)
            ->limit(1)
            ->update(['session' => '']) >= 0;
    }

    /**
     * checkSessions - check all sessions in the database and unset them if necessary
     */
    private function invalidateExpiredUserSessions(): bool
    {
        $expirationTime = time() - $this->config->sessionExpiration;

        return $this->db->table('zp_user')
            ->where('sessionTime', '<', $expirationTime)
            ->update(['session' => '']) >= 0;
    }

    /**
     * getUserByLogin - Check login data and returns user if correct
     */
    public function getUserByLogin(string $username, string $password): array|false
    {
        $user = $this->userRepo->getUserByEmail($username);

        if ($user !== false && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    public function getUserByEmail(string $username): array|false
    {
        return $this->userRepo->getUserByEmail($username);
    }

    /**
     * updateSession - Update the session time by sessionId
     */
    public function updateUserSession(int $userId, string $sessionid, string $time): bool
    {
        return $this->db->table('zp_user')
            ->where('id', $userId)
            ->limit(1)
            ->update([
                'lastlogin' => now(),
                'session' => $sessionid,
                'sessionTime' => $time,
                'pwReset' => null,
                'pwResetExpiration' => null,
            ]) >= 0;
    }

    /**
     * validateResetLink - validates that the password reset link belongs to a user account in the database
     */
    public function validateResetLink(string $hash): bool
    {
        return $this->db->table('zp_user')
            ->where('pwReset', $hash)
            ->where('status', 'like', 'a')
            ->exists();
    }

    /**
     * getUserByInviteLink - gets an invited user by invite code
     */
    public function getUserByInviteLink(string $hash): bool|array
    {
        $result = $this->db->table('zp_user')
            ->where('pwReset', $hash)
            ->where('status', 'like', 'i')
            ->limit(1)
            ->first();

        return $result ? (array) $result : false;
    }

    public function setPWResetLink(string $username, string $resetLink): bool
    {
        return $this->db->table('zp_user')
            ->where('username', $username)
            ->limit(1)
            ->update([
                'pwReset' => $resetLink,
                'pwResetExpiration' => now(),
                'pwResetCount' => $this->db->raw('COALESCE(pwResetCount, 0) + 1'),
            ]) >= 0;
    }

    public function changePW(string $password, string $hash): bool
    {
        return $this->db->table('zp_user')
            ->where('pwReset', $hash)
            ->limit(1)
            ->update([
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'pwReset' => '',
                'pwResetExpiration' => '',
                'lastpwd_change' => now(),
                'pwResetCount' => 0,
            ]) >= 0;
    }
}

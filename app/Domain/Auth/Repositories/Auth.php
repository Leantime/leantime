<?php

namespace Leantime\Domain\Auth\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Domain\Users\Repositories\Users as UserRepository;

class Auth
{
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

    /**
     * How often can a user reset a password before it has to be changed
     */
    public int $pwResetLimit = 5;

    private UserRepository $userRepo;

    private DatabaseHelper $dbHelper;

    public function __construct(
        DbCore $db,
        UserRepository $userRepo,
        DatabaseHelper $dbHelper
    ) {
        $this->db = $db->getConnection();
        $this->userRepo = $userRepo;
        $this->dbHelper = $dbHelper;
    }

    /**
     * logout - destroy sessions and cookies
     */
    public function invalidateSession(string $sessionId): bool
    {
        return $this->db->table('zp_user')
            ->where('session', $sessionId)
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
            ->update([
                'lastlogin' => now(),
                'session' => $sessionid,
                'sessiontime' => $time,
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
            ->where('pwResetExpiration', '>=', now())
            ->exists();
    }

    /**
     * getUserByInviteLink - gets an invited user by invite code
     */
    public function getUserByInviteLink(string $hash): bool|array
    {
        $result = $this->db->table('zp_user')
            ->where('pwReset', $hash)
            ->whereRaw('LOWER(status) = ?', ['i'])
            ->limit(1)
            ->first();

        return $result ? (array) $result : false;
    }

    public function setPWResetLink(string $username, string $resetLink): bool
    {
        return $this->db->table('zp_user')
            ->where('username', $username)
            ->update([
                'pwReset' => $resetLink,
                // Store the EXPIRY moment (not creation): the reset link is valid for 1 hour.
                'pwResetExpiration' => now()->addHours(1),
                'pwResetCount' => $this->db->raw('COALESCE('.$this->dbHelper->wrapColumn('pwResetCount').', 0) + 1'),
            ]) >= 0;
    }

    public function changePW(string $password, string $hash): bool
    {
        // Never match on an empty reset token: many accounts carry an empty
        // pwReset (it's cleared after every successful change), so an empty hash
        // would match a pile of users. Resolve to a single user id first, then
        // update by primary key. This also avoids the MySQL-only DELETE/UPDATE
        // ... LIMIT 1 that breaks on Postgres (#3384) — we can't drop limit(1)
        // here because pwReset isn't unique.
        if ($hash === '') {
            return false;
        }

        $userId = $this->db->table('zp_user')
            ->where('pwReset', $hash)
            ->where('pwResetExpiration', '>=', now())
            ->value('id');

        if (empty($userId)) {
            return false;
        }

        return $this->db->table('zp_user')
            ->where('id', $userId)
            ->update([
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'pwReset' => '',
                'pwResetExpiration' => '',
                'lastpwd_change' => now(),
                'pwResetCount' => 0,
            ]) >= 0;
    }
}

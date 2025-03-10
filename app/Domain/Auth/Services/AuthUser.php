<?php

namespace Leantime\Domain\Auth\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Laravel\Sanctum\HasApiTokens;
use Leantime\Domain\Auth\Services\Auth as AuthService;

class AuthUser implements UserProvider
{
    use HasApiTokens;

    protected $authRepo;

    protected $userRepo;

    protected $userdata;

    public function __construct(
        protected AuthService $authService)
    {
        $this->authRepo = $this->authService->authRepo;
        $this->userRepo = $this->authService->userRepo;
    }

    public function retrieveById($identifier)
    {
        return (object) $this->userRepo->getUser($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        return (object) $this->authService->getUserByToken($token);
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // Not implemented for now
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (! isset($credentials['username'])) {
            return null;
        }

        return $this->authRepo->getUserByLogin(
            $credentials['username'],
            $credentials['password'] ?? null
        );
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->authService->login(
            $credentials['username'],
            $credentials['password']
        );
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false) {}

    public function getOrCreateUser($user, $source)
    {

        $user = $this->authRepo->getUserByEmail($user['email']);

        if (empty($user) && config()->get('auth.create_user')) {

            $userArray = [
                'firstname' => $user['firstname'],
                'lastname' => $user['lastname'],
                'phone' => $user['phone'] ?? '',
                'user' => $user['email'] ?? '',
                'role' => $user['role'] ?? '30',
                'department' => $user['department'] ?? '',
                'jobTitle' => $user['jobTitle'] ?? '',
                'jobLevel' => $user['jobLevel'] ?? '',
                'password' => '',
                'clientId' => '',
                'source' => $source,
                'status' => 'a',
            ];

            $userId = $this->userRepo->addUser($userArray);
            $user = $this->authRepo->getUserByEmail($user['email']);
        }

        return $user;
    }

    public function setUser($userId)
    {
        $this->userdata = $this->userRepo->getUser($userId);

        $this->setUserSession($this->userdata);
    }

    protected function setUserSession($user)
    {
        $currentUser = [
            'id' => (int) $user['id'],
            'name' => strip_tags($user['firstname']),
            'profileId' => $user['profileId'],
            'mail' => filter_var($user['username'], FILTER_SANITIZE_EMAIL),
            'clientId' => $user['clientId'],
            'role' => $user['role'],
            'settings' => $user['settings'] ? unserialize($user['settings']) : [],
            'twoFAEnabled' => $user['twoFAEnabled'] ?? false,
            'twoFAVerified' => true, // Auto-verify for API tokens
            'twoFASecret' => $user['twoFASecret'] ?? '',
            'isExternalAuth' => false,
            'createdOn' => ! empty($user['createdOn']) ? dtHelper()->parseDbDateTime($user['createdOn']) : dtHelper()->userNow(),
            'modified' => ! empty($user['modified']) ? dtHelper()->parseDbDateTime($user['modified']) : dtHelper()->userNow(),
        ];

        session(['userdata' => $currentUser]);
    }
}

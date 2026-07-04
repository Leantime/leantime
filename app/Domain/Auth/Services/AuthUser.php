<?php

namespace Leantime\Domain\Auth\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Laravel\Sanctum\HasApiTokens;
use Leantime\Domain\Auth\Models\AuthenticatableUser;
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
        $userData = $this->userRepo->getUser($identifier);

        // Not found → null, per the UserProvider contract. Returning a (non-null) empty user
        // object would let the guard treat the request as authenticated.
        if (empty($userData)) {
            return null;
        }

        return new AuthenticatableUser((array) $userData);
    }

    public function retrieveByToken($identifier, $token)
    {
        $userData = $this->authService->getUserByToken($token);

        if (empty($userData)) {
            return null;
        }

        return new AuthenticatableUser((array) $userData);
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
        // Look up the existing account in a separate variable — the $user param holds the
        // external/OAuth profile data we need to create the account from, so it must not be
        // overwritten by the lookup result (doing so previously built new users with empty fields).
        $existingUser = $this->authRepo->getUserByEmail($user['email']);

        if (empty($existingUser) && config()->get('auth.create_user')) {

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

            $this->userRepo->addUser($userArray);
            $existingUser = $this->authRepo->getUserByEmail($user['email']);
        }

        return $existingUser;
    }

    public function setUser($userId)
    {
        $this->userdata = $this->userRepo->getUser($userId);

        $this->setUserSession($this->userdata);
    }

    protected function setUserSession($user)
    {
        // Sanctum/Bearer-token session. twoFAVerified: true — the token is the strong credential
        // and no interactive 2FA is possible. Built via the shared factory so role (NAME string,
        // not raw int) and every other field stay identical to the web + x-api-key paths.
        session(['userdata' => UserSessionBuilder::build($user, isExternalAuth: false, twoFAVerified: true)]);
    }
}

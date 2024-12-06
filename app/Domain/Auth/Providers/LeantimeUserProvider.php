<?php

namespace Leantime\Domain\Auth\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Leantime\Domain\Auth\Services\Auth as AuthService;

class LeantimeUserProvider implements UserProvider
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function retrieveById($identifier)
    {
        return $this->authService->getUserById($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        return null; // Not implemented for now
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

        return $this->authService->getUserByLogin(
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

    public function rehashPasswordIfRequired() {}
}

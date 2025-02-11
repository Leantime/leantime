<?php

namespace Leantime\Domain\Auth\Guards;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Leantime\Domain\Auth\Services\Auth as AuthService;

class WebGuard implements Guard
{
    protected $provider;

    protected $user;

    protected AuthService $authService;

    public function __construct(UserProvider $provider, AuthService $authService)
    {
        $this->provider = $provider;
        $this->authService = $authService;
    }

    public function check()
    {
        return $this->authService->loggedIn();
    }

    public function guest()
    {
        return ! $this->check();
    }

    public function user()
    {
        if ($this->user !== null) {
            return $this->user;
        }

        if ($this->authService->loggedIn()) {
            $this->user = $this->provider->retrieveById($this->authService::getUserId());
        }

        return $this->user;
    }

    public function hasUser()
    {
        return $this->user ? true : false;
    }

    public function id()
    {
        if ($this->user()) {
            return $this->user()->id;
        }
    }

    public function validate(array $credentials = [])
    {
        return $this->authService->login(
            $credentials['username'],
            $credentials['password']
        );
    }

    public function setUser(Authenticatable $user)
    {
        $this->user = $user;

        return $this;
    }
}

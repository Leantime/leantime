<?php

namespace Leantime\Domain\Auth\Guards;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Domain\Api\Services\Api;

class ApiGuard implements Guard
{
    protected $user;

    private string $apiKey;

    public function __construct(
        protected UserProvider $provider,
        protected Api $apiService,
        protected IncomingRequest $request)
    {
        if ($this->request->isApiRequest()) {
            $this->apiKey = $this->request->getAPIKey();
        }
    }

    public function check()
    {

        if (empty($this->apiKey)) {
            return false;
        }

        $apiUser = $this->apiService->getAPIKeyUser($this->apiKey);

        if (! $apiUser) {
            return false;
        }

        return true;
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

        if (empty($this->apiKey)) {
            return $this->user;
        }

        $apiUser = $this->apiService->getAPIKeyUser($this->apiKey);

        if (! $apiUser) {
            $this->user = null;

            return $this->user;
        }

        $this->user = (object) $apiUser;

        return $this->user;
    }

    public function id()
    {
        if ($this->user()) {
            return $this->user()->id;
        }
    }

    public function validate(array $credentials = [])
    {

        if (empty($this->apiKey)) {
            return false;
        }

        $apiUser = $this->apiService->getAPIKeyUser($this->apiKey);

        if (! $apiUser) {
            return false;
        }

        return true;

    }

    public function hasUser()
    {
        return $this->user ? true : false;
    }

    public function setUser(Authenticatable $user)
    {
        $this->user = $user;

        return $this;
    }
}

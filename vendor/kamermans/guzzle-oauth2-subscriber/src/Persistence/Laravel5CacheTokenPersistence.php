<?php

namespace kamermans\OAuth2\Persistence;

use Illuminate\Contracts\Cache\Repository;
use kamermans\OAuth2\Token\TokenInterface;

class Laravel5CacheTokenPersistence implements TokenPersistenceInterface
{
    /**
     * @var Repository
     */
    private $cache;

    /**
     * @var string
     */
    private $cacheKey;

    public function __construct(Repository $cache, $cacheKey = 'guzzle-oauth2-token')
    {
        $this->cache = $cache;
        $this->cacheKey = $cacheKey;
    }

    public function saveToken(TokenInterface $token)
    {
        $this->cache->forever($this->cacheKey, $token->serialize());
    }

    public function restoreToken(TokenInterface $token)
    {
        $data = $this->cache->get($this->cacheKey);

        if (!is_array($data)) {
            return null;
        }

        return $token->unserialize($data);
    }

    public function deleteToken()
    {
        $this->cache->forget($this->cacheKey);
    }

    public function hasToken()
    {
        return $this->cache->has($this->cacheKey);
    }
}

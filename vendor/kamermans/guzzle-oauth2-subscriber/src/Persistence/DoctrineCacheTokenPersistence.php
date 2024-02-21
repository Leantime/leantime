<?php

namespace kamermans\OAuth2\Persistence;

use Doctrine\Common\Cache\Cache;
use kamermans\OAuth2\Token\TokenInterface;

class DoctrineCacheTokenPersistence implements TokenPersistenceInterface
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var string
     */
    private $cacheKey;

    public function __construct(Cache $cache, $cacheKey = 'guzzle-oauth2-token')
    {
        $this->cache = $cache;
        $this->cacheKey = $cacheKey;
    }

    public function saveToken(TokenInterface $token)
    {
        $this->cache->save($this->cacheKey, $token->serialize());
    }

    public function restoreToken(TokenInterface $token)
    {
        $data = $this->cache->fetch($this->cacheKey);

        if (!is_array($data)) {
            return null;
        }

        return $token->unserialize($data);
    }

    public function deleteToken()
    {
        $this->cache->delete($this->cacheKey);
    }

    public function hasToken()
    {
        return $this->cache->contains($this->cacheKey);
    }
}

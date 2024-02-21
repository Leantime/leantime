<?php

namespace kamermans\OAuth2\Token;

class RawToken implements Serializable, TokenInterface
{
    // Pull in serialize() and unserialize() methods
    use TokenSerializer;

    /**
     * @param string $accessToken
     * @param string $refreshToken
     * @param int    $expiresAt
     */
    public function __construct($accessToken = null, $refreshToken = null, $expiresAt = null)
    {
        $this->accessToken  = (string) $accessToken;
        $this->refreshToken = (string) $refreshToken;
        $this->expiresAt    = (int) $expiresAt;
    }

    /**
     * @return string The access token
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @return string|null The refresh token
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @return int The expiration timestamp
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @return boolean
     */
    public function isExpired()
    {
        return $this->expiresAt && $this->expiresAt < time();
    }
}

<?php

namespace kamermans\OAuth2\Token;

trait TokenSerializer
{
    /**
     * Access Token.
     *
     * @var string
     */
    private $accessToken;

    /**
     * Refresh Token.
     *
     * @var string
     */
    private $refreshToken;

    /**
     * Expiration timestamp.
     *
     * @var int
     */
    private $expiresAt;

    /**
     * Serialize Token data
     * @return string Token data
     */
    public function serialize()
    {
        return [
            'access_token'  => $this->accessToken,
            'refresh_token' => $this->refreshToken,
            'expires_at'    => $this->expiresAt,
        ];
    }

    /**
     * Unserialize token data
     * @return self
     */
    public function unserialize(array $data)
    {
        if (!isset($data['access_token'])) {
            throw new \InvalidArgumentException('Unable to create a RawToken without an "access_token"');
        }

        $this->accessToken = $data['access_token'];
        $this->refreshToken = isset($data['refresh_token']) ? $data['refresh_token'] : null;
        $this->expiresAt = isset($data['expires_at']) ? $data['expires_at'] : null;

        return $this;
    }
}

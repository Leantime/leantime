<?php

namespace kamermans\OAuth2\Token;

interface TokenInterface
{
    /**
     * @return string The access token
     */
    public function getAccessToken();

    /**
     * @return string|null The refresh token
     */
    public function getRefreshToken();

    /**
     * @return int The expiration date as a timestamp
     */
    public function getExpiresAt();

    /**
     * @return boolean
     */
    public function isExpired();
}

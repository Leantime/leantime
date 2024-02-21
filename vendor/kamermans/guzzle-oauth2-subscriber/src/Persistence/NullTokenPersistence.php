<?php

namespace kamermans\OAuth2\Persistence;

use kamermans\OAuth2\Token\TokenInterface;

class NullTokenPersistence implements TokenPersistenceInterface
{
    public function saveToken(TokenInterface $token)
    {
        return;
    }

    public function restoreToken(TokenInterface $token)
    {
        return null;
    }

    public function deleteToken()
    {
        return;
    }

    public function hasToken()
    {
        return false;
    }
}

<?php

namespace kamermans\OAuth2\Persistence;

use kamermans\OAuth2\Token\TokenInterface;

interface TokenPersistenceInterface
{
    /**
     * Restore the token data into the give token.
     *
     * @param TokenInterface $token
     *
     * @return TokenInterface Restored token
     */
    public function restoreToken(TokenInterface $token);

    /**
     * Save the token data.
     *
     * @param TokenInterface $token
     */
    public function saveToken(TokenInterface $token);

    /**
     * Delete the saved token data.
     */
    public function deleteToken();

    /**
     * Returns true if a token exists (although it may not be valid)
     *
     * @return bool
     */
    public function hasToken();
}

<?php

namespace kamermans\OAuth2\GrantType;

use kamermans\OAuth2\Signer\ClientCredentials\SignerInterface;

interface GrantTypeInterface
{
    /**
     * Get the token data returned by the OAuth2 server.
     *
     * @param SignerInterface $clientCredentialsSigner
     * @param string          $refreshToken
     *
     * @return array
     */
    public function getRawData(SignerInterface $clientCredentialsSigner, $refreshToken = null);
}

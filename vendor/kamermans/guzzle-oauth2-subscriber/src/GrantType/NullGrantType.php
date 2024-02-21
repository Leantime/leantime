<?php

namespace kamermans\OAuth2\GrantType;

use kamermans\OAuth2\Signer\ClientCredentials\SignerInterface;
use kamermans\OAuth2\Exception\ReauthorizationException;

/**
 * Null grant type, used for manually-obtained access tokens.
 */
class NullGrantType implements GrantTypeInterface
{
    public function getRawData(SignerInterface $clientCredentialsSigner, $refreshToken = null)
    {
        throw new ReauthorizationException("No access token is present and there is no way to obtain one with NullGrantType");
    }
}

<?php

namespace kamermans\OAuth2\Signer\ClientCredentials;

interface SignerInterface
{
    /**
     * Signs the given request using the provided client ID and Secret.
     *
     * @param object  $request
     * @param string  $clientId      OAuth client identifier
     * @param string  $clientSecret  OAuth client secret
     */
    public function sign($request, $clientId, $clientSecret);
}

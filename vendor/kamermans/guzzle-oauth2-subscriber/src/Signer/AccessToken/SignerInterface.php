<?php

namespace kamermans\OAuth2\Signer\AccessToken;

interface SignerInterface
{
    /**
     * @param object  $request
     * @param string  $accessToken
     */
    public function sign($request, $accessToken);
}

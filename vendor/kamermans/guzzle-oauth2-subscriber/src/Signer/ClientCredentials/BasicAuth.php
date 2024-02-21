<?php

namespace kamermans\OAuth2\Signer\ClientCredentials;

use kamermans\OAuth2\Utils\Helper;

class BasicAuth implements SignerInterface
{
    public function sign($request, $clientId, $clientSecret)
    {
        if (Helper::guzzleIs('>=', 6)) {
            return $request->withHeader('Authorization', 'Basic ' .  base64_encode($clientId . ':' . $clientSecret));
        }

        $request->getConfig()->set('auth', 'basic');
        $request->setHeader('Authorization', 'Basic ' . base64_encode($clientId . ':' . $clientSecret));
        return $request;
    }
}

<?php

namespace kamermans\OAuth2\Signer\AccessToken;

use kamermans\OAuth2\Utils\Helper;

class BearerAuth implements SignerInterface
{
    public function sign($request, $accessToken)
    {
        if (Helper::guzzleIs('>=', 6)) {
            return $request->withHeader('Authorization', 'Bearer ' . $accessToken);
        }

        $request->setHeader('Authorization', 'Bearer ' . $accessToken);

        return $request;
    }
}

<?php

namespace kamermans\OAuth2\Signer\AccessToken;

use kamermans\OAuth2\Utils\Helper;

class QueryString implements SignerInterface
{
    private $fieldName;

    public function __construct($fieldName = 'access_token')
    {
        $this->fieldName = $fieldName;
    }

    public function sign($request, $accessToken)
    {
        if (Helper::guzzleIs('>=', 6)) {
            $uri = \GuzzleHttp\Psr7\Uri::withQueryValue(
                    $request->getUri(),
                    $this->fieldName,
                    $accessToken
            );

            return $request->withUri($uri);
        }

        $request->getQuery()->set($this->fieldName, $accessToken);
        return $request;
    }
}

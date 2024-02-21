<?php

namespace kamermans\OAuth2\Signer\ClientCredentials;

use kamermans\OAuth2\Utils\Helper;
use GuzzleHttp\Post\PostBodyInterface;

class PostFormData implements SignerInterface
{
    private $clientIdField;
    private $clientSecretField;

    public function __construct($clientIdField = 'client_id', $clientSecretField = 'client_secret')
    {
        $this->clientIdField = $clientIdField;
        $this->clientSecretField = $clientSecretField;
    }

    public function sign($request, $clientId, $clientSecret)
    {
        if (Helper::guzzleIs('>=', 6)) {
            if ($request->getHeaderLine('Content-Type') != 'application/x-www-form-urlencoded') {
                throw new \RuntimeException('Unable to set fields in request body');
            }

            parse_str($request->getBody(), $data);
            $data[$this->clientIdField] = $clientId;
            $data[$this->clientSecretField] = $clientSecret;

            $body_stream = Helper::streamFor(http_build_query($data, '', '&'));
            return $request->withBody($body_stream);
        }

        $body = $request->getBody();

        if (!($body instanceof PostBodyInterface)) {
            throw new \RuntimeException('Unable to set fields in request body');
        }

        $body->setField($this->clientIdField, $clientId);
        $body->setField($this->clientSecretField, $clientSecret);

        return $request;
    }
}

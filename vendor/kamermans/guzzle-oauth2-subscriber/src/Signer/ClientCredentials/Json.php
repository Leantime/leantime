<?php

namespace kamermans\OAuth2\Signer\ClientCredentials;

use GuzzleHttp\Stream\Stream;
use kamermans\OAuth2\Utils\Helper;

class Json implements SignerInterface
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
        }

        parse_str((string) $request->getBody(), $data);

        unset($data['client_id'], $data['client_secret']);

        $data[$this->clientIdField] = $clientId;
        $data[$this->clientSecretField] = $clientSecret;

        $body_stream = json_encode($data);

        if (Helper::guzzleIs('>=', 6)) {
            return $request
                ->withHeader('Content-Type', 'application/json')
                ->withBody(Helper::streamFor($body_stream));
        }

        $request->setHeader('Content-Type', 'application/json');
        $request->setBody(Stream::factory($body_stream));
        return $request;
    }
}

<?php

namespace kamermans\OAuth2\GrantType;

use GuzzleHttp\Post\PostBody;
use GuzzleHttp\ClientInterface;
use kamermans\OAuth2\Utils\Helper;
use kamermans\OAuth2\Utils\Collection;
use kamermans\OAuth2\Signer\ClientCredentials\SignerInterface;

/**
 * Refresh token grant type.
 *
 * @link http://tools.ietf.org/html/rfc6749#section-6
 */
class RefreshToken implements GrantTypeInterface
{
    /**
     * The token endpoint client.
     *
     * @var ClientInterface
     */
    private $client;

    /**
     * Configuration settings.
     *
     * @var Collection
     */
    private $config;

    public function __construct(ClientInterface $client, $config)
    {
        $this->client = $client;
        $this->config = Collection::fromConfig(
            $config,
            // Defaults
            [
                'client_secret' => '',
                'refresh_token' => '',
                'scope' => '',
            ],
            // Required
            [
                'client_id',
            ]
        );
    }

    public function getRawData(SignerInterface $clientCredentialsSigner, $refreshToken = null)
    {
        if (Helper::guzzleIs('>=', 6)) {
            $request = (new \GuzzleHttp\Psr7\Request('POST', ''))
                        ->withBody($this->getPostBody($refreshToken))
                        ->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        } else {
            $request = $this->client->createRequest('POST', null);
            $request->setBody($this->getPostBody($refreshToken));
        }

        $request = $clientCredentialsSigner->sign(
            $request,
            $this->config['client_id'],
            $this->config['client_secret']
        );

        $response = $this->client->send($request);
        $rawData = json_decode($response->getBody(), true);

        return is_array($rawData) ? $rawData : [];
    }

    /**
     * @return PostBody|\Psr\Http\Message\StreamInterface
     */
    protected function getPostBody($refreshToken)
    {
        if (Helper::guzzleIs('>=', '6')) {
            $data = [
                'grant_type' => 'refresh_token',
                // If no refresh token was provided to the method, use the one
                // provided to the constructor.
                'refresh_token' => $refreshToken ?: $this->config['refresh_token'],
           ];

            if ($this->config['scope']) {
                $data['scope'] = $this->config['scope'];
            }

            return Helper::streamFor(http_build_query($data, '', '&'));
        }

        $postBody = new PostBody();
        $postBody->replaceFields([
            'grant_type' => 'refresh_token',
            // If no refresh token was provided to the method, use the one
            // provided to the constructor.
            'refresh_token' => $refreshToken ?: $this->config['refresh_token'],
       ]);

        if ($this->config['scope']) {
            $postBody->setField('scope', $this->config['scope']);
        }

        return $postBody;
    }
}

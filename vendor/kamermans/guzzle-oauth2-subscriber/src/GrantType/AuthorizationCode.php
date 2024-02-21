<?php

namespace kamermans\OAuth2\GrantType;

use GuzzleHttp\Post\PostBody;
use GuzzleHttp\ClientInterface;
use kamermans\OAuth2\Utils\Helper;
use kamermans\OAuth2\Utils\Collection;
use kamermans\OAuth2\Signer\ClientCredentials\SignerInterface;

/**
 * Authorization code grant type.
 *
 * @link http://tools.ietf.org/html/rfc6749#section-4.1
 */
class AuthorizationCode implements GrantTypeInterface
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

    public function __construct(ClientInterface $client, array $config)
    {
        $this->client = $client;
        $this->config = Collection::fromConfig(
            $config,
            // Defaults
            [
                'client_secret' => '',
                'scope' => '',
                'redirect_uri' => '',
            ],
            // Required
            [
                'client_id',
                'code',
            ]
        );
    }

    public function getRawData(SignerInterface $clientCredentialsSigner, $refreshToken = null)
    {
        if (Helper::guzzleIs('>=', 6)) {
            $request = (new \GuzzleHttp\Psr7\Request('POST', ''))
                        ->withBody($this->getPostBody())
                        ->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        } else {
            $request = $this->client->createRequest('POST', null);
            $request->setBody($this->getPostBody());
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
    protected function getPostBody()
    {
        if (Helper::guzzleIs('>=', '6')) {
            $data = [
                'grant_type' => 'authorization_code',
                'code' => $this->config['code'],
            ];

            if ($this->config['scope']) {
                $data['scope'] = $this->config['scope'];
            }

            if ($this->config['redirect_uri']) {
                $data['redirect_uri'] = $this->config['redirect_uri'];
            }

            return Helper::streamFor(http_build_query($data, '', '&'));
        }

        $postBody = new PostBody();
        $postBody->replaceFields([
            'grant_type' => 'authorization_code',
            'code' => $this->config['code'],
        ]);

        if ($this->config['scope']) {
            $postBody->setField('scope', $this->config['scope']);
        }

        if ($this->config['redirect_uri']) {
            $postBody->setField('redirect_uri', $this->config['redirect_uri']);
        }

        return $postBody;
    }
}

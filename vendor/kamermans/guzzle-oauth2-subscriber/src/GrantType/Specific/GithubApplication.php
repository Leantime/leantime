<?php

namespace kamermans\OAuth2\GrantType\Specific;

use GuzzleHttp\ClientInterface;
use kamermans\OAuth2\Utils\Collection;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use kamermans\OAuth2\Utils\Helper;
use kamermans\OAuth2\GrantType\ClientCredentials;
use kamermans\OAuth2\GrantType\GrantTypeInterface;
use kamermans\OAuth2\Signer\ClientCredentials\SignerInterface;

/**
 * GitHub Application-specific grant type. Like ClientCredentials, but uses
 * github.com username/password via basic auth and client_id/client_secret via JSON
 * to create an access_token.
 */
class GithubApplication implements GrantTypeInterface
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

    /**
     * @param ClientInterface $client
     * @param array           $config
     */
    public function __construct(ClientInterface $client, array $config)
    {
        $this->client = $client;
        $this->config = Collection::fromConfig(
            $config,
            // Defaults
            [
                'scope' => '',
            ],
            // Required
            [
                'client_id',
                'client_secret',
                'note',
                'username',
                'password',
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

        $clientCredentialsSigner->sign(
            $request,
            $this->config['username'],
            $this->config['password']
        );

        $response = $this->client->send($request);

        // Restructure some fields from the GitHub response
        /* Example Response:
        {
          "id": 00913101,
          "url": "https://api.github.com/authorizations/00913101",
          "app":
          {
            "name": "OAuthTestApplication",
            "url": "http://localhost/test",
            "client_id": "042c2d7a8a216e2bbf82",
          },
          "token": "ab3758bd55c324cfee74c87fcc704656af6d98f6",
          "note": "OAuth Test Token",
          "note_url": NULL,
          "created_at": "2014-10-10T19:05:00Z",
          "updated_at": "2014-10-10T19:05:00Z",
          "scopes":
          [
            "public_repo",
            "repo",
            "user",
          ],
        }
        */

        $data = json_decode($response->getBody(), true);
        $data['access_token'] = $data['token'];
        unset($data['token']);

        return $data;
    }

    protected function getPostBody()
    {
        $postBody = [
            'client_id'     => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'note'          => $this->config['note'],
            'scopes'        => [],
        ];

        if ($this->config['scope']) {
            // In github's API, "scope" is called "scopes" and is passed as a JSON array
            $postBody['scopes'] = explode(',', $this->config['scope']);
        }

        if ($this->config['note_url']) {
            $postBody['note_url'] = $this->config['note_url'];
        }

        $postBody = json_encode($postBody);

        return Helper::guzzleIs('<', 6)? Stream::factory($postBody): Helper::streamFor($postBody);
    }

    /**
     * Helper function to parse the GitHub HTTP Response header "Link", which
     * contains links to next and/or previous "pages" of data.
     *
     * @param GuzzleHttpMessageResponse $response
     *
     * @return array Array containing keys: next, prev, first, last
     */
    public static function parseLinkHeader(Response $response)
    {
        $linkHeader = $response->getHeader('Link');

        if (!strpos($linkHeader, 'rel')) {
            return null;
        }

        $out = [
            "next" => null,
            "last" => null,
            "prev" => null,
            "first" => null,
        ];

        $links = explode(',', $linkHeader);
        foreach ($links as $link) {
            $parts = explode(';', $link);
            if (count($parts) < 2) {
                continue;
            }

            // Get the URL
            $url = trim(array_shift($parts), '<> ');
            $relParts = explode('=', trim(array_shift($parts)));

            if (count($relParts) !== 2 || $relParts[0] != 'rel') {
                continue;
            }

            // Get the rel="" value (next, prev, first, last)
            $rel = trim($relParts[1], ' "\'');
            $out[$rel] = $url;
        }

        return $out;
    }

    /**
     * Helper function to retrieve all the "pages" of results from a GitHub API call
     * and returns them as a single array
     *
     * @param  ClientInterface $client
     * @param  string $url
     * @return array
     */
    public static function getAllResults(ClientInterface $client, $url)
    {
        $data = [];
        do {
            $response = $client->get($url);
            $data = array_merge($data, $response->json());

            $url = GithubApplication::parseLinkHeader($response)['next'];
        } while ($url);

        return $data;
    }
}

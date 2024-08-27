<?php

namespace Leantime\Core\Http\Client;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use kamermans\OAuth2\GrantType\AuthorizationCode;
use kamermans\OAuth2\GrantType\ClientCredentials;
use kamermans\OAuth2\GrantType\GrantTypeInterface;
use kamermans\OAuth2\GrantType\PasswordCredentials;
use kamermans\OAuth2\GrantType\RefreshToken;
use kamermans\OAuth2\OAuth2Middleware;

/**
 * ApiSession - Creates a Guzzle Client with a connection
 *
 * @package    leantime
 * @subpackage core
 */
class ApiClient
{
    /**
     * Checks passed credentials to see if they are properly provided
     *
     * @see https://github.com/kamermans/guzzle-oauth2-subscriber#middleware-guzzle-6
     * @param array $requiredCreds
     * @param array $creds
     * @param array $optionalCreds (optional)
     * @return bool
     */
    private static function checkCreds(
        array $requiredCreds,
        array $creds,
        array $optionalCreds = []
    ): bool {
        if (!empty($optionalCreds)) {
            foreach ($optionalCreds as $optionalCred) {
                if (isset($creds[$optionalCred])) {
                    unset($creds[$optionalCred]);
                }
            }
        }

        if (empty($creds) || !empty(array_diff($requiredCreds, array_keys($creds)))) {
            return false;
        }

        return true;
    }

    /**
     * Creates a Guzzle Client with an oAuth2 connection
     *
     * @see https://github.com/kamermans/guzzle-oauth2-subscriber#client-credentials-example
     * @param string       $baseUri
     * @param HandlerStack $stack
     * @param array        $requestDefaults
     * @return Client
     */
    public static function oAuth2(
        string $baseUri,
        HandlerStack $stack,
        array $requestDefaults = []
    ): Client {
        return new Client(
            array_merge_recursive(
                $requestDefaults,
                [
                    'base_uri' => $baseUri,
                    'handler' => $stack,
                    'auth' => 'oauth',
                ]
            )
        );
    }

    /**
     * Creates a handler for oAuth2 Client
     *
     * @see https://github.com/kamermans/guzzle-oauth2-subscriber
     * @param string                  $baseUri
     * @param array                   $creds           Just pass an empty array if you supply $customGrantType.
     * @param bool                    $usesRefresh     (optional)
     * @param GrantTypeInterface|null $customGrantType (optional)
     * @return HandlerStack
     */
    public static function oAuth2Grants(
        string $baseUri,
        array $creds,
        bool $usesRefresh = false,
        GrantTypeInterface $customGrantType = null
    ): HandlerStack {
        $middleware_params = [];

        if ($customGrantType == null) {
            $requiredCreds = [
                'client_id',
                'client_secret',
            ];
            $optionalCreds = [
                'scope',
                'state',
                'redirect_uri',
                'code',
            ];

            if (!self::checkCreds($requiredCreds, $creds, $optionalCreds)) {
                throw new \Error(
                    "oAuth2 credentials were incorrectly provided"
                );
            }

            $client = new Client(['base_uri' => $baseUri]);

            if (in_array('code', $creds)) {
                $middleware_params[] = new AuthorizationCode($client, $creds);
            } elseif (in_array('username', $creds) && in_array('password', $creds)) {
                $middleware_params[] = new PasswordCredentials($client, $creds);
            } else {
                $middleware_params[] = new ClientCredentials($client, $creds);
            }

            if ($usesRefresh) {
                $middleware_params[] = new RefreshToken($client, $creds);
            }

        } else {
            $middleware_params[] = $customGrantType;
        }



        $stack = HandlerStack::create();
        $oauth = new OAuth2Middleware(...$middleware_params);
        $stack->push($oauth);

        return $stack;
    }

    /**
     * Creates a Guzzle Client with an oAuth1 connection
     *
     * @see https://github.com/guzzle/oauth-subscriber#using-the-subscriber
     * @param string $baseUri
     * @param array  $creds
     * @param array  $requestDefaults (optional)
     * @return Client
     */
    public static function oAuth1(
        string $baseUri,
        array $creds,
        array $requestDefaults = []
    ): Client {
        $requiredCreds = [
            'consumer_key',
            'consumer_secret',
            'token',
            'token_secret',
        ];
        $optionalCreds = [
            'private_key_file',
            'private_key_passphrase',
            'signature_method',
        ];

        if (!self::checkCreds($requiredCreds, $creds, $optionalCreds)) {
            throw new \Error(
                "oAuth1 credentials were incorrectly provided"
            );
        }

        $stack = HandlerStack::create();
        $middleware = new Oauth1($creds);
        $stack->push($middleware);

        return new Client(
            array_merge_recursive(
                $requestDefaults,
                [
                    'base_uri' => $baseUri,
                    'auth' => 'oauth',
                    'handler' => $stack,
                ]
            )
        );
    }

    /**
     * Creates a Guzzle Client with a basic authentication connection
     *
     * @see https://docs.guzzlephp.org/en/latest/request-options.html#auth
     * @param string $baseUri
     * @param array  $creds
     * @param array  $requestDefaults (optional)
     * @return Client
     */
    public static function basicAuth(
        string $baseUri,
        array $creds,
        array $requestDefaults = []
    ): Client {
        $requiredCreds = [
            'username',
            'password',
        ];

        if (!self::checkCreds($requiredCreds, $creds)) {
            throw new \Error(
                "basic auth credentials must match exactly: ['username' => ..., 'password' => ...]"
            );
        }

        return new Client(
            array_merge_recursive(
                $requestDefaults,
                [
                    'base_uri' => $baseUri,
                    'auth' => $creds,
                ]
            )
        );
    }

    /**
     * Creates a Guzzle Client with a digest connection
     *
     * @see https://docs.guzzlephp.org/en/latest/request-options.html#auth
     * @param string $baseUri
     * @param array  $creds
     * @param array  $requestDefaults (optional)
     * @return Client
     */
    public static function digest(
        string $baseUri,
        array $creds,
        array $requestDefaults = []
    ): Client {
        $requiredCreds = [
            'username',
            'password',
            'digest',
        ];

        if (!self::checkCreds($requiredCreds, $creds)) {
            throw new \Error(
                "basic auth credentials must match exactly: ['username' => ..., 'password' => ..., 'digest' => ...]"
            );
        }

        return new Client(
            array_merge_recursive([
                $requestDefaults,
                [
                    'base_uri' => $baseUri,
                    'auth' => $creds,
                ],
            ])
        );
    }

    /**
     * Creates a Guzzle Client with a ntlm connection
     *
     * @see https://docs.guzzlephp.org/en/latest/request-options.html#auth
     * @param string $baseUri
     * @param array  $creds
     * @param array  $requestDefaults (optional)
     * @return Client
     */
    public static function ntlm(
        string $baseUri,
        array $creds,
        array $requestDefaults = []
    ): Client {
        $requiredCreds = [
            'username',
            'password',
            'ntlm',
        ];

        if (!self::checkCreds($requiredCreds, $creds)) {
            throw new \Error(
                "basic auth credentials must match exactly: ['username' => ..., 'password' => ..., 'ntlm' => ...]"
            );
        }

        return new Client(
            array_merge_recursive(
                $requestDefaults,
                [
                    'base_uri' => $baseUri,
                    'auth' => $creds,
                ]
            )
        );
    }

    /**
     * Creates a Guzzle Client with a token/apikey connection
     *
     * @param string $baseUri
     * @param array  $creds
     * @param array  $requestDefaults (optional)
     * @return Client
     */
    public static function bearerToken(
        string $baseUri,
        array $creds,
        array $requestDefaults = []
    ): Client {
        $requiredCreds = ['token'];

        if (!self::checkCreds($requiredCreds, $creds)) {
            throw new \Error(
                "bearer token credentials must match exactly: ['token' => ...]"
            );
        }

        return new Client(
            array_merge_recursive(
                $requestDefaults,
                [
                    'base_uri' => $baseUri,
                    'headers' => ['Authorization' => "Bearer ".$creds['token'].""],
                ]
            )
        );
    }
}

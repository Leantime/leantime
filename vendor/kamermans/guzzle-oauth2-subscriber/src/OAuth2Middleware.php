<?php

namespace kamermans\OAuth2;

use Psr\Http\Message\RequestInterface;

/**
 * OAuth2 plugin.
 *
 * @link http://tools.ietf.org/html/rfc6749 OAuth2 specification
 */
class OAuth2Middleware extends OAuth2Handler
{

    /**
     * Guzzle middleware invocation.
     *
     * @param callable $handler
     * @return \Closure
     */
    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {

            // Only sign requests using "auth"="oauth"
            if (!isset($options['auth']) || $options['auth'] !== 'oauth') {
                return $handler($request, $options);
            }

            $request = $this->signRequest($request);

            return $handler($request, $options)->then(
                $this->onFulfilled($request, $options, $handler),
                $this->onRejected($request, $options, $handler)
            );
        };
    }

    /**
     * Request error event handler.
     *
     * Handles unauthorized errors by acquiring a new access token and
     * retrying the request.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param array                              $options
     * @param callable                           $handler
     *
     * @return callable
     */
    private function onFulfilled(RequestInterface $request, array $options, $handler)
    {
        return function ($response) use ($request, $options, $handler) {
            // Only deal with Unauthorized response.
            if ($response && $response->getStatusCode() != 401) {
                return $response;
            }

            // If we already retried once, give up.
            // This is extremely unlikely in Guzzle 6+ since we're using promises
            // to check the response - looping should be impossible, but I'm leaving
            // the code here in case something interferes with the Middleware
            if ($request->hasHeader('X-Guzzle-Retry')) {
                return $response;
            }

            // Delete the previous access token, if any
            $this->deleteAccessToken();

            // Acquire a new access token, and retry the request.
            $accessToken = $this->getAccessToken();
            if ($accessToken === null) {
                return $response;
            }

            $request = $request->withHeader('X-Guzzle-Retry', '1');
            $request = $this->signRequest($request);

            return $handler($request, $options);
        };
    }

    private function onRejected(RequestInterface $request, array $options, $handler)
    {
        return function ($reason) use ($request, $options) {
            if (class_exists('\GuzzleHttp\Promise\Create')) {
                return \GuzzleHttp\Promise\Create::rejectionFor($reason);
            }

            // As of Guzzle Promises 2.0.0, the rejection_for function is deprecated and replaced with Create::rejectionFor
            return \GuzzleHttp\Promise\rejection_for($reason);
        };
    }
}

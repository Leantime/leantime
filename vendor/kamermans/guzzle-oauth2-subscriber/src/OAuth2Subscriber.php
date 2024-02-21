<?php

namespace kamermans\OAuth2;

use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Event\SubscriberInterface;

/**
 * OAuth2 plugin.
 *
 * @link http://tools.ietf.org/html/rfc6749 OAuth2 specification
 */
class OAuth2Subscriber extends OAuth2Handler implements SubscriberInterface
{
    public function getEvents()
    {
        return [
            'before' => ['onBefore', RequestEvents::VERIFY_RESPONSE + 100],
            'error'  => ['onError', RequestEvents::EARLY - 100],
        ];
    }

    /**
      * Request before-send event handler.
      *
      * Adds the Authorization header if an access token was found.
      *
      * @param BeforeEvent $event Event received
      */
    public function onBefore(BeforeEvent $event)
    {
        $request = $event->getRequest();

        // Only sign requests using "auth"="oauth"
        if ('oauth' !== $request->getConfig()['auth']) {
            return;
        }

        $this->signRequest($request);
    }

    /**
      * Request error event handler.
      *
      * Handles unauthorized errors by acquiring a new access token and
      * retrying the request.
      *
      * @param ErrorEvent $event Event received
      */
    public function onError(ErrorEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        // Only sign requests using "auth"="oauth"
        if ('oauth' !== $request->getConfig()['auth']) {
            return;
        }

        // Only deal with Unauthorized response.
        if ($response && $response->getStatusCode() != 401) {
            return;
        }

        // If we already retried once, give up.
        if ($request->getHeader('X-Guzzle-Retry')) {
            return;
        }

        // Delete the previous access token, if any
        $this->deleteAccessToken();

        // Acquire a new access token, and retry the request.
        $accessToken = $this->getAccessToken();
        if ($accessToken != null) {
            $newRequest = clone $request;
            $newRequest->setHeader('X-Guzzle-Retry', '1');

            $this->accessTokenSigner->sign($newRequest, $accessToken);

            $event->intercept(
                $event->getClient()->send($newRequest)
            );
        }
    }
}

<?php

namespace Leantime\Core\Http;

/**
 * Class ApiRequest
 *
 * Represents an API request.
 */
class ApiRequest extends IncomingRequest
{
    /**
     * Retrieves the Authorization header from the request.
     * The method checks multiple keys in the request headers for Authorization,
     * including 'Authorization', 'HTTP_AUTHORIZATION', and 'REDIRECT_HTTP_AUTHORIZATION'.
     * If the header is found, it is trimmed and returned as a string.
     * If the header is not found in the request headers, the method falls back to using the
     * getallheaders() function to retrieve all the request headers and checks for the
     * 'Authorization' header. If found, it is trimmed and returned as a string.
     * If no Authorization header is found, an empty string is returned.
     *
     * @return string The Authorization header value, or an empty string if not found.
     */
    public function getAuthorizationHeader(): string
    {
        foreach (
            [
                'Authorization',
                //Nginx or fast CGI
                'HTTP_AUTHORIZATION',
                //Nginx or fast CGI
                'REDIRECT_HTTP_AUTHORIZATION',
            ] as $key
        ) {
            $header = trim($this->headers->get($key)) ?? '';

            if (! empty($header)) {
                return $header;
            }
        }

        // fallback
        $allheaders = getallheaders();
        foreach ($allheaders as $name => $value) {
            if (strtolower($name) == 'authorization') {
                return trim($value);
            }
        }

        return '';
    }

    /**
     * Retrieves the API key from the request headers.
     *
     * @return string The API key, or an empty string if not found.
     */
    public function getAPIKey(): string
    {
        return $this->headers->get('x-api-key');
    }

    /**
     * Get the bearer token from the authorization header.
     *
     * @return string|null The bearer token if found, null otherwise.
     */
    public function getBearerToken(): ?string
    {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (! empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Checks if the current request is an API request.
     *
     * @return bool Returns true if the current request is an API request, false otherwise.
     */
    public function isApiRequest(): bool
    {
        $requestUri = $this->getRequestUri();

        return str_starts_with($requestUri, '/api/jsonrpc');
    }
}

<?php

namespace Leantime\Core;

class ApiRequest extends IncomingRequest
{
    /**
     * @param array                $query      The GET parameters
     * @param array                $request    The POST parameters
     * @param array                $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array                $cookies    The COOKIE parameters
     * @param array                $files      The FILES parameters
     * @param array                $server     The SERVER parameters
     * @param string|resource|null $content    The raw body data
     */
    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    /**
     * Get header Authorization
     *
     * @return string
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
     * get api key from header
     *
     * @return string
     */
    public function getAPIKey(): string
    {
        return trim($this->headers->get('x-api-key') ?? '');
    }

    /**
     * get access token from header
     *
     * @return ?string
     */
    public function getBearerToken(): ?string
    {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}

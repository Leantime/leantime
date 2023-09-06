<?php

namespace Leantime\Core;

class ApiRequest extends IncomingRequest
{
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
        return trim($this->headers->get('HTTP_X_API_KEY') ?? '');
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

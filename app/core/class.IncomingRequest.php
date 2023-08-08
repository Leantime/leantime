<?php

namespace leantime\core;

/**
 * Incoming Request information
 *
 * @package    leantime
 * @subpackage core
 */
class IncomingRequest
{
    /**
     * Constructor
     *
     * @return self
     */
    public function __construct()
    {
    }

    /**
     * Gets the base fqdn including protocol
     * Note: HTTP_HOST will return port number as well
     *
     * @return string
     */
    public function getBaseURL(): string
    {
        if (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
        ) {
            $protocol = "https://";
        } else {
            $protocol = "http://";
        }

        $domainName = $_SERVER['HTTP_HOST'];
        return $protocol . $domainName;
    }

    /**
     * Gets the full URL including request uri and protocol
     *
     * @return string
     */
    public function getFullURL()
    {
        return $this->getBaseURL() . rtrim($this->getRequestURI(), "/");
    }

    /**
     * Gets the request URI (path behind domain name)
     * Will adjust for subfolder installations
     *
     * @param string $baseURL Base Url in case of subfolder installations
     * @return string
     */
    public function getRequestURI(string $baseURL = ""): string
    {
        //$_SERVER['REQUEST_URI'] will include the subfolder if one is set. Let's make sure to take it out
        if ($baseURL != "") {
            $trimmedBaseURL = rtrim($baseURL, "/");
            $baseURLParts = explode("/", $trimmedBaseURL);

            //We only need to update Request URI if we have a subfolder install
            if (is_array($baseURLParts) && count($baseURLParts) == 4) {
                //0: http, 1: "", 2: domain.com 3: subfolder
                $subfolderName = $baseURLParts[3];

                //Remove subfoldername from Request URI
                $requestURI = preg_replace('/^\/' . $subfolderName . '/', '', $_SERVER['REQUEST_URI']);

                if (is_string($requestURI) === true) {
                    return $requestURI;
                } else {
                    return '';
                }
            }
        }

        return $_SERVER['REQUEST_URI'] ?? '';
    }

    /**
     * Gets the request method
     *
     * @return string|false
     */
    public static function getRequestMethod(): string|false
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            return strtolower($_SERVER['REQUEST_METHOD']);
        }

        return false;
    }

    /**
     * Gets the request params
     *
     * @param string $method
     * @return array
     */
    public static function getRequestParams($method)
    {
        if ($method == 'patch') {
            parse_str(file_get_contents("php://input"), $patch_vars);
        }

        match ($method) {
            'patch' => $patch_vars,
            'post' => $_POST,
            'delete', 'get' => $_GET,
            default => $_GET,
        };
    }

    /**
     * Get header Authorization
     *
     * @return string
     */
    public function getAuthorizationHeader(): string
    {
        if (isset($_SERVER['Authorization'])) {
            return trim($_SERVER["Authorization"]);
        }

        //Nginx or fast CGI
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return trim($_SERVER["HTTP_AUTHORIZATION"]);
        }

        //Nginx or fast CGI
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            return trim($_SERVER["REDIRECT_HTTP_AUTHORIZATION"]);
        }

        if (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );

            if (isset($requestHeaders['Authorization'])) {
                return trim($requestHeaders['Authorization']);
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
        return trim($_SERVER["HTTP_X_API_KEY"]);
    }

    /**
     * check if api key is set
     *
     * @return bool
     */
    public function hasAPIKey(): bool
    {
        if (isset($_SERVER["HTTP_X_API_KEY"])) {
            return true;
        }

        return false;
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

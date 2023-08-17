<?php

namespace leantime\core;

use Symfony\Component\HttpFoundation\Request;

/**
 * Incoming Request information
 *
 * @package    leantime
 * @subpackage core
 */
class IncomingRequest extends Request
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

        $this->setActFromRequestUri();
    }

    /**
     * Sets the act parameter from the request uri
     *
     * @param string $requestUri
     * @return void
     */
    protected function setActFromRequestUri(string $requestUri = null): void
    {
        $requestUri ??= $this->getRequestUri();
        $act = str_replace('/', '.', trim($requestUri, '/'));
        $this->query->set('act', $act);
    }

    /**
     * Gets the full URL including request uri and protocol
     *
     * @return string
     */
    public function getFullUrl()
    {
        return $request->getSchemeAndHttpHost() . $request->getBaseUrl() . $request->getPathInfo();
    }

    /**
     * Gets the request URI (path behind domain name)
     * Will adjust for subfolder installations
     *
     * @return string
     */
    public function getRequestUri()
    {
        $requestUri = parent::getRequestUri();

        static $subfolderFixApplied;

        if ($subfolderFixApplied) {
            return $requestUri;
        }

        $config = app()->make(environment::class);

        if (empty($config->appUrl)) {
            return $requestUri;
        }

        $baseUrlParts = explode('/', rtrim($config->appUrl, '/'));

        if (! is_array($baseUrlParts) || count($baseUrlParts) < 4) {
            return $requestUri;
        }

        $subfolderName = $baseUrlParts[3];
        $requestUri = preg_replace('/^\/' . $subfolderName . '/', '', $requestUri);

        $subfolderFixApplied = true;

        return $requestUri;
    }

    /**
     * Gets the request params
     *
     * @param string $method
     * @return array
     */
    public function getRequestParams(string $method = null): array
    {
        $method ??= $this->getMethod();
        $method = strtoupper($method);

        if ($method == 'PATCH') {
            parse_str($this->request->getContent(), $patch_vars);
        }

        return match ($method) {
            'PATCH' => $patch_vars,
            'POST' => $this->request->all(),
            'DELETE', 'GET' => $this->query->all(),
            default => $this->query->all(),
        };
    }

    /**
     * is htmx request
     *
     * @return bool
     */
    public function isHtmx(): bool
    {
        return filter_var(
            $this->headers->get('HX-Request') ?? 'false',
            FILTER_VALIDATE_BOOLEAN
        );
    }

    /**
     * check if api key is set
     *
     * @return bool
     */
    public function hasApiKey(): bool
    {
        if ($this->headers->get('HTTP_X_API_KEY')) {
            return true;
        }

        return false;
    }
}

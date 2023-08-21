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

        $this->setRequestDest();
    }

    /**
     * Sets the request destination from the path
     *
     * @param ?string $requestUri
     * @return void
     */
    protected function setRequestDest(?string $requestUri = null): void
    {
        $this->query->remove('act');
        $this->query->remove('id');
        $this->query->remove('request_parts');

        $requestUri ??= $this->getPathInfo();
        preg_match_all('#\/([^\/.]+)#', $requestUri, $uriParts);
        $uriParts = $uriParts[1] ?? array_map('ltrim', $uriParts[0] ?? [], '/');

        switch (count($uriParts)) {
            case 0:
                $act = 'dashboard.show';
                break;

            case 1:
            case 2:
                $act = join('.', $uriParts);
                break;

            default:
                $act = join('.', [$uriParts[0], $uriParts[1]]);
                $id = $uriParts[2];
                isset($uriParts[3]) && $request_parts = join('.', array_slice($uriParts, 3));
                break;
        };

        isset($act) && $this->query->set('act', str_replace('-', '', $act));
        isset($id) && $this->query->set('id', $id);
        isset($request_parts) && $this->query->set('request_parts', $request_parts);
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
            parse_str($this->getContent(), $patch_vars);
        }

        return match ($method) {
            'PATCH' => $patch_vars,
            'POST' => $this->request->all(),
            'DELETE', 'GET' => $this->query->all(),
            default => $this->query->all(),
        };
    }
}

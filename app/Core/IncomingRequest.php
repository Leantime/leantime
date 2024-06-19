<?php

namespace Leantime\Core;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Session\SessionManager;
use Illuminate\Session\SymfonySessionDecorator;
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
     * @param string|null $requestUri
     * @return void
     */
    protected function setRequestDest(?string $requestUri = null): void
    {
        $this->query->remove('act');
        $this->query->remove('id');
        $this->query->remove('request_parts');

        $requestUri ??= $this->getPathInfo();
        preg_match_all('#\/([^\/]+)#', $requestUri, $uriParts);
        $uriParts = $uriParts[1] ?? array_map('ltrim', $uriParts[0] ?? [], '/');

        switch (count($uriParts)) {
            case 0:
                $act = 'dashboard.home';
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

        $this->query->set('act', $act);
        isset($id) && $this->query->set('id', $id);
        isset($request_parts) && $this->query->set('request_parts', $request_parts);
    }

    /**
     * Gets the full URL including request uri and protocol
     *
     * @return string
     */
    public function getFullUrl(): string
    {
        return  $this->getSchemeAndHttpHost() .  $this->getBaseUrl() .  $this->getPathInfo();
    }

    /**
     * Gets the request URI (path behind domain name)
     * Will adjust for subfolder installations
     *
     * @return string
     * @throws BindingResolutionException
     */
    public function getRequestUri(): string
    {

        $requestUri = parent::getRequestUri();

        $config = app()->make(Environment::class);

        if (empty($config->appUrl)) {
            return $requestUri;
        }

        $baseUrlParts = explode('/', rtrim($config->appUrl, '/'));

        if (! is_array($baseUrlParts) || count($baseUrlParts) < 4) {
            return $requestUri;
        }

        $subfolderName = $baseUrlParts[3];
        $requestUri = preg_replace('/^\/' . $subfolderName . '/', '', $requestUri);

        $this->requestUri = $requestUri;

        $subfolderFixApplied = true;

        return $requestUri;
    }

    /**
     * Gets the request params
     *
     * @param string|null $method
     * @return array
     */
    public function getRequestParams(string $method = null): array
    {
        $method ??= $this->getMethod();
        $method = strtoupper($method);
        $patch_vars = [];

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

    /**
     * Retrieve an input item from the request.
     *
     * @param  string|null $key
     * @param  mixed       $default
     * @return mixed
     */
    public function input($key = null, $default = null)
    {
        return data_get(
            $this->getInputSource()->all() + $this->query->all(),
            $key,
            $default
        );
    }

    /**
     * Get the input source for the request.
     *
     * @return \Symfony\Component\HttpFoundation\InputBag
     */
    protected function getInputSource()
    {
        if ($this->isJson()) {
            return $this->json();
        }

        return in_array($this->getRealMethod(), ['GET', 'HEAD']) ? $this->query : $this->request;
    }

    /**
     * Set the Laravel session instance.
     *
     * @param \Illuminate\Contracts\Session\Session $session The Laravel session instance.
     *
     * @return void
     */
    public function setLaravelSession(\Illuminate\Contracts\Session\Session $session)
    {
        $this->session = new SymfonySessionDecorator($session);
    }

    /**
     * Get the full URL of the current request.
     * Wrapper for Laravel
     *
     * @return string The full URL of the current request.
     */
    public function fullUrl()
    {
        return $this->getFullUrl();
    }

    /**
     * Determine if the request is JSON.
     *
     * @return bool
     */
    public function isJson()
    {
        return $this->hasHeader('Content-Type') &&
            str_contains($this->header('Content-Type')[0], 'json');
    }

    /**
     * Determine if a header is set on the request.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasHeader($key)
    {
        return ! is_null($this->header($key));
    }

    /**
     * Retrieve a header from the request.
     *
     * @param  string|null  $key
     * @param  string|array|null  $default
     * @return string|array|null
     */
    public function header($key = null, $default = null)
    {
        return $this->retrieveItem('headers', $key, $default);
    }

    /**
     * Retrieve a parameter item from a given source.
     *
     * @param  string  $source
     * @param  string|null  $key
     * @param  string|array|null  $default
     * @return string|array|null
     */
    protected function retrieveItem($source, $key, $default)
    {
        if (is_null($key)) {
            return $this->$source->all();
        }

        if ($this->$source instanceof InputBag) {
            return $this->$source->all()[$key] ?? $default;
        }

        return $this->$source->get($key, $default);
    }

}

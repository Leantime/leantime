<?php

namespace Leantime\Core\Http;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Http\RequestType\RequestTypeDetector;
use Symfony\Component\HttpFoundation\Request;

/**
 * Incoming Request information
 */
class IncomingRequest extends \Illuminate\Http\Request
{
    /**
     * The decoded JSON content for the request.
     *
     * @var \Symfony\Component\HttpFoundation\InputBag|null
     */
    protected $json;

    protected $pageUrl;

    protected $currentRoute;

    protected $basePath;

    private $basePathCalculated = false;

    private $pathInfoCalculated = false;

    public $headers = [];

    protected $requestUri;

    public $query;

    public $request;

    public const HEADER_FORWARDED = parent::HEADER_FORWARDED; // When using RFC 7239

    public const HEADER_X_FORWARDED_FOR = parent::HEADER_X_FORWARDED_FOR;

    public const HEADER_X_FORWARDED_HOST = parent::HEADER_X_FORWARDED_HOST;

    public const HEADER_X_FORWARDED_PROTO = parent::HEADER_X_FORWARDED_PROTO;

    public const HEADER_X_FORWARDED_PORT = parent::HEADER_X_FORWARDED_PORT;

    public const HEADER_X_FORWARDED_PREFIX = parent::HEADER_X_FORWARDED_PREFIX;

    public const HEADER_X_FORWARDED_AWS_ELB = parent::HEADER_X_FORWARDED_AWS_ELB; // AWS ELB doesn't send X-Forwarded-Host

    public const HEADER_X_FORWARDED_TRAEFIK = parent::HEADER_X_FORWARDED_TRAEFIK; // All "X-Forwarded-*"

    public static function createFromGlobals(): static
    {
        return parent::createFromBase(parent::createFromGlobals());
    }

    public static function capture(): IncomingRequest
    {
        parent::enableHttpMethodParameterOverride();

        $request = self::createFromGlobals();
        $requestClass = RequestTypeDetector::detect($request);

        return $requestClass::createFromBase($request);
    }

    /**
     * Gets the full URL including request uri and protocol
     */
    public function getFullUrl(): string
    {
        return $this->getSchemeAndHttpHost().$this->getBasePath().$this->getPathInfo();
    }

    public function getBasePath(): string
    {

        // Early in the stack we may not have BASE_URL yet.
        // Let's have symfony deal with it
        if (! defined('BASE_URL')) {
            return $this->prepareBasePath();
        }

        // Will always only return the domain portion
        if (! $this->basePathCalculated) {
            $schemeHost = $this->getSchemeAndHttpHost();
            $baseUrl = rtrim(BASE_URL, '/');

            // Extract potential subfolder from BASE_URL
            if ($baseUrl !== $schemeHost) {
                $this->basePath = substr($baseUrl, strlen($schemeHost));
            } else {
                $this->basePath = $this->prepareBasePath();
            }

            $this->basePathCalculated = true;
        }

        return $this->basePath;
    }

    public function getPathInfo(): string
    {
        if (! $this->pathInfoCalculated) {
            $pathInfo = $this->preparePathInfo();
            $basePath = $this->getBasePath();

            // Only strip basePath if it exists at the start of pathInfo
            if ($basePath && strpos($pathInfo, $basePath) === 0) {
                $this->pathInfo = substr($pathInfo, strlen($basePath));
            } else {
                $this->pathInfo = $pathInfo;
            }

            $this->pathInfoCalculated = true;
        }

        return $this->pathInfo;
    }

    /**
     * Gets the request URI (path behind domain name)
     * Will adjust for subfolder installations
     */
    public function getRequestUri(): string
    {

        if ($this->requestUri === null) {
            $requestUri = parent::getRequestUri();
            $basePath = $this->getBasePath();

            // If we have a basePath (subfolder installation)
            // and it exists at the start of the requestUri,
            // strip it out to get the correct relative path
            if ($basePath && str_starts_with($requestUri, $basePath)) {
                $requestUri = substr($requestUri, strlen($basePath));
            }

            // Ensure requestUri starts with a forward slash
            if (! str_starts_with($requestUri, '/')) {
                $requestUri = '/'.$requestUri;
            }

            $this->requestUri = $requestUri;
        }

        return $this->requestUri;
    }

    /**
     * Gets the request params
     */
    public function getRequestParams(?string $method = null): array
    {
        $method ??= $this->method();
        $method = strtoupper($method);
        $patch_vars = [];

        if ($method === 'PATCH') {
            parse_str($this->getContent(), $patch_vars);
        }

        $params = $this->query->all();

        // Merge query vars with post or patch vars
        return match ($method) {
            'PATCH' => array_merge($params, $patch_vars),
            'POST' => array_merge($this->request->all(), $params),
            default => $params
        };
    }

    /**
     * Get the full URL of the current request.
     * Wrapper for Laravel
     *
     * @return string The full URL of the current request.
     *
     * @Override
     */
    public function fullUrl(): string
    {
        return $this->getFullUrl();
    }

    /**
     * Determines whether the current request is an API or Cron request.
     *
     * @return bool Returns true if the request is an API or Cron request, false otherwise.
     */
    public function isApiOrCronRequest(): bool
    {
        $requestUri = $this->getRequestUri();

        return str_starts_with(strtolower($requestUri), '/api/jsonrpc') || str_starts_with($requestUri, '/cron');
    }

    /**
     * Determines whether the current request is an Htmx request.
     *
     * @return bool Returns true if the request is an Htmx request, false otherwise.
     */
    public function isHtmxRequest(): bool
    {
        return ! empty($this->headers->get('Hx-Request'));
    }

    /**
     * Determines whether the current request is a boosted htmx request.
     *
     * @return bool Returns true if the request is a boosted htmx request, false otherwise.
     */
    public function isBoostedHtmxRequest(): bool
    {
        return $this->isHtmxRequest() &&
            $this->headers->get('Hx-Boost') === 'true';
    }

    /**
     * Determines whether the current request is an unboosted HTMX request.
     *
     * @return bool Returns true if the request is an unboosted HTMX request, false otherwise.
     */
    public function isUnboostedHtmxRequest(): bool
    {
        return $this->isHtmxRequest() &&
            empty($this->headers->get('Hx-Boost'));
    }

    public function getCurrentRoute()
    {
        if ($this->currentRoute === null) {
            $path = $this->getPathInfo();
            $path = trim($path, '/');

            if (empty($path)) {
                return '';
            }

            $route = str_replace('/', '.', $path);

            $this->currentRoute = $route;
        }

        return $this->currentRoute;
    }

    public function segments(): array
    {
        $segments = explode('/', $this->decodedPath());

        return array_values(array_filter($segments, static function ($value) {
            return $value !== '';
        }));
    }

    public function decodedPath(): string
    {
        return rawurldecode($this->path());
    }

    public function path(): string
    {
        $pattern = trim($this->getPathInfo(), '/');

        return $pattern === '' ? '/' : $pattern;
    }

    private function getBaseUrlReal(): string
    {
        return $this->baseUrl ??= $this->prepareBaseUrl();
    }

    public function setCurrentRoute($route): void
    {
        $this->currentRoute = $route;
    }

    /**
     * Gets the module name from the given complete name or the current route.
     *
     * @param  string|null  $completeName  The complete name from which to extract the module name. If not provided, the current route will be used.
     * @return string The module name.
     *
     * @deprecated
     */
    public function getModuleName(?string $completeName = null): string
    {
        $completeName ??= $this->getCurrentRoute();
        $actionParts = explode('.', empty($completeName) ? $this->currentRoute : $completeName);

        if (is_array($actionParts)) {
            return $actionParts[0];
        }

        return '';
    }

    /**
     * getActionName - split string to get actionName
     *
     * @throws BindingResolutionException
     *
     * @deprecated
     */
    public function getActionName(?string $completeName = null): string
    {
        $completeName ??= $this->getCurrentRoute();
        $actionParts = explode('.', empty($completeName) ? $this->currentRoute : $completeName);
        $actionName = '';

        // If no action name was given, call index controller
        if (is_array($actionParts) && count($actionParts) === 1) {
            $actionName = 'index';
        }

        if (is_array($actionParts) && count($actionParts) === 2) {
            $actionName = $actionParts[1];
        }

        return $actionName;
    }

    /**
     * Checks if the current request is an API request.
     *
     * @return bool Returns true if the current request is an API request, false otherwise.
     */
    public function isApiRequest(): bool
    {
        $requestUri = $this->getRequestUri();

        return str_starts_with(strtolower($requestUri), '/api/jsonrpc');
    }
}

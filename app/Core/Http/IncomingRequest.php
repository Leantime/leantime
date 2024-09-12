<?php

namespace Leantime\Core\Http;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Console\CliRequest;
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
        return parent::createFromGlobals();
    }

    public static function capture()
    {
        parent::enableHttpMethodParameterOverride();

        $headers = collect(getallheaders())
            ->mapWithKeys(fn ($val, $key) => [
                strtolower($key) => match (true) {
                    in_array($val, ['false', 'true']) => filter_var($val, FILTER_VALIDATE_BOOLEAN),
                    preg_match('/^[0-9]+$/', $val) => filter_var($val, FILTER_VALIDATE_INT),
                    default => $val,
                },
            ])
            ->all();

        $request = match (true) {
            isset($headers['hx-request']) => HtmxRequest::createFromGlobals(),
            isset($headers['x-api-key']) => ApiRequest::createFromGlobals(),
            defined('LEAN_CLI') && LEAN_CLI => CliRequest::createFromGlobals(),
            default => parent::createFromGlobals(),
        };

        $request->setUrlConstants();

        return $request;

    }

    /**
     * Sets the URL constants for the application.
     *
     * If the BASE_URL constant is not defined, it will be set based on the value of $appUrl parameter.
     * If $appUrl is empty or not provided, it will be set using the getSchemeAndHttpHost method of the class.
     *
     * The APP_URL environment variable will be set to the value of $appUrl.
     *
     * If the CURRENT_URL constant is not defined, it will be set by appending the getRequestUri method result to the BASE_URL.
     *
     * @param  string  $appUrl  The URL to be used as BASE_URL and APP_URL. Defaults to an empty string.
     * @return void
     */
    public function setUrlConstants($appUrl = '')
    {

        if (! defined('BASE_URL')) {
            if (isset($appUrl) && ! empty($appUrl)) {
                define('BASE_URL', $appUrl);

            } else {
                define('BASE_URL', parent::getSchemeAndHttpHost());
            }
        }

        putenv('APP_URL='.$appUrl);

        if (! defined('CURRENT_URL')) {
            define('CURRENT_URL', BASE_URL.$this->getRequestUri());
        }
    }

    /**
     * Gets the full URL including request uri and protocol
     */
    public function getFullUrl(): string
    {
        return parent::getSchemeAndHttpHost().parent::getBaseUrl().parent::getPathInfo();
    }

    /**
     * Gets the request URI (path behind domain name)
     * Will adjust for subfolder installations
     *
     * @throws BindingResolutionException
     */
    public function getRequestUri($appUrl = ''): string
    {

        $requestUri = parent::getRequestUri();

        if (empty($appUrl)) {
            return $requestUri;
        }

        $baseUrlParts = explode('/', rtrim($appUrl, '/'));

        if (! is_array($baseUrlParts) || count($baseUrlParts) < 4) {
            return $requestUri;
        }

        $subfolderName = $baseUrlParts[3];
        $requestUri = preg_replace('/^\/'.$subfolderName.'/', '', $requestUri);

        $this->requestUri = $requestUri;

        $subfolderFixApplied = true;

        return $requestUri;
    }

    /**
     * Gets the request params
     */
    public function getRequestParams(?string $method = null): array
    {
        $method ??= parent::method();
        $method = strtoupper($method);
        $patch_vars = [];

        if ($method == 'PATCH') {
            parse_str(parent::getContent(), $patch_vars);
        }

        $params = $this->query->all();

        //Merge query vars wigh post or patch vars
        return match ($method) {
            'PATCH' => array_merge($patch_vars, $params),
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
    public function fullUrl()
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

        return str_starts_with($requestUri, '/api') || str_starts_with($requestUri, '/cron');
    }

    /**
     * Determines whether the current request is an Htmx request.
     *
     * @return bool Returns true if the request is an Htmx request, false otherwise.
     */
    public function isHtmxRequest(): bool
    {
        return ! empty($this->headers->get('Hx-Request')) ? true : false;
    }

    /**
     * Determines whether the current request is a boosted htmx request.
     *
     * @return bool Returns true if the request is a boosted htmx request, false otherwise.
     */
    public function isBoostedHtmxRequest(): bool
    {
        if ($this->isHtmxRequest() &&
            this->headers->get('Hx-Boost') == 'true') {
            return true;
        }

        return false;
    }

    /**
     * Determines whether the current request is an unboosted HTMX request.
     *
     * @return bool Returns true if the request is an unboosted HTMX request, false otherwise.
     */
    public function isUnboostedHtmxRequest(): bool
    {
        if ($this->isHtmxRequest() &&
            empty($this->headers->get('Hx-Boost'))) {
            return true;
        }

        return false;
    }

    public function getCurrentRoute()
    {

        if ($this->currentRoute == null) {

            $route = '';
            $segments = parent::segments();
            if (count($segments) > 1) {
                $route = implode('.', $segments);
            }

            $this->currentRoute = $route;
        }

        return $this->currentRoute;
    }

    public function setCurrentRoute($route)
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

        //If not action name was given, call index controller
        if (is_array($actionParts) && count($actionParts) == 1) {
            return 'index';
        } elseif (is_array($actionParts) && count($actionParts) == 2) {
            return $actionParts[1];
        }

        return '';
    }
}

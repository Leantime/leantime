<?php

namespace Leantime\Core\Http;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Session\SymfonySessionDecorator;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Console\CliRequest;
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

    public function isApiOrCronRequest(): bool
    {
        $requestUri = $this->getRequestUri();
        return str_starts_with($requestUri, "/api") || str_starts_with($requestUri, "/cron");
    }

    public function isHtmxRequest(): bool
    {
        return !empty($this->headers->get('Hx-Request')) ? true : false;
    }

    public function isBoostedHtmxRequest(): bool
    {
        if($this->isHtmxRequest() &&
            this->headers->get('Hx-Boost') == 'true') {
            return true;
        }

        return false;
    }

    public function isUnboostedHtmxRequest(): bool
    {
        if($this->isHtmxRequest() &&
            empty($this->headers->get('Hx-Boost'))) {
            return true;
        }

        return false;
    }


    /**
     * Determine if the current request probably expects a JSON response.
     *
     * @return bool
     */
    public function expectsJson()
    {
        if($this instanceof CliRequest || $this->isApiOrCronRequest()) {
            return true;
        }

        return false;
    }

}

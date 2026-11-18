<?php

namespace Unit\app\Domain\Files;

use Leantime\Core\Http\IncomingRequest;
use Unit\TestCase;

/**
 * Regression coverage for #4062: old /download.php attachment links redirect to
 * /files/get. The target was the bare path '/files/get', which Laravel resolves against
 * the request's host root, so an installation in a subfolder
 * (LEAN_APP_URL = https://example.com/leantime) lost its /leantime prefix. The target
 * is now built from BASE_URL, the configured application URL.
 */
class LegacyDownloadRouteTest extends TestCase
{
    /**
     * Run the real /download.php route from Files/routes.php for $uri and return the response.
     */
    private function runLegacyDownloadRoute(string $uri): \Symfony\Component\HttpFoundation\Response
    {
        require APP_ROOT.'/app/Domain/Files/routes.php';

        $request = IncomingRequest::create($uri, 'GET');
        $this->app->instance('request', $request);

        $route = $this->app->make('router')->getRoutes()->match($request);
        $route->bind($request);

        return $route->run();
    }

    /**
     * The redirect points at BASE_URL, not at whatever host/root the request came in on.
     * The request below uses a different host than BASE_URL, which is what the old
     * relative redirect followed.
     */
    public function test_redirect_is_built_from_base_url(): void
    {
        $response = $this->runLegacyDownloadRoute(
            'http://not-the-app-url.test/download.php?module=x&encName=y&ext=png&realName=z'
        );

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(
            BASE_URL.'/files/get?encName=y&ext=png&module=x&realName=z',
            $response->headers->get('Location')
        );
    }

    /**
     * Without a query string the redirect has no dangling '?'.
     */
    public function test_redirect_without_query_string(): void
    {
        $response = $this->runLegacyDownloadRoute('http://not-the-app-url.test/download.php');

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(BASE_URL.'/files/get', $response->headers->get('Location'));
    }
}

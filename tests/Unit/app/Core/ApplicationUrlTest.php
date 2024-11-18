<?php

namespace Tests\Unit\App\Core;

use Leantime\Core\Application;
use Leantime\Core\Bootstrap\LoadConfig;
use Leantime\Core\Bootstrap\SetRequestForConsole;
use Leantime\Core\Configuration\Environment;

class ApplicationUrlTest extends \Unit\TestCase
{
    protected $app;

    protected $config;

    protected function setUp(): void
    {

        parent::setUp();

        $this->bootstrapApplication();

    }

    protected function bootstrapApplication()
    {

        $this->app = new Application(APP_ROOT);
        $this->app->boot();

        $this->app->bootstrapWith([LoadConfig::class, SetRequestForConsole::class]);

        $this->config = $this->app['config'];
    }

    public function testBaseUrlIsSetCorrectlyFromConfig(): void
    {
        // Test default behavior (no LEAN_APP_URL set)
        $this->assertEquals('http://localhost', BASE_URL);
        $this->assertEquals('http://localhost', $this->config->get('app.url'));

        // Test with LEAN_APP_URL set
        //putenv('LEAN_APP_URL=https://example.com');
        $_ENV['LEAN_APP_URL'] = 'https://example.com';

        // Reinitialize application to test new environment
        $this->bootstrapApplication();

        //dd($this->config);

        $this->assertEquals('https://example.com', $this->config->get('app.url'));
        $this->assertEquals('https://example.com', $this->config->get('appUrl'));
    }

    public function testBaseUrlHandlesTrailingSlash(): void
    {

        $_ENV['LEAN_APP_URL'] = 'https://example.com/';

        $this->bootstrapApplication();

        $this->assertEquals('https://example.com', $this->config->get('app.url'));
        $this->assertEquals('https://example.com', $this->config->get('appUrl'));
    }

    public function testBaseUrlHandlesSubdirectory(): void
    {

        $_ENV['LEAN_APP_URL'] = 'https://example.com/leantime';

        $this->bootstrapApplication();

        $this->assertEquals('https://example.com/leantime', $this->config->get('app.url'));
        $this->assertEquals('https://example.com/leantime', $this->config->get('appUrl'));
    }

    public function testBaseUrlHandlesPort(): void
    {

        $_ENV['LEAN_APP_URL'] = 'https://example.com:8443';

        $this->bootstrapApplication();

        $this->assertEquals('https://example.com:8443', $this->config->get('app.url'));
        $this->assertEquals('https://example.com:8443', $this->config->get('appUrl'));
    }

    public function testBaseUrlHandlesReverseProxy(): void
    {
        // Simulate reverse proxy headers
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $_SERVER['HTTP_X_FORWARDED_HOST'] = 'example.com';

        $_ENV['LEAN_APP_URL'] = 'https://example.com';

        $this->bootstrapApplication();

        $this->assertEquals('https://example.com', $this->config->get('app.url'));
        $this->assertEquals('https://example.com', $this->config->get('appUrl'));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up environment
        putenv('LEAN_APP_URL');
        unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
        unset($_SERVER['HTTP_X_FORWARDED_HOST']);
    }
}

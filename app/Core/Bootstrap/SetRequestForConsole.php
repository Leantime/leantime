<?php

namespace Leantime\Core\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Leantime\Core\Console\CliRequest;

class SetRequestForConsole
{
    /**
     * Bootstrap the given application.
     *
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $uri = $app->make('config')->get('app.url', 'http://localhost');
        $uri = empty($uri) ? 'http://localhost' : $uri;

        $components = parse_url($uri);

        $server = $_SERVER;

        if (isset($components['path'])) {
            $server = array_merge([
                'SCRIPT_FILENAME' => $components['path'],
                'SCRIPT_NAME' => $components['path'],
            ], $server);
        }

        if(! defined('BASE_URL')) {
            define('BASE_URL', $uri);
        }

        //IMPORTANT: We can't use the native laravel bootstrapper for this because they inject Illuminate\Http\Request
        //And we need CliRequest
        $app->instance('request', CliRequest::create(
            $uri, 'GET', [], [], [], $server
        ));

    }
}

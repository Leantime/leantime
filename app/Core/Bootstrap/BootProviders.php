<?php

namespace Leantime\Core\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Leantime\Core\Events\EventDispatcher;

class BootProviders
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $app->boot();
    }
}
